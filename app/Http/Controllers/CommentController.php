<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Movie;

class CommentController extends Controller
{
    public function store(Request $request, $id)
    {
        // Xác thực dữ liệu
        $validated = $request->validate([
            'user_name' => 'required|string|max:100',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|max:1000',
        ]);

        // Kiểm tra phim tồn tại
        $movies = Movie::findOrFail($id);

        // Lưu bình luận mới
        Comment::create([
            'movie_id' => $movies->id,
            'user_name' => $validated['user_name'],
            'rating' => $validated['rating'],
            'content' => $validated['content'],
        ]);

        // Quay lại trang chi tiết phim
        return redirect()->route('movies.movieshow', $movies->id)->with('success', 'Bình luận đã được gửi!');
    }
}
