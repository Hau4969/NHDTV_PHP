<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Movie;
use Carbon\Carbon;
class MoviesController extends Controller
{



public function store(Request $r)
{
    $data = $r->validate([
        'title'         => ['required','string','max:255'],
        'genre'         => ['required','string','max:255'],
        'duration'      => ['required','integer','min:1','max:600'],
        'release_date'  => ['required','string'],      // input type=date => Y-m-d
        'director'      => ['nullable','string','max:255'],
        'cast'          => ['nullable','string','max:1000'],
        'summary'       => ['nullable','string'],
        'poster_url'    => ['nullable','url','max:2048'],
        'poster_thumb'  => ['nullable','url','max:2048'],
        'trailer_url'   => ['nullable','url','max:2048'],
        'status'        => ['nullable','in:published,draft'],
        'is_now_showing'=> ['nullable','boolean'],
    ]);

    // checkbox → 0/1
    $data['is_now_showing'] = $r->boolean('is_now_showing');

    // chuẩn hóa ngày về Y-m-d
    $data['release_date'] = $this->normalizeDate($data['release_date']);

    Movie::create($data);
    return redirect()->route('admin.movies.index')->with('ok','Đã thêm phim.');
}

public function update(Request $r, Movie $movie)
{
    $data = $r->validate([
        'title'         => ['required','string','max:255'],
        'genre'         => ['required','string','max:255'],
        'duration'      => ['required','integer','min:1','max:600'],
        'release_date'  => ['required','string'],
        'director'      => ['nullable','string','max:255'],
        'cast'          => ['nullable','string','max:1000'],
        'summary'       => ['nullable','string'],
        'poster_url'    => ['nullable','url','max:2048'],
        'poster_thumb'  => ['nullable','url','max:2048'],
        'trailer_url'   => ['nullable','url','max:2048'],
        'status'        => ['nullable','in:published,draft'],
        'is_now_showing'=> ['nullable','boolean'],
    ]);

    $data['is_now_showing'] = $r->boolean('is_now_showing');
    $data['release_date']   = $this->normalizeDate($data['release_date']);

    $movie->update($data);
    return redirect()->route('admin.movies.index')->with('ok','Cập nhật phim thành công.');
}

// Helper
private function normalizeDate(string $v): string
{
    if (Carbon::hasFormat($v,'Y-m-d')) return Carbon::createFromFormat('Y-m-d',$v)->format('Y-m-d');
    if (Carbon::hasFormat($v,'d/m/Y')) return Carbon::createFromFormat('d/m/Y',$v)->format('Y-m-d');
    return Carbon::parse($v)->format('Y-m-d');
}


    public function destroy(Movie $movie)
    {
        $movie->delete();
        return back()->with('ok', 'Đã xóa phim.');
    }
    public function index()
    {
        $movies = Movie::orderByDesc('created_at')->paginate(12);
        return view('admin.movies.index', compact('movies'));
    }

    public function create()
    {
        $movie = new Movie(['is_active' => 1]);
        return view('admin.movies.form', compact('movie'));
    }

    public function edit(Movie $movie)
    {
        return view('admin.movies.form', compact('movie'));
    }

}
