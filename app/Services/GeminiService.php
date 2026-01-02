<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public function chat(string $userMessage, array $movies = [], string $mode = 'now', array $history = []): string
    {
        $userMessage = trim($userMessage);
        if ($userMessage === '') return 'Bạn muốn hỏi gì nè? 😊';

        $apiKey = env('GEMINI_API_KEY');
        $model  = env('GEMINI_MODEL', 'gemini-2.5-flash');
        $base   = rtrim(env('GEMINI_API_BASE', 'https://generativelanguage.googleapis.com/v1beta'), '/');

        if (!$apiKey) return '⚠️ Chưa cấu hình GEMINI_API_KEY.';

        $contextLabel = match ($mode) {
            'soon' => 'PHIM SẮP CHIẾU',
            'all'  => 'TẤT CẢ PHIM',
            default => 'PHIM ĐANG CHIẾU',
        };

        $movieText = collect($movies)->map(function ($m) {
            $id    = $m['id'] ?? '';
            $title = $m['title'] ?? '';
            $genre = $m['genre'] ?? '';
            $dur   = $m['duration'] ?? '';
            $date  = $m['release_date'] ?? '';
            $sum   = mb_substr((string)($m['summary'] ?? ''), 0, 160);

            return "- [{$id}] {$title} | {$genre} | {$dur} phút | {$date} | {$sum}";
        })->implode("\n");

        $dbNote = empty($movies)
            ? "LƯU Ý: Danh sách {$contextLabel} hiện trống trong DB."
            : "DANH SÁCH {$contextLabel} (nguồn DB):\n{$movieText}";

        $system = <<<SYS
Bạn là trợ lý hội thoại kiểu ChatGPT cho website đặt vé xem phim (tiếng Việt, thân thiện).

- Người dùng hỏi gì cũng trả lời được.
- Nhưng nếu liên quan đến phim (đang chiếu/sắp chiếu/gợi ý/theo thể loại/chi tiết...):
  CHỈ được dùng phim trong danh sách DB bên dưới, KHÔNG bịa.

Nếu liệt kê/gợi ý:
- 3–6 phim
- Mỗi phim format: "Tên phim (ID: X) — lý do ngắn. Xem: /movies/X"

{$dbNote}
SYS;

        // Gemini chỉ nhận role: user / model
        $contents = [];

        // Nhét hướng dẫn vào lượt user đầu
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $system]],
        ];

        foreach (array_slice($history, -10) as $h) {
            $role = ($h['role'] ?? '') === 'assistant' ? 'model' : 'user';
            $text = (string)($h['content'] ?? '');
            if (trim($text) === '') continue;

            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $text]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userMessage]],
        ];

        $url = "{$base}/models/{$model}:generateContent?key={$apiKey}";

        try {
            $resp = Http::timeout(30)->acceptJson()->post($url, [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.8,
                    'maxOutputTokens' => 900,
                ],
            ]);

            if (!$resp->ok()) {
                Log::error('Gemini API error', [
                    'status' => $resp->status(),
                    'body'   => $resp->body(),
                    'model'  => $model,
                ]);

                return "AI lỗi (HTTP {$resp->status()}).";
            }

            $data = $resp->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            return (is_string($text) && trim($text) !== '')
                ? trim($text)
                : 'Mình chưa trả lời được 😅 Bạn hỏi lại nha.';
        } catch (\Throwable $e) {
            Log::error('Gemini exception', ['message' => $e->getMessage()]);
            return 'AI đang bận 🥲 Bạn thử lại sau nha.';
        }
    }
}
