<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\SeatType;
use App\Models\Seat;

class RoomsController extends Controller
{
    public function index()
    {
        $rooms = Room::orderBy('name')->paginate(15);

        // Đếm ghế hiện có theo phòng
        $seatCounts = DB::table('seats')
            ->select('room_id', DB::raw('COUNT(*) as c'))
            ->whereIn('room_id', $rooms->pluck('id'))
            ->groupBy('room_id')
            ->pluck('c', 'room_id');

        foreach ($rooms as $r) {
            $r->seats_count = $seatCounts[$r->id] ?? 0;
        }

        return view('admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('admin.rooms.form', ['room' => new Room(), 'mode' => 'create']);
    }

    public function store(Request $r)
    {
        $r->validate([
            'name'     => ['required','string','max:100', Rule::unique('rooms','name')],
            'capacity' => ['required','integer','min:1','max:1000'],
        ]);

        Room::create($r->only('name','capacity'));
        return redirect()->route('admin.rooms.index')->with('ok', 'Đã tạo phòng chiếu.');
    }

    public function edit(Room $room)
    {
        return view('admin.rooms.form', ['room' => $room, 'mode' => 'edit']);
    }

    public function update(Request $r, Room $room)
    {
        $r->validate([
            'name'     => ['required','string','max:100', Rule::unique('rooms','name')->ignore($room->id)],
            'capacity' => ['required','integer','min:1','max:1000'],
        ]);

        $room->update($r->only('name','capacity'));
        return redirect()->route('admin.rooms.index')->with('ok', 'Đã cập nhật phòng chiếu.');
    }

    /**
     * Xoá phòng:
     * - Nếu có suất chiếu đã có vé -> chặn + thông báo đẹp
     * - Nếu có lịch sử vé theo seat_id -> chặn + thông báo đẹp
     * - Nếu ok: xoá prices, showtimes, seats, room
     */
    public function destroy(Room $room)
    {
        try {
            DB::transaction(function () use ($room) {

                // 1) Lấy tất cả suất chiếu thuộc phòng này
                $showtimes = \App\Models\Showtime::where('room_id', $room->id)->get();

                foreach ($showtimes as $st) {
                    // Nếu suất chiếu đã có vé -> không cho xoá
                    if ($st->tickets()->exists()) {
                        $time = $st->start_time;
                        throw new \Exception(
                            "Không thể xoá phòng \"{$room->name}\" vì suất chiếu lúc {$time} đã có người mua vé. "
                            . "Vui lòng xử lý (huỷ/hoàn) các vé liên quan trước khi xoá."
                        );
                    }

                    // Xoá bảng giá (prices) rồi mới xoá suất chiếu
                    $st->prices()->delete();
                    $st->delete();
                }

                // 2) Kiểm tra lịch sử vé theo ghế (dù showtime đã xoá/không còn)
                $seatIds = DB::table('seats')->where('room_id', $room->id)->pluck('id');

                if ($seatIds->isNotEmpty() && DB::table('tickets')->whereIn('seat_id', $seatIds)->exists()) {
                    throw new \Exception(
                        "Không thể xoá phòng \"{$room->name}\" vì phòng có ghế đã từng được đặt vé (có dữ liệu lịch sử). "
                        . "Vui lòng giữ phòng để bảo toàn dữ liệu hoặc xử lý dữ liệu vé trước khi xoá."
                    );
                }

                // 3) Xoá ghế và phòng
                DB::table('seats')->where('room_id', $room->id)->delete();
                $room->delete();
            });

            return redirect()->route('admin.rooms.index')
                ->with('ok', 'Đã xoá phòng, bao gồm cả ghế và các suất chiếu chưa bán vé.');

        } catch (\Throwable $e) {
            // Trả lỗi về UI (không văng 500)
            return back()->withErrors([$e->getMessage()]);
        }
    }

    /**
     * Sinh ghế theo schema hiện có.
     * rows: số hàng (0 = auto), cols: ghế/hàng (mặc định 10), reset: xoá ghế cũ (bị chặn nếu có vé).
     */
    public function generateSeats(Request $request, Room $room)
    {
        // 1. Validate dữ liệu mở rộng
        $data = $request->validate([
            'rows'         => 'required|integer|min:1|max:26',
            'cols'         => 'required|integer|min:1|max:50',

            // Thêm các trường nhập phạm vi cho 3 loại ghế
            'normal_from'  => 'nullable|integer',
            'normal_to'    => 'nullable|integer',
            'vip_from'     => 'nullable|integer',
            'vip_to'       => 'nullable|integer',
            'couple_from'  => 'nullable|integer',
            'couple_to'    => 'nullable|integer',

            'reset'        => 'nullable|boolean',
        ]);

        $rows = (int)$data['rows'];
        $cols = (int)$data['cols'];

        // Lấy cấu hình phạm vi (nếu không nhập thì coi như bằng 0)
        $normFrom = $data['normal_from'] ?? 0;
        $normTo   = $data['normal_to']   ?? 0;
        $vipFrom  = $data['vip_from']    ?? 0;
        $vipTo    = $data['vip_to']      ?? 0;
        $cplFrom  = $data['couple_from'] ?? 0;
        $cplTo    = $data['couple_to']   ?? 0;

        // 2. Lấy/Tạo loại ghế
        $normalType = SeatType::firstOrCreate(['name' => 'Thường'], ['base_price' => 80000])->id;
        $vipType    = SeatType::firstOrCreate(['name' => 'VIP'],    ['base_price' => 110000])->id;
        $coupleType = SeatType::firstOrCreate(['name' => 'Đôi'],    ['base_price' => 150000])->id;


        // 3. Reset ghế cũ
        if ($request->has('reset')) {
            $seatIds = $room->seats()->pluck('id');
            if (\App\Models\Ticket::whereIn('seat_id', $seatIds)->exists()) {
                return back()->withErrors(['error' => 'Không thể reset: Phòng này có ghế đã bán vé!']);
            }
            $room->seats()->delete();
        }

        // 4. Tạo ghế
        $seats = [];
        $now = now();

        for ($r = 1; $r <= $rows; $r++) {
            $rowLabel = chr(64 + $r);

            // === LOGIC XÁC ĐỊNH LOẠI GHẾ ===
            $typeId = $normalType;

            if ($r >= $cplFrom && $r <= $cplTo) {
                $typeId = $coupleType;
            } elseif ($r >= $vipFrom && $r <= $vipTo) {
                $typeId = $vipType;
            } elseif ($r >= $normFrom && $r <= $normTo) {
                $typeId = $normalType;
            }

            // Ghế đôi: số ghế = 1/2
            $colsInRow = ($typeId == $coupleType) ? floor($cols / 2) : $cols;

            for ($c = 1; $c <= $colsInRow; $c++) {
                $seats[] = [
                    'room_id'      => $room->id,
                    'row_letter'   => $rowLabel,
                    'seat_number'  => $c,
                    'seat_type_id' => $typeId,
                    'status'       => 'active',
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }
        }

        if (!empty($seats)) {
            \App\Models\Seat::insert($seats);
        }

        return back()->with('ok', "Đã tạo sơ đồ ghế ({$rows} hàng x {$cols} cột) thành công!");
    }

    /**
     * Cắt bớt ghế dư (an toàn): chỉ xóa ghế KHÔNG bị vé tham chiếu, cho đến khi số ghế = capacity.
     */
    public function trimSeats(Room $room)
    {
        return DB::transaction(function () use ($room) {
            $capacity = (int)$room->capacity;
            $allSeatIds = DB::table('seats')
                ->where('room_id', $room->id)
                ->orderByDesc('id')
                ->pluck('id');

            $total = $allSeatIds->count();

            if ($total <= $capacity) {
                return back()->with('ok', 'Không có ghế dư để cắt.');
            }

            $referenced = DB::table('tickets')->whereIn('seat_id', $allSeatIds)->pluck('seat_id')->unique();
            $free = $allSeatIds->diff($referenced);

            $needRemove = max(0, $total - $capacity);
            if ($free->isEmpty()) {
                return back()->withErrors(['error' => 'Tất cả ghế dư đều đang bị vé tham chiếu, không thể cắt.']);
            }

            $toDelete = $free->take($needRemove);
            DB::table('seats')->whereIn('id', $toDelete)->delete();

            $deleted = $toDelete->count();
            return back()->with('ok', "Đã cắt {$deleted} ghế dư (không bị vé tham chiếu).");
        });
    }

    // Helpers
    protected function letters(int $count): array
    {
        $out = [];
        for ($i = 0; $i < $count; $i++) $out[] = $this->numberToLetters($i);
        return $out;
    }

    protected function numberToLetters(int $num): string
    {
        $s = '';
        $num += 1;
        while ($num > 0) {
            $mod = ($num - 1) % 26;
            $s = chr(65 + $mod) . $s;
            $num = intdiv($num - 1, 26);
        }
        return $s;
    }
}
