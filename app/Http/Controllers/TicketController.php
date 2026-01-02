<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Showtime;
use App\Models\Voucher;

class TicketController extends Controller
{
    /** Trang chọn ghế */
    public function create(Showtime $showtime)
    {
        // Phim & phòng
        $movie = DB::table('movies')->where('id', $showtime->movie_id)->first();
        $room  = DB::table('rooms')->where('id', $showtime->room_id)->first();

        // Ghế
        $seats = DB::table('seats as s')
            ->leftJoin('seat_types as t','t.id','=','s.seat_type_id')
            ->where('s.room_id', $showtime->room_id)
            ->select('s.id','s.row_letter','s.seat_number','s.code','s.seat_type_id','t.name as seat_type')
            ->orderBy('s.row_letter')->orderBy('s.seat_number')
            ->get();

        // Ghế đã giữ/đặt
        $occupied = DB::table('tickets')
            ->where('showtime_id', $showtime->id)
            ->where('status','!=','canceled')
            ->pluck('seat_id')->toArray();

        // Giá hiệu lực (showtime_prices > seat_types)
        $sp = DB::table('showtime_prices')
            ->where('showtime_id', $showtime->id)
            ->select('seat_type_id','base_price','price','final_price')
            ->get();

        $effective = [];
        foreach ($sp as $r) {
            $effective[$r->seat_type_id] = !is_null($r->final_price)
                ? (int)$r->final_price
                : (int)($r->base_price ?? 0) + (int)($r->price ?? 0);
        }
        if (empty($effective)) {
            $effective = DB::table('seat_types')
                ->pluck('base_price','id')
                ->map(fn($v)=>(int)$v)->toArray();
        }
        $priceMap = $effective;

        // Loại ghế để hiển thị nút chọn
        $ticketTypes = DB::table('seat_types')
            ->select('id','name','base_price as display_price')
            ->orderBy('id')->get();

        // Danh sách voucher đang hoạt động
        $vouchers = Voucher::query()
            ->where('status', 'active')
            ->where(function($q){ $now=now(); $q->whereNull('start_at')->orWhere('start_at','<=',$now); })
            ->where(function($q){ $now=now(); $q->whereNull('end_at')->orWhere('end_at','>=',$now); })
            ->get()
            ->filter->isActive()
            ->values();

        // Suất chiếu khác
        $otherShowtimes = DB::table('showtimes as st')
            ->join('rooms as rm','rm.id','=','st.room_id')
            ->where('st.movie_id',$showtime->movie_id)
            ->whereDate('st.start_time','>=',now()->toDateString())
            ->select('st.id','st.start_time','rm.name as room_name')
            ->orderBy('st.start_time')->get();

        return view('tickets.create', compact(
            'showtime','movie','room',
            'seats','occupied','priceMap',
            'otherShowtimes','ticketTypes','vouchers'
        ));
    }

    /** Lưu vé (đặt chỗ hoặc tạo payment nếu pay_now=1) */
    public function store(Showtime $showtime, Request $request)
    {
        $data = $request->validate([
            'seat_ids'     => ['required','array','min:1','max:10'],
            'seat_ids.*'   => ['integer'],
            'seat_type_id' => ['required','integer'], // đồng bộ form, không dùng định giá
            'voucher_id'   => ['nullable','integer'],
            'pay_now'      => ['nullable','boolean'],
        ]);

        $userId = Auth::id();
        $payNow = $request->boolean('pay_now'); // true => tạo payment + trang QR

        // Giá hiệu lực
        $sp = DB::table('showtime_prices')
            ->where('showtime_id', $showtime->id)
            ->select('seat_type_id','base_price','price','final_price')
            ->get();

        $effective = [];
        foreach ($sp as $r) {
            $effective[$r->seat_type_id] = !is_null($r->final_price)
                ? (int)$r->final_price
                : (int)($r->base_price ?? 0) + (int)($r->price ?? 0);
        }
        if (empty($effective)) {
            $effective = DB::table('seat_types')
                ->pluck('base_price','id')
                ->map(fn($v)=>(int)$v)->toArray();
        }

        // Lấy voucher (nếu có) + kiểm tra hiệu lực & quota
        $voucher    = null;
        $voucherUse = 0;
        if (!empty($data['voucher_id'])) {
            $voucher = Voucher::lockForUpdate()->find($data['voucher_id']);
            if (!$voucher || !$voucher->isActive()) {
                return back()->withErrors('Mã ưu đãi không hợp lệ hoặc đã hết hạn/quá lượt.')->withInput();
            }
            if (!is_null($voucher->usage_limit)) {
                $remain = $voucher->usage_limit - $voucher->used_count;
                if ($remain < count($data['seat_ids'])) {
                    return back()->withErrors('Mã ưu đãi không đủ lượt cho số ghế bạn chọn.')->withInput();
                }
            }
        }

        $firstPaymentId = null;

        DB::transaction(function () use ($data, $showtime, $userId, $effective, $voucher, $payNow, &$voucherUse, &$firstPaymentId) {
            // Khoá kiểm tra ghế
            $exists = DB::table('tickets')
                ->where('showtime_id',$showtime->id)
                ->whereIn('seat_id',$data['seat_ids'])
                ->where('status','!=','canceled')
                ->lockForUpdate()
                ->exists();
            if ($exists) {
                abort(409, 'Một hoặc nhiều ghế đã bị người khác giữ chỗ. Vui lòng chọn lại.');
            }

            // Lấy ghế
            $rows = DB::table('seats')
                ->whereIn('id',$data['seat_ids'])
                ->select('id','seat_type_id','row_letter','seat_number')->get();

            // Với pay_now = true, nếu user đã có "reserved" cũ -> chuyển failed/canceled để tránh trùng
            if ($payNow) {
                DB::table('tickets')
                    ->where('showtime_id', $showtime->id)
                    ->where('user_id', $userId)
                    ->where('status', 'reserved')
                    ->update(['status' => 'canceled', 'updated_at' => now()]);

                DB::table('payments')
                    ->where('user_id', $userId)
                    ->where('status', 'pending')
                    ->update(['status' => 'failed', 'updated_at' => now()]);
            }

            // Insert tickets & tạo payment per ticket nếu pay_now
            foreach ($rows as $row) {
                $base  = (int)($effective[$row->seat_type_id] ?? 0);
                $final = $base;

                if ($voucher) {
                    if ($voucher->type === 'percent') {
                        $final = max((int)round($base * (100 - (int)$voucher->value) / 100), 0);
                    } else { // fixed
                        $final = max($base - (int)$voucher->value, 0);
                    }
                    $voucherUse++;
                }

                // Nếu chỗ này có vé canceled của GHẾ đó cho chính user, có thể reuse thay vì insert
                $old = DB::table('tickets')
                    ->where('showtime_id', $showtime->id)
                    ->where('seat_id', $row->id)
                    ->where('status', 'canceled')
                    ->lockForUpdate()
                    ->first();

                if ($old) {
                    DB::table('tickets')
                        ->where('id', $old->id)
                        ->update([
                            'user_id'     => $userId,
                            'ticket_type' => 'default',
                            'status'      => 'reserved',
                            'price'       => $base,
                            'final_price' => $final,
                            'voucher_id'  => $voucher->id ?? null,
                            'updated_at'  => now(),
                        ]);
                    $ticketId = $old->id;
                } else {
                    // Unique key (showtime_id, seat_id) đã đảm bảo không trùng
                    $ticketId = DB::table('tickets')->insertGetId([
                        'showtime_id' => $showtime->id,
                        'user_id'     => $userId,
                        'seat_id'     => $row->id,
                        'ticket_type' => 'default',
                        'status'      => 'reserved',      // confirm thanh toán mới chuyển paid
                        'price'       => $base,
                        'final_price' => $final,
                        'voucher_id'  => $voucher->id ?? null,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }

                // Nếu thanh toán ngay -> tạo payment pending 30s cho từng ticket (kèm reference + user_id)
                if ($payNow) {
                    $ref = 'PM' . now()->format('ymdHis') . $userId . str_pad((string)$ticketId, 3, '0', STR_PAD_LEFT);
                
                    // KHÓA và kiểm tra payment cũ của chính ticket này
                    $existingPay = DB::table('payments')
                        ->where('ticket_id', $ticketId)
                        ->lockForUpdate()
                        ->first();
                
                    if ($existingPay) {
                        // Nếu đã PAID thì không cho tạo lại
                        if ($existingPay->status === 'paid') {
                            abort(409, 'Vé này đã được thanh toán trước đó.');
                        }
                    
                        // Tái sử dụng record cũ: set reference mới, đặt lại pending + hạn 30s
                        DB::table('payments')->where('id', $existingPay->id)->update([
                            'user_id'        => $userId,
                            'reference'      => $ref,
                            'amount'         => $final,
                            'payment_method' => 'qr',
                            'status'         => 'pending',
                            'txn_id'         => null,
                            'paid_at'        => null,
                            'expires_at'     => now()->addSeconds(30),
                            'updated_at'     => now(),
                        ]);
                    
                        $pid = $existingPay->id;
                    } else {
                        // Chưa có -> tạo mới
                        $pid = DB::table('payments')->insertGetId([
                            'ticket_id'      => $ticketId,
                            'user_id'        => $userId,
                            'reference'      => $ref,
                            'amount'         => $final,
                            'payment_method' => 'qr',
                            'status'         => 'pending',
                            'txn_id'         => null,
                            'paid_at'        => null,
                            'expires_at'     => now()->addSeconds(30),
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                    }
                
                    if (!$firstPaymentId) $firstPaymentId = $pid;
                }
            }

            if ($voucher && $voucherUse > 0) {
                $voucher->increment('used_count', $voucherUse);
            }
        });

        if ($payNow && $firstPaymentId) {
            return redirect()->route('payments.show', $firstPaymentId);
        }

        return redirect()->route('tickets.history')
            ->with('ok', 'Đặt vé thành công! Bạn có thể thanh toán trong mục “Vé của tôi”.');
    }

    /** Lịch sử vé */
public function history()
{
    $uid = \Illuminate\Support\Facades\Auth::id();

    $tickets = \Illuminate\Support\Facades\DB::table('tickets as t')
        ->join('seats as s', 's.id', '=', 't.seat_id')
        ->join('rooms as r', 'r.id', '=', 's.room_id')
        ->join('showtimes as st', 'st.id', '=', 't.showtime_id')
        ->join('movies as m', 'm.id', '=', 'st.movie_id')
        ->where('t.user_id', $uid)
        ->where('t.status', '!=', 'canceled')
        ->select(
            't.*',
            's.row_letter',
            's.seat_number',
            \Illuminate\Support\Facades\DB::raw("CONCAT(s.row_letter, s.seat_number) as seat_label"),
            'r.name as room_name',
            'st.start_time',
            'm.title as movie_title'
        )
        ->orderBy('t.id', 'desc')
        ->paginate(10);

    return view('tickets.history', compact('tickets'));
}

}
