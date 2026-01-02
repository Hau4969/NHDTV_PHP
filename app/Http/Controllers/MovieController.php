<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class MovieController extends Controller
{
    public function index(Request $r)
    {
        $tab   = $r->query('tab', 'now'); // now | upcoming
        $today = Carbon::today();

        // Banner: lấy vài phim có poster
        $banners = DB::table('movies')
            ->whereNotNull('poster_url')
            ->orderByDesc('release_date')
            ->limit(5)
            ->get();

        // ==== ĐANG CHIẾU ====
        $nowShowing = DB::table('movies as m')
            ->leftJoin('showtimes as st', 'st.movie_id', '=', 'm.id')
            ->select('m.id','m.title','m.genre','m.poster_url','m.release_date','m.duration')
            ->where(function ($q) use ($today) {
                $q->where('m.is_now_showing', 1)
                  ->orWhere('m.status', 'now')
                  ->orWhere(function ($q2) use ($today) {
                      $q2->whereNotNull('st.id')
                         ->whereDate('st.start_time', '>=', $today->toDateString());
                  });
            })
            ->groupBy('m.id','m.title','m.genre','m.poster_url','m.release_date','m.duration')
            ->orderBy('m.title')
            ->paginate(24, ['*'], 'page_now')
            ->appends(['tab'=>'now']);

        // ==== SẮP CHIẾU ====
        $upcoming = DB::table('movies as m')
            ->leftJoin('showtimes as st', 'st.movie_id', '=', 'm.id')
            ->select('m.id','m.title','m.genre','m.poster_url','m.release_date','m.duration')
            ->where(function ($q) use ($today) {
                $q->where('m.status', 'upcoming')
                  ->orWhereDate('m.release_date', '>=', $today->toDateString());
            })
            ->where(function ($q) {
                // tránh trùng với đang chiếu nếu bạn đã bật cờ
                $q->whereNull('m.is_now_showing')
                  ->orWhere('m.is_now_showing', 0);
            })
            ->groupBy('m.id','m.title','m.genre','m.poster_url','m.release_date','m.duration')
            ->orderBy('m.release_date')
            ->paginate(24, ['*'], 'page_up')
            ->appends(['tab'=>'upcoming']);

        return view('movies.index', compact('banners','tab','nowShowing','upcoming'));
    }

    public function show($movieId, Request $r)
    {
        // map summary -> description để view dùng $movie->description không lỗi
        $movie = DB::table('movies')
            ->select(
                'id', 'title', 'genre', 'poster_url', 'trailer_url',
                'release_date', 'duration',
                DB::raw('summary as description')
            )
            ->where('id', $movieId)
            ->first();
            
        if (!$movie) {
            return redirect()->route('movies.index')->withErrors('Không tìm thấy phim.');
        }
    
        $date = $r->query('date'); // yyyy-mm-dd
        $day  = $date ? Carbon::parse($date) : Carbon::today();
    
        $showtimes = DB::table('showtimes as st')
            ->join('rooms as rm', 'rm.id', '=', 'st.room_id')
            ->where('st.movie_id', $movieId)
            ->whereDate('st.start_time', $day->toDateString())
            ->orderBy('st.start_time')
            ->select('st.id', 'st.start_time', 'rm.name as room_name', 'rm.id as room_id')
            ->get();
    
        return view('movies.show', compact('movie', 'showtimes', 'day'));
    }

}
