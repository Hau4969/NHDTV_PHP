<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $r)
    {
        $from = $r->date('from') ?: now()->startOfMonth()->toDateString();
        $to   = $r->date('to')   ?: now()->endOfMonth()->toDateString();

        // Doanh thu (tickets.completed or booked? tuỳ business – dùng booked làm ví dụ)
        $revenue = DB::table('tickets as t')
            ->join('showtimes as st','st.id','=','t.showtime_id')
            ->whereBetween(DB::raw('DATE(st.start_time)'), [$from,$to])
            ->where('t.status','!=','canceled')
            ->sum('t.final_price');

        // Số ghế đã bán / tổng ghế theo các suất trong khoảng
        // Tổng ghế suất = sum(capacity phòng cho mỗi suất) – đây là ước lượng.
        $showtimes = DB::table('showtimes as st')
            ->join('rooms as rm','rm.id','=','st.room_id')
            ->whereBetween(DB::raw('DATE(st.start_time)'), [$from,$to])
            ->select('st.id','rm.capacity')
            ->get();

        $totalCapacity = $showtimes->sum('capacity');

        $sold = DB::table('tickets as t')
            ->join('showtimes as st','st.id','=','t.showtime_id')
            ->whereBetween(DB::raw('DATE(st.start_time)'), [$from,$to])
            ->where('t.status','!=','canceled')
            ->count();

        $occupancy = $totalCapacity > 0 ? round($sold / $totalCapacity * 100, 1) : 0;

        // Top phim theo doanh thu
        $topMovies = DB::table('tickets as t')
            ->join('showtimes as st','st.id','=','t.showtime_id')
            ->join('movies as mv','mv.id','=','st.movie_id')
            ->whereBetween(DB::raw('DATE(st.start_time)'), [$from,$to])
            ->where('t.status','!=','canceled')
            ->groupBy('mv.id','mv.title')
            ->select('mv.title', DB::raw('SUM(t.final_price) as total_rev'), DB::raw('COUNT(*) as tickets'))
            ->orderByDesc('total_rev')
            ->limit(10)
            ->get();

        return view('admin.reports.index', compact('from','to','revenue','occupancy','sold','totalCapacity','topMovies'));
    }
}
