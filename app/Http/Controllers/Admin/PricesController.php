<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PricesController extends Controller
{
    public function index(Request $r)
    {
        $movieId = $r->integer('movie_id');
        $roomId  = $r->integer('room_id');
        $date    = $r->date('date'); // null nếu không truyền

        $q = DB::table('showtime_prices as sp')
            ->join('showtimes as st', 'st.id', '=', 'sp.showtime_id')
            ->join('seat_types as stp', 'stp.id', '=', 'sp.seat_type_id')
            ->join('rooms as rm', 'rm.id', '=', 'st.room_id')
            ->join('movies as mv', 'mv.id', '=', 'st.movie_id')
            ->select(
                'sp.id',
                'sp.showtime_id',
                'sp.seat_type_id',
                'sp.price_modifier',
                'st.start_time',
                'st.end_time',
                'rm.name as room_name',
                'mv.title as movie_title',
                'stp.name as seat_type_name',
                'stp.base_price'
            )
            ->orderBy('st.start_time','desc');

        if ($movieId) $q->where('st.movie_id', $movieId);
        if ($roomId)  $q->where('st.room_id',  $roomId);
        if ($date)    $q->whereDate('st.start_time', $date);

        $prices = $q->paginate(20)->withQueryString();

        $movies = DB::table('movies')->orderBy('title')->get(['id','title']);
        $rooms  = DB::table('rooms')->orderBy('name')->get(['id','name']);

        return view('admin.prices.index', compact('prices','movies','rooms','movieId','roomId','date'));
    }

    // Lưu các chỉnh sửa price_modifier theo hàng loạt
    public function store(Request $r)
    {
        $r->validate([
            'price' => ['required','array'],
            'price.*.id' => ['required','integer','exists:showtime_prices,id'],
            'price.*.price_modifier' => ['required','numeric','min:-10000000','max:100000000'],
        ]);
    
        foreach ($r->input('price') as $row) {
        
            // Lấy base_price tương ứng
            $base = DB::table('showtime_prices as sp')
                ->join('seat_types as st', 'st.id', '=', 'sp.seat_type_id')
                ->where('sp.id', $row['id'])
                ->value('st.base_price');
        
            $modifier = (float)$row['price_modifier'];
        
            // Nếu giảm vượt quá giá gốc → báo lỗi
            if ($modifier < -$base) {
                return back()->with('err', "Giảm giá không được vượt quá giá gốc ({$base} đ).");
            }
        
            // Cập nhật
            DB::table('showtime_prices')
                ->where('id', $row['id'])
                ->update([
                    'price_modifier' => $modifier,
                    'updated_at'     => now(),
                ]);
        }
    
        return back()->with('ok', 'Đã lưu thay đổi giá.');
    }


    // Khởi tạo cấu hình giá cho các suất chiếu (tạo bản ghi nếu chưa có)
    public function bootstrap(Request $r)
    {
        $movieId = $r->integer('movie_id');
        $roomId  = $r->integer('room_id');
        $date    = $r->date('date');

        // Lấy các suất chiếu theo bộ lọc (nếu có)
        $st = DB::table('showtimes');
        if ($movieId) $st->where('movie_id', $movieId);
        if ($roomId)  $st->where('room_id',  $roomId);
        if ($date)    $st->whereDate('start_time', $date);

        $showtimes = $st->pluck('id'); // danh sách id suất chiếu

        if ($showtimes->isEmpty()) {
            return back()->with('err', 'Không có suất chiếu phù hợp để khởi tạo.');
        }

        // Lấy các loại ghế
        $seatTypes = DB::table('seat_types')->pluck('id');

        // Tạo rows showtime_prices (modifier = 0) nếu chưa tồn tại
        $rows = [];
        $now = now();
        foreach ($showtimes as $sid) {
            foreach ($seatTypes as $tid) {
                $rows[] = [
                    'showtime_id'   => $sid,
                    'seat_type_id'  => $tid,
                    'price_modifier'=> 0,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }
        }

        // upsert theo (showtime_id, seat_type_id)
        DB::table('showtime_prices')->upsert(
            $rows,
            ['showtime_id', 'seat_type_id'], // unique-by
            ['price_modifier', 'updated_at'] // columns update
        );

        return back()->with('ok', 'Đã khởi tạo cấu hình giá cho các suất chiếu.');
    }
}
