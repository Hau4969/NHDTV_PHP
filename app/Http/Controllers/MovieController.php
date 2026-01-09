<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class MovieController extends Controller
{
    public function index(Request $r)
    {
        $tab   = $r->query('tab', 'now');
        $today = Carbon::today();

        // Banner: Lấy 5 phim mới nhất có poster
        $banners = DB::table('movies')
            ->where('status', 'published')
            ->whereNotNull('poster_url')
            ->orderByDesc('release_date')
            ->limit(5)
            ->get();

        // ==== TAB 1: ĐANG CHIẾU ====
        // Logic: Ngày phát hành <= Hôm nay
        $nowShowing = DB::table('movies as m')
            ->select('m.id', 'm.title', 'm.genre', 'm.poster_url', 'm.release_date', 'm.duration')
            ->where('m.status', 'published')
            ->whereDate('m.release_date', '<=', $today)
            ->orderBy('m.release_date', 'desc')
            ->paginate(24, ['*'], 'page_now')
            ->appends(['tab' => 'now']);

        // ==== TAB 2: SẮP CHIẾU ====
        // Logic: Ngày phát hành > Hôm nay
        $upcoming = DB::table('movies as m')
            ->select('m.id', 'm.title', 'm.genre', 'm.poster_url', 'm.release_date', 'm.duration')
            ->where('m.status', 'published')
            ->whereDate('m.release_date', '>', $today)
            ->orderBy('m.release_date', 'asc')
            ->paginate(24, ['*'], 'page_up')
            ->appends(['tab' => 'upcoming']);

        return view('movies.index', compact('banners', 'tab', 'nowShowing', 'upcoming'));
    }

    public function show($movieId, Request $r)
    {
        // 1. Lấy thông tin phim
        $movie = DB::table('movies')
            ->select(
                'id', 'title', 'genre', 'poster_url', 'trailer_url',
                'release_date', 'duration',
                DB::raw('summary as description')
            )
            ->where('id', $movieId)
            ->where('status', 'published')
            ->first();

        if (!$movie) {
            return redirect()->route('movies.index')->withErrors('Không tìm thấy phim.');
        }

        // 2. Lấy TẤT CẢ suất chiếu sắp tới (từ hiện tại trở về sau)
        $allShowtimes = DB::table('showtimes as st')
            ->join('rooms as rm', 'rm.id', '=', 'st.room_id')
            ->where('st.movie_id', $movieId)
            ->where('st.start_time', '>=', Carbon::now()) // Chỉ lấy suất chưa chiếu
            ->orderBy('st.start_time')
            ->select('st.id', 'st.start_time', 'rm.name as room_name', 'rm.id as room_id')
            ->get();

        // 3. Nhóm suất chiếu theo Ngày (Format: Y-m-d)
        // Kết quả mảng: ['2025-01-05' => [suất A, suất B], '2025-01-06' => [suất C]...]
        $groupedShowtimes = $allShowtimes->groupBy(function($item) {
            return Carbon::parse($item->start_time)->format('Y-m-d');
        });

        return view('movies.show', compact('movie', 'groupedShowtimes'));
    }
}
