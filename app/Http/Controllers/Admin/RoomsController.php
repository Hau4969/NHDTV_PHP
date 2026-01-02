<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

    public function destroy(Room $room)
    {
        return DB::transaction(function () use ($room) {
            if (DB::table('showtimes')->where('room_id', $room->id)->exists()) {
                return back()->withErrors('Phòng đã có suất chiếu, không thể xoá.');
            }

            if (
                Schema::hasTable('tickets') && Schema::hasTable('seats') &&
                DB::table('tickets')->whereIn(
                    'seat_id',
                    DB::table('seats')->where('room_id', $room->id)->pluck('id')
                )->exists()
            ) {
                return back()->withErrors('Phòng có ghế đã được đặt vé, không thể xoá.');
            }

            DB::table('seats')->where('room_id', $room->id)->delete();
            $room->delete();
            return redirect()->route('admin.rooms.index')->with('ok', 'Đã xoá phòng chiếu.');
        });
    }

    /**
     * Sinh ghế theo schema hiện có.
     * rows: số hàng (0 = auto), cols: ghế/hàng (mặc định 10), reset: xoá ghế cũ (bị chặn nếu có vé).
     */
    public function generateSeats(Room $room, Request $r)
    {
        $r->validate([
            'rows'  => ['nullable','integer','min:0'],
            'cols'  => ['nullable','integer','min:1','max:100'],
            'reset' => ['nullable','boolean'],
        ]);

        $cols    = (int)($r->input('cols', 10));
        $rows    = (int)($r->input('rows', 0)); // 0 = auto
        $doReset = (bool)$r->boolean('reset', false);

        $hasRow   = Schema::hasColumn('seats','row_letter');
        $hasNum   = Schema::hasColumn('seats','seat_number');
        $hasCode  = Schema::hasColumn('seats','code');
        $hasLabel = Schema::hasColumn('seats','label');

        if (!($hasRow && $hasNum) && !$hasCode && !$hasLabel) {
            return back()->withErrors("Không thể tạo ghế: bảng seats thiếu (row_letter/seat_number) hoặc (code/label).");
        }

        $seatTypeId = DB::table('seat_types')->where('name','Thường')->value('id');

        return DB::transaction(function () use ($room, $cols, $rows, $doReset, $hasRow, $hasNum, $hasCode, $hasLabel, $seatTypeId) {
            // CHẶN reset nếu có vé tham chiếu
            if ($doReset) {
                $seatIds = DB::table('seats')->where('room_id', $room->id)->pluck('id');
                if ($seatIds->isNotEmpty()) {
                    $used = DB::table('tickets')->whereIn('seat_id', $seatIds)->count();
                    if ($used > 0) {
                        return back()->withErrors(
                            "Không thể reset ghế vì có {$used} vé đang tham chiếu ghế của phòng {$room->name}. ".
                            "Gợi ý: bỏ chọn 'Reset' hoặc huỷ/điều chỉnh các vé liên quan."
                        );
                    }
                }
                DB::table('seats')->where('room_id', $room->id)->delete();
            }

            $capacity = (int)$room->capacity;
            if ($capacity <= 0) return back()->withErrors('Sức chứa phòng phải > 0.');

            if ($rows <= 0) {
                $rows = (int)ceil($capacity / max(1, $cols));
            }

            $letters = $this->letters($rows);
            $made = 0;

            foreach ($letters as $letter) {
                for ($n = 1; $n <= $cols; $n++) {
                    if ($made >= $capacity) break 2;

                    if ($hasRow && $hasNum) {
                        DB::table('seats')->insert([
                            'room_id'      => $room->id,
                            'row_letter'   => $letter,
                            'seat_number'  => $n,
                            'seat_type_id' => $seatTypeId,
                        ]);
                    } elseif ($hasCode) {
                        DB::table('seats')->insert([
                            'room_id'      => $room->id,
                            'code'         => $letter.$n,
                            'seat_type_id' => $seatTypeId,
                        ]);
                    } else {
                        DB::table('seats')->insert([
                            'room_id'      => $room->id,
                            'label'        => $letter.$n,
                            'seat_type_id' => $seatTypeId,
                        ]);
                    }

                    $made++;
                }
            }

            return back()->with('ok', "Đã tạo {$made} ghế cho phòng {$room->name}.");
        });
    }

    /**
     * Cắt bớt ghế dư (an toàn): chỉ xóa ghế KHÔNG bị vé tham chiếu, cho đến khi số ghế = capacity.
     */
    public function trimSeats(Room $room)
    {
        return DB::transaction(function () use ($room) {
            $capacity = (int)$room->capacity;
            $allSeatIds = DB::table('seats')->where('room_id', $room->id)->orderByDesc('id')->pluck('id'); // xóa ghế mới trước
            $total = $allSeatIds->count();

            if ($total <= $capacity) {
                return back()->with('ok', 'Không có ghế dư để cắt.');
            }

            $referenced = DB::table('tickets')->whereIn('seat_id', $allSeatIds)->pluck('seat_id')->unique();
            $free = $allSeatIds->diff($referenced);

            $needRemove = max(0, $total - $capacity);
            if ($free->isEmpty()) {
                return back()->withErrors('Tất cả ghế dư đều đang bị vé tham chiếu, không thể cắt.');
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
        $s = ''; $num += 1;
        while ($num > 0) { $mod = ($num - 1) % 26; $s = chr(65 + $mod).$s; $num = intdiv($num - 1, 26); }
        return $s;
    }
}
