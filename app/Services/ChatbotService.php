<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    private function getUserProfileData(): string
    {
        $user = Auth::user();
        if (!$user) return "User not logged in.";
        return "User's identity:\n- Name: " . ($user->name ?? 'Not provided') . "\n- Email: " . ($user->email ?? 'Not provided') . "\n";
    }

    private function getTimelineData(): string
    {
        $user = Auth::user();
        $activities = Activity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get();

        if ($activities->isEmpty()) {
            return "No timeline data yet.";
        }

        $context = "User's timeline memories (most recent first):\n";
        foreach ($activities as $activity) {
            $text = '';

            if (in_array($activity->type, ['text', 'emoji', 'emotion'])) {
                $text = $activity->parsed_content ?? '';
            } elseif (in_array($activity->type, ['image', 'video', 'voice'])) {
                $mediaType = $activity->type;
                $fileName = $activity->file_path ? basename($activity->file_path) : 'file';
                $text = "Uploaded a $mediaType ($fileName)";
                $extra = $activity->parsed_content ?? '';
                if ($extra && !str_contains($extra, 'Uploaded')) {
                    $text .= " – " . $extra;
                }
            }

            if (!empty($text)) {
                $context .= "- " . $activity->created_at->format('Y-m-d H:i') . " : " . $text . "\n";
            }
        }
        return $context;
    }

    public function chat(string $userMessage, array $history = []): string
    {
        $userProfile = $this->getUserProfileData();
        $timeline = $this->getTimelineData();

        $systemPrompt = "You are a warm, helpful AI assistant. You can chat about anything.
You know the user's identity from the profile below.
You also have access to their personal timeline, including uploaded photos, videos, and voice notes.
If the user asks about media, check the timeline. If no media, say 'I don't see any in your timeline yet.'
Keep responses warm, natural, and concise.

User profile:
$userProfile

User timeline:
$timeline";

        $apiKey = env('OPENROUTER_API_KEY');
        if (!$apiKey) {
            return "AI service is not configured.";
        }

        $model = env('OPENROUTER_MODEL', 'google/gemini-2.0-flash-001');

        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($history as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => $turn['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'HTTP-Referer' => env('APP_URL'),
                'X-Title' => 'Life Replay System',
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 500,
                'temperature' => 0.8,
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'] ?? "No response.";
            } else {
                Log::error('OpenRouter error', ['status' => $response->status(), 'body' => $response->body()]);
                return "AI service temporarily unavailable.";
            }
        } catch (\Exception $e) {
            Log::error('Chat exception: ' . $e->getMessage());
            return "Sorry, I encountered an error.";
        }
    }
}
