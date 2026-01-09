<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Showtime, Movie, Room, SeatType}; // Thêm SeatType
use Illuminate\Support\Facades\DB; // Thêm DB để insert nhanh
use Carbon\Carbon;

class ShowtimesController extends Controller

{
    public function index()
    {
        $showtimes = Showtime::with(['movie', 'room'])
            ->orderByDesc('start_time')
            ->paginate(12);

        return view('admin.showtimes.index', compact('showtimes'));
    }

    public function create()
    {
        $movies = Movie::orderBy('title')->get(['id','title','duration']);
        $rooms  = Room::orderBy('name')->get(['id','name']);
        $showtime = new Showtime();
        return view('admin.showtimes.form', compact('showtime','movies','rooms'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'movie_id'   => 'required|exists:movies,id',
            'room_id'    => 'required|exists:rooms,id',
            'start_time' => 'required|date|after:now', // Thêm after:now để không tạo suất trong quá khứ
            'end_time'   => 'nullable|date|after:start_time',
        ]);

        // 1. Tính end_time nếu chưa nhập (dùng duration của movie)
        if (empty($data['end_time'])) {
            $movie = Movie::find($data['movie_id']);
            $duration = (int)($movie->duration ?? 120);
            $data['end_time'] = Carbon::parse($data['start_time'])->addMinutes($duration);
        }

        $start = Carbon::parse($data['start_time']);
        $end   = Carbon::parse($data['end_time']);
        $roomId = $data['room_id'];
        $bufferMinutes = 30; // thời gian nghỉ tối thiểu giữa 2 phim

        // 2. ===== CHECK TRÙNG GIỜ + NGHỈ 30P TRONG CÙNG PHÒNG =====
        $existingShowtimes = Showtime::where('room_id', $roomId)->get(['id','start_time','end_time']);

        foreach ($existingShowtimes as $st) {
            $stStart = Carbon::parse($st->start_time);
            $stEnd   = Carbon::parse($st->end_time);

            // Nếu suất chiếu hiện tại bắt đầu trước hoặc bằng suất mới
            if ($stStart->lte($start)) {
                // Suất mới phải bắt đầu SAU khi phim cũ kết thúc + 30p
                $minStartNew = $stEnd->copy()->addMinutes($bufferMinutes);
                if ($start->lt($minStartNew)) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'start_time' => 'Phòng này đã có suất chiếu từ '
                                . $stStart->format('H:i d/m')
                                . ' đến ' . $stEnd->format('H:i d/m')
                                . '. Cần cách ít nhất ' . $bufferMinutes
                                . ' phút mới được chiếu phim mới.',
                        ]);
                }
            } else {
                // Suất mới diễn ra TRƯỚC suất đang xét
                // Suất cũ phải bắt đầu SAU khi phim mới kết thúc + 30p
                $minStartOld = $end->copy()->addMinutes($bufferMinutes);
                if ($stStart->lt($minStartOld)) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'start_time' => 'Phòng này đã có suất chiếu lúc '
                                . $stStart->format('H:i d/m')
                                . '. Phim mới phải kết thúc trước đó ít nhất '
                                . $bufferMinutes . ' phút.',
                        ]);
                }
            }
        }
        // ===== HẾT PHẦN CHECK =====

        // 3. Tạo suất chiếu
        $showtime = Showtime::create($data);

        // 4. ===== TỰ ĐỘNG KHỞI TẠO GIÁ VÉ (cho tất cả loại ghế) =====
        $seatTypes = SeatType::pluck('id'); // Lấy danh sách ID loại ghế
        $priceData = [];
        $now = now();

        foreach ($seatTypes as $typeId) {
            $priceData[] = [
                'showtime_id'    => $showtime->id,
                'seat_type_id'   => $typeId,
                'price_modifier' => 0, // Giá mặc định (bằng giá gốc)
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        if (!empty($priceData)) {
            DB::table('showtime_prices')->insert($priceData);
        }
        // ============================================================

        return redirect()->route('admin.showtimes.index')
            ->with('ok','Đã thêm suất chiếu và khởi tạo giá vé thành công.');
    }

    public function edit(Showtime $showtime)
    {
        $movies = Movie::orderBy('title')->get(['id','title']);
        $rooms  = Room::orderBy('name')->get(['id','name']);
        return view('admin.showtimes.form', compact('showtime','movies','rooms'));
    }

    public function update(Request $r, Showtime $showtime)
    {
        $data = $r->validate([
            'movie_id'   => 'required|exists:movies,id',
            'room_id'    => 'required|exists:rooms,id',
            'start_time' => 'required|date',
            'end_time'   => 'nullable|date|after:start_time',
        ]);

        if (empty($data['end_time'])) {
            $movie = Movie::find($data['movie_id']);
            $duration = (int)($movie->duration ?? 120);
            $data['end_time'] = Carbon::parse($data['start_time'])->addMinutes($duration);
        }

        $start = Carbon::parse($data['start_time']);
        $end   = Carbon::parse($data['end_time']);
        $roomId = $data['room_id'];
        $bufferMinutes = 30;

        // Lấy các suất khác trong cùng phòng (trừ chính nó)
        $existingShowtimes = Showtime::where('room_id', $roomId)
            ->where('id', '!=', $showtime->id)
            ->get(['id','start_time','end_time']);

        foreach ($existingShowtimes as $st) {
            $stStart = Carbon::parse($st->start_time);
            $stEnd   = Carbon::parse($st->end_time);

            if ($stStart->lte($start)) {
                $minStartNew = $stEnd->copy()->addMinutes($bufferMinutes);
                if ($start->lt($minStartNew)) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'start_time' => 'Phòng này đã có suất chiếu từ '
                                . $stStart->format('H:i d/m')
                                . ' đến ' . $stEnd->format('H:i d/m')
                                . '. Cần cách ít nhất ' . $bufferMinutes
                                . ' phút mới được chiếu phim mới.',
                        ]);
                }
            } else {
                $minStartOld = $end->copy()->addMinutes($bufferMinutes);
                if ($stStart->lt($minStartOld)) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'start_time' => 'Phòng này đã có suất chiếu lúc '
                                . $stStart->format('H:i d/m')
                                . '. Phim mới phải kết thúc trước đó ít nhất '
                                . $bufferMinutes . ' phút.',
                        ]);
                }
            }
        }

        $showtime->update($data);
        return redirect()->route('admin.showtimes.index')->with('ok','Cập nhật thành công.');
    }

    public function destroy(Showtime $showtime)
    {
        // 1. Kiểm tra vé đã bán chưa
        if ($showtime->tickets()->exists()) {
             return back()->withErrors(['error' => 'Không thể xóa: Suất chiếu này đã có vé được bán!']);
        }

        // 2. Xóa các bảng giá (ShowtimePrice) thuộc suất chiếu này trước
        // (Bước này khắc phục lỗi Integrity constraint violation)
        $showtime->prices()->delete();

        // 3. Sau đó mới xóa suất chiếu
        $showtime->delete();

        return back()->with('ok','Đã xoá suất chiếu và giá vé đi kèm.');
    }
}

