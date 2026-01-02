<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// Các model có thể có trong dự án
use App\Models\Movie;
use App\Models\Event; // nếu chưa có, khối try/catch sẽ fallback
use App\Models\Post;  // nếu chưa có, khối try/catch sẽ fallback

class HomeController extends Controller
{
    /**
     * Trang chủ khách hàng
     */
    public function index()
    {
        // ------ NOW SHOWING ------
        $nowShowing = $this->safeMoviesQuery(function () {
            return Movie::query()
                ->where('status', 'published')
                ->when($this->hasColumn('movies', 'is_now_showing'), fn($q) => $q->where('is_now_showing', true))
                ->when($this->hasColumn('movies', 'release_date'), fn($q) => $q->latest('release_date'))
                ->latest('id')
                ->take(12)
                ->get();
        });

        // ------ COMING SOON ------
        $comingSoon = $this->safeMoviesQuery(function () {
            return Movie::query()
                ->where('status', 'published')
                ->when($this->hasColumn('movies', 'is_now_showing'), fn($q) => $q->where('is_now_showing', false))
                ->when($this->hasColumn('movies', 'release_date'), fn($q) => $q->orderBy('release_date'))
                ->latest('id')
                ->take(12)
                ->get();
        });

        // ------ BOX OFFICE (top doanh thu) ------
        $boxOffice = $this->safeMoviesQuery(function () {
            // Nếu chưa có bảng tickets, truy vấn sẽ ném exception -> được catch phía dưới
            return Movie::query()
                ->leftJoin('tickets', 'tickets.movie_id', '=', 'movies.id')
                ->select('movies.*', DB::raw('COALESCE(SUM(tickets.price),0) AS revenue'))
                ->groupBy('movies.id')
                ->orderByDesc('revenue')
                ->take(8)
                ->get();
        }, fallback: fn () => $this->mapRevenueZero($nowShowing));

        // ------ Featured (ưu tiên top Box Office > Now Showing > Coming Soon) ------
        $featured = $boxOffice->first()
            ?? $nowShowing->first()
            ?? $comingSoon->first();

        // ------ Events ------
        $events = $this->safeQuery(function () {
            // Nếu không có model Event hoặc bảng tương ứng → fallback collection rỗng
            return class_exists(Event::class)
                ? Event::query()->when($this->hasColumn('events', 'is_public'), fn($q) => $q->where('is_public', true))
                    ->latest()->take(3)->get()
                : collect();
        });

        // ------ Notices (thông báo) ------
        $notices = $this->safeQuery(function () {
            return class_exists(Post::class)
                ? Post::query()->when($this->hasColumn('posts', 'type'), fn($q) => $q->where('type', 'notice'))
                    ->latest()->take(4)->get()
                : collect();
        });

        return view('customer.home', compact(
            'featured', 'boxOffice', 'nowShowing', 'comingSoon', 'events', 'notices'
        ));
    }

    /* =========================
       Helpers an toàn / tiện ích
       ========================= */

    /**
     * Chạy query trên Movie một cách an toàn.
     * @param  \Closure $cb
     * @param  \Closure|null $fallback
     * @return \Illuminate\Support\Collection
     */
    protected function safeMoviesQuery(\Closure $cb, \Closure $fallback = null): Collection
    {
        try {
            if (!class_exists(Movie::class)) {
                return collect();
            }
            return value($cb) ?? collect();
        } catch (\Throwable $e) {
            return $fallback ? value($fallback) : collect();
        }
    }

    /**
     * Chạy 1 query bất kỳ an toàn.
     */
    protected function safeQuery(\Closure $cb): Collection
    {
        try {
            $res = value($cb);
            return $res instanceof Collection ? $res : collect($res);
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /**
     * Nếu không join được tickets, set revenue = 0 cho danh sách phim đầu vào.
     */
    protected function mapRevenueZero(Collection $movies): Collection
    {
        return $movies->map(function ($m) {
            // append thuộc tính ảo revenue để view dùng number_format
            $m->revenue = 0;
            return $m;
        });
    }

    /**
     * Kiểm tra bảng có cột không (tránh lỗi trên môi trường dev khi chưa migrate đủ).
     */
    protected function hasColumn(string $table, string $column): bool
    {
        try {
            return DB::getSchemaBuilder()->hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
