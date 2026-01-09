<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

// Import các model
use App\Models\Movie;
use App\Models\Event;
use App\Models\Post;

class HomeController extends Controller
{
    /**
     * Trang chủ khách hàng
     */
    public function index()
    {
        $now = Carbon::now();

        // 1. NOW SHOWING (Phim có vé / Đang chiếu / Suất chiếu sớm)
        // Logic: Phim đã publish + Có ít nhất 1 suất chiếu từ thời điểm hiện tại trở đi
        $nowShowing = $this->safeMoviesQuery(function () use ($now) {
            return Movie::query()
                ->where('status', 'published')
                ->whereHas('showtimes', function($q) use ($now) {
                    $q->where('start_time', '>=', $now);
                })
                // Đếm số suất chiếu để ưu tiên phim có nhiều suất lên đầu (hoặc dùng release_date tùy bạn)
                ->withCount(['showtimes' => function ($q) use ($now) {
                    $q->where('start_time', '>=', $now);
                }])
                ->orderByDesc('showtimes_count')
                ->take(12)
                ->get();
        });

        // 2. COMING SOON (Phim sắp chiếu)
        // Logic: Phim đã publish + Ngày khởi chiếu > Hôm nay
        // (Vẫn hiển thị ở đây để PR, dù có thể đã có suất chiếu sớm ở mục trên)
        $comingSoon = $this->safeMoviesQuery(function () use ($now) {
            return Movie::query()
                ->where('status', 'published')
                ->whereDate('release_date', '>', $now)
                ->orderBy('release_date', 'asc')
                ->take(12)
                ->get();
        });

        // 3. BOX OFFICE (Top doanh thu)
        $boxOffice = $this->safeMoviesQuery(function () {
            return Movie::query()
                ->leftJoin('tickets', 'tickets.movie_id', '=', 'movies.id')
                ->select('movies.*', DB::raw('COALESCE(SUM(tickets.price),0) AS revenue'))
                ->where('movies.status', 'published')
                ->groupBy('movies.id')
                ->orderByDesc('revenue')
                ->take(8)
                ->get();
        }, fallback: fn () => $this->mapRevenueZero($nowShowing));

        // 4. Featured (Phim nổi bật)
        $featured = $boxOffice->where('revenue', '>', 0)->first()
            ?? $nowShowing->first()
            ?? $comingSoon->first();

        // 5. Events
        $events = $this->safeQuery(function () {
            return class_exists(Event::class)
                ? Event::query()->when($this->hasColumn('events', 'is_public'), fn($q) => $q->where('is_public', true))
                    ->latest()->take(3)->get()
                : collect();
        });

        // 6. Notices
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
       Helpers
       ========================= */

    protected function safeMoviesQuery(\Closure $cb, \Closure $fallback = null): Collection
    {
        try {
            if (!class_exists(Movie::class)) return collect();
            return value($cb) ?? collect();
        } catch (\Throwable $e) {
            return $fallback ? value($fallback) : collect();
        }
    }

    protected function safeQuery(\Closure $cb): Collection
    {
        try {
            $res = value($cb);
            return $res instanceof Collection ? $res : collect($res);
        } catch (\Throwable $e) {
            return collect();
        }
    }

    protected function mapRevenueZero(Collection $movies): Collection
    {
        return $movies->map(function ($m) {
            $m->revenue = 0;
            return $m;
        });
    }

    protected function hasColumn(string $table, string $column): bool
    {
        try {
            return DB::getSchemaBuilder()->hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
