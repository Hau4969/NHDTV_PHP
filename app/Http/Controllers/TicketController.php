<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Showtime;
use App\Models\Voucher;

class TicketController extends Controller
{
    /**
     * Hàm hỗ trợ tính toán giá vé cho từng loại ghế trong suất chiếu
     * Priority: Final Price > (Base Price + Adjustment) > Global Base Price
     */
    private function getShowtimePrices($showtimeId, $roomSeatTypeIds = [])
    {
        // 1. Lấy giá gốc toàn cục (Global Base Price)
        $globalBasePrices = DB::table('seat_types')->pluck('base_price', 'id');

        // 2. Lấy cấu hình giá riêng của suất chiếu (Showtime Config)
        $showtimeConfigs = DB::table('showtime_prices')
            ->where('showtime_id', $showtimeId)
            ->get()
            ->keyBy('seat_type_id');

        $priceMap = [];

        // Nếu không truyền danh sách loại ghế cụ thể, lấy tất cả loại ghế
        $targetTypes = !empty($roomSeatTypeIds) ? $roomSeatTypeIds : $globalBasePrices->keys()->toArray();

        foreach ($targetTypes as $typeId) {
            // Mặc định lấy giá gốc toàn cục
            $finalPrice = $globalBasePrices[$typeId] ?? 0;

            if (isset($showtimeConfigs[$typeId])) {
                $cfg = $showtimeConfigs[$typeId];

                // Nếu có set final_price trực tiếp
                if (!is_null($cfg->final_price) && $cfg->final_price > 0) {
                    $finalPrice = (int)$cfg->final_price;
                } else {
                    // Nếu không, tính: Base (ưu tiên base của suất chiếu) + Price (phần điều chỉnh tăng/giảm)
                    $base = ($cfg->base_price && $cfg->base_price > 0) ? (int)$cfg->base_price : $finalPrice;
                    $adjust = (int)($cfg->price ?? 0);
                    $finalPrice = $base + $adjust;
                }
            }

            $priceMap[$typeId] = max(0, $finalPrice); // Không để giá âm
        }

        return $priceMap;
    }

    /** Trang chọn ghế */
    public function create(Showtime $showtime)
    {
        // 1. Phim & phòng
        $movie = DB::table('movies')->where('id', $showtime->movie_id)->first();
        $room  = DB::table('rooms')->where('id', $showtime->room_id)->first();

        // 2. Lấy danh sách Ghế & Loại ghế
        $seats = DB::table('seats as s')
            ->leftJoin('seat_types as t', 't.id', '=', 's.seat_type_id')
            ->where('s.room_id', $showtime->room_id)
            ->select(
                's.id', 's.row_letter', 's.seat_number', 's.code',
                's.seat_type_id', 't.name as seat_type_name'
            )
            ->orderBy('s.row_letter')->orderBy('s.seat_number')
            ->get();

        // 3. Ghế đã giữ/đặt (trừ loại hủy)
        $occupied = DB::table('tickets')
            ->where('showtime_id', $showtime->id)
            ->where('status', '!=', 'canceled')
            ->pluck('seat_id')->toArray();

        // 4. Lấy danh sách ID các loại ghế CÓ TRONG PHÒNG này
        $roomSeatTypeIds = $seats->pluck('seat_type_id')->unique()->toArray();

        // 5. Tính giá vé (Chỉ tính cho các loại ghế có trong phòng)
        $priceMap = $this->getShowtimePrices($showtime->id, $roomSeatTypeIds);

        // 6. Tạo danh sách hiển thị bảng giá (Filter loại ghế có trong phòng)
        $ticketTypes = DB::table('seat_types')
            ->whereIn('id', $roomSeatTypeIds)
            ->select('id', 'name')
            ->orderBy('base_price', 'asc') // Sắp xếp giá thấp lên cao cho đẹp
            ->get()
            ->map(function($type) use ($priceMap) {
                $type->display_price = $priceMap[$type->id] ?? 0;
                return $type;
            });

        // 7. Danh sách voucher
        $vouchers = Voucher::query()
            ->where('status', 'active')
            ->where(function($q){ $now=now(); $q->whereNull('start_at')->orWhere('start_at','<=',$now); })
            ->where(function($q){ $now=now(); $q->whereNull('end_at')->orWhere('end_at','>=',$now); })
            ->get()
            ->filter->isActive()
            ->values();

        // 8. Suất chiếu khác
        $otherShowtimes = DB::table('showtimes as st')
            ->join('rooms as rm', 'rm.id', '=', 'st.room_id')
            ->where('st.movie_id', $showtime->movie_id)
            ->whereDate('st.start_time', '>=', now()->toDateString())
            ->where('st.id', '!=', $showtime->id)
            ->select('st.id', 'st.start_time', 'rm.name as room_name')
            ->orderBy('st.start_time')->get();

        return view('tickets.create', compact(
            'showtime', 'movie', 'room',
            'seats', 'occupied', 'priceMap',
            'otherShowtimes', 'ticketTypes', 'vouchers'
        ));
    }

    /** Lưu vé */
    public function store(Showtime $showtime, Request $request)
    {
        $data = $request->validate([
            'seat_ids'     => ['required', 'array', 'min:1', 'max:10'],
            'seat_ids.*'   => ['integer'],
            'voucher_id'   => ['nullable', 'integer'],
            'pay_now'      => ['nullable', 'boolean'],
        ]);

        $userId = Auth::id();
        $payNow = $request->boolean('pay_now');

        // 1. Tính lại giá vé Server-side (Security)
        $selectedSeats = DB::table('seats')
            ->whereIn('id', $data['seat_ids'])
            ->select('id', 'seat_type_id', 'row_letter', 'seat_number')
            ->get();

        $neededTypes = $selectedSeats->pluck('seat_type_id')->unique()->toArray();
        $priceMap = $this->getShowtimePrices($showtime->id, $neededTypes);

        // 2. Xử lý Voucher
        $voucher = null;
        $voucherUseCount = 0;

        if (!empty($data['voucher_id'])) {
            $voucher = Voucher::lockForUpdate()->find($data['voucher_id']);
            if (!$voucher || !$voucher->isActive()) {
                return back()->withErrors('Mã ưu đãi không hợp lệ.')->withInput();
            }
            if (!is_null($voucher->usage_limit)) {
                $remain = $voucher->usage_limit - $voucher->used_count;
                if ($remain <= 0) {
                    return back()->withErrors('Mã ưu đãi đã hết lượt sử dụng.')->withInput();
                }
            }
        }

        $firstPaymentId = null;

        // --- QUAN TRỌNG: Tạo Mã Giao Dịch Chung ---
        // Dùng chung reference cho tất cả các ghế trong lần đặt này
        $sharedReference = 'PM' . now()->format('YmdHis') . $userId . rand(100, 999);

        DB::transaction(function () use ($data, $showtime, $userId, $selectedSeats, $priceMap, $voucher, $payNow, $sharedReference, &$voucherUseCount, &$firstPaymentId) {
            // Check concurrency
            $exists = DB::table('tickets')
                ->where('showtime_id', $showtime->id)
                ->whereIn('seat_id', $data['seat_ids'])
                ->where('status', '!=', 'canceled')
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                abort(409, 'Một hoặc nhiều ghế bạn chọn vừa được người khác đặt. Vui lòng chọn lại.');
            }

            // Hủy vé pending cũ nếu có
            if ($payNow) {
                DB::table('tickets')
                    ->where('showtime_id', $showtime->id)
                    ->where('user_id', $userId)
                    ->where('status', 'reserved')
                    ->update(['status' => 'canceled', 'updated_at' => now()]);
            }

            foreach ($selectedSeats as $row) {
                $basePrice = $priceMap[$row->seat_type_id] ?? 0;
                $finalPrice = $basePrice;

                // Logic Voucher (Giảm trên từng vé)
                if ($voucher) {
                    if ($voucher->type === 'percent') {
                        $discount = (int)round($basePrice * $voucher->value / 100);
                        $finalPrice = max($basePrice - $discount, 0);
                    } else {
                        $finalPrice = max($basePrice - (int)$voucher->value, 0);
                    }
                    $voucherUseCount++;
                }

                // Insert/Update Ticket
                $oldTicket = DB::table('tickets')
                    ->where('showtime_id', $showtime->id)
                    ->where('seat_id', $row->id)
                    ->where('status', 'canceled')
                    ->lockForUpdate()->first();

                $ticketData = [
                    'user_id'     => $userId,
                    'ticket_type' => 'default',
                    'status'      => 'reserved',
                    'price'       => $basePrice,
                    'final_price' => $finalPrice,
                    'voucher_id'  => $voucher->id ?? null,
                    'updated_at'  => now(),
                ];

                if ($oldTicket) {
                    DB::table('tickets')->where('id', $oldTicket->id)->update($ticketData);
                    $ticketId = $oldTicket->id;
                } else {
                    $ticketData['showtime_id'] = $showtime->id;
                    $ticketData['seat_id'] = $row->id;
                    $ticketData['created_at'] = now();
                    $ticketId = DB::table('tickets')->insertGetId($ticketData);
                }

                // Tạo Payment (Dùng chung $sharedReference)
                if ($payNow) {
                    $pid = DB::table('payments')->insertGetId([
                        'ticket_id'      => $ticketId,
                        'user_id'        => $userId,
                        'reference'      => $sharedReference, // <--- Key Change
                        'amount'         => $finalPrice,
                        'payment_method' => 'qr',
                        'status'         => 'pending',
                        'expires_at'     => now()->addSeconds(300),
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                    if (!$firstPaymentId) $firstPaymentId = $pid;
                }
            }

            // Cập nhật lượt dùng voucher
            if ($voucher && $voucherUseCount > 0) {
                $voucher->increment('used_count', $voucherUseCount);
            }
        });

        if ($payNow && $firstPaymentId) {
            return redirect()->route('payments.show', $firstPaymentId);
        }

        return redirect()->route('tickets.history')
            ->with('ok', 'Đặt vé thành công! Vui lòng thanh toán.');
    }

    public function history()
    {
        $uid = Auth::id();
        $tickets = DB::table('tickets as t')
            ->join('seats as s', 's.id', '=', 't.seat_id')
            ->join('rooms as r', 'r.id', '=', 's.room_id')
            ->join('showtimes as st', 'st.id', '=', 't.showtime_id')
            ->join('movies as m', 'm.id', '=', 'st.movie_id')
            ->where('t.user_id', $uid)
            ->where('t.status', '!=', 'canceled')
            ->select('t.*', 's.row_letter', 's.seat_number',
                     DB::raw("CONCAT(s.row_letter, s.seat_number) as seat_label"),
                     'r.name as room_name', 'st.start_time', 'm.title as movie_title')
            ->orderBy('t.id', 'desc')->paginate(10);
        return view('tickets.history', compact('tickets'));
    }
}
