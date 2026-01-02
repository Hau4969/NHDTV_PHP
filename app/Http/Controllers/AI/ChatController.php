<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __invoke(Request $request, GeminiService $ai)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'history' => ['nullable', 'array'],
            'mode'    => ['nullable', 'in:now,soon,all'],
        ]);

        $mode = $request->input('mode', 'now'); // now|soon|all

        $q = Movie::query()->where('status', 'published');

        if ($mode === 'now') {
            $q->where('is_now_showing', 1)->orderBy('release_date', 'desc');
        } elseif ($mode === 'soon') {
            $q->where('is_now_showing', 0)->orderBy('release_date', 'asc');
        } else {
            $q->orderBy('is_now_showing', 'desc')->orderBy('release_date', 'asc');
        }

        $movies = $q->limit(30)
            ->get(['id','title','genre','duration','release_date','summary'])
            ->toArray();

        $answer = $ai->chat(
            $request->input('message'),
            $movies,
            $mode,
            $request->input('history', [])
        );

        return response()->json(['answer' => $answer]);
    }
}
