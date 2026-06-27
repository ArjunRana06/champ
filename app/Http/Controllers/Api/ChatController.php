<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected AiService $ai;

    public function __construct(AiService $ai)
    {
        $this->ai = $ai;
    }

    private function manageHistory(array $history, string $userMessage, string $assistantResponse, string $historyKey): array
    {
        $history[] = ['role' => 'user', 'content' => $userMessage];
        $history[] = ['role' => 'assistant', 'content' => $assistantResponse];

        // Keep up to 50 turns; if exceeded, summarize old ones
        if (count($history) > 50) {
            $olderTurns = array_slice($history, 0, -20);
            $recentTurns = array_slice($history, -20);

            try {
                $summary = $this->summarizeHistory($olderTurns);
                $history = array_merge(
                    [['role' => 'system', 'content' => '[Previous conversation summary]: ' . $summary]],
                    $recentTurns
                );
            } catch (\Exception $e) {
                Log::warning('History summarization failed', ['error' => $e->getMessage()]);
                $history = $recentTurns;
            }
        }

        session()->put($historyKey, $history);
        return $history;
    }

    private function summarizeHistory(array $turns): string
    {
        $text = '';
        foreach ($turns as $turn) {
            $role = $turn['role'] ?? 'unknown';
            $content = $turn['content'] ?? '';
            $text .= "$role: " . mb_substr($content, 0, 200) . "\n";
        }

        $messages = [
            ['role' => 'system', 'content' => 'Summarize this study conversation in 2-3 sentences. Focus on topics discussed and what the student was studying. Be concise.'],
            ['role' => 'user', 'content' => "Conversation:\n$text\n\nSummary:"],
        ];

        return $this->ai->chat($messages, null, 0.3, 500);
    }

    public function sendMessage(Request $request, ChatbotService $chatbot)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'persona' => 'nullable|in:default,strict,friendly,socratic,simplifier',
        ]);

        $userId = auth()->id();
        $historyKey = 'chat_history_' . $userId;
        $personaKey = 'chat_persona_' . $userId;
        $history = session()->get($historyKey, []);

        $persona = $request->persona ?? session()->get($personaKey, 'default');
        session()->put($personaKey, $persona);

        $answer = $chatbot->chat($request->message, $history, $persona);

        $history = $this->manageHistory($history, $request->message, $answer, $historyKey);

        return response()->json(['response' => $answer]);
    }

    public function sendMessageStream(Request $request, ChatbotService $chatbot)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'persona' => 'nullable|in:default,strict,friendly,socratic,simplifier',
        ]);

        $userId = auth()->id();
        $historyKey = 'chat_history_' . $userId;
        $personaKey = 'chat_persona_' . $userId;
        $history = session()->get($historyKey, []);

        $persona = $request->persona ?? session()->get($personaKey, 'default');
        session()->put($personaKey, $persona);

        return response()->stream(function () use ($chatbot, $request, $history, $historyKey, $persona) {
            $fullResponse = $chatbot->chatStream($request->message, $history, $persona, function ($chunk) {
                echo $chunk;
                ob_flush();
                flush();
            });

            $this->manageHistory($history, $request->message, $fullResponse, $historyKey);
        }, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function clearChat()
    {
        $userId = auth()->id();
        session()->forget('chat_history_' . $userId);
        return response()->json(['status' => 'ok']);
    }

    public function showChatPage()
    {
        return view('Backend.Ai.chat');
    }

    public function explainAnswer(Request $request, ChatbotService $chatbot)
    {
        $request->validate([
            'question' => 'required|string|max:2000',
            'user_answer' => 'required|string|max:5000',
            'correct_answer' => 'nullable|string|max:5000',
            'question_type' => 'nullable|string|max:50',
        ]);

        $message = "I answered this question: \"{$request->question}\"\n";
        $message .= "My answer was: \"{$request->user_answer}\"\n";
        if ($request->correct_answer) {
            $message .= "The correct answer is: \"{$request->correct_answer}\"\n";
        }
        $message .= "\nPlease explain why my answer is " . ($request->correct_answer ? 'wrong and explain the correct answer' : 'correct or incorrect') . ". Be detailed and educational.";

        $explanation = $chatbot->chat($message, [], 'friendly', false);

        return response()->json(['explanation' => $explanation]);
    }
}
