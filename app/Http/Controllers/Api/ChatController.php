<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use App\Services\AiService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected AiService $ai;
    protected NotificationService $notificationService;

    public function __construct(AiService $ai, NotificationService $notificationService)
    {
        $this->ai = $ai;
        $this->notificationService = $notificationService;
    }

    private function manageHistory(array $history, string $userMessage, string $assistantResponse, string $historyKey): array
    {
        $history[] = ['role' => 'user', 'content' => $userMessage];
        $history[] = ['role' => 'assistant', 'content' => $assistantResponse];

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

        try {
            $answer = $chatbot->chat($request->message, $history, $persona);
            $history = $this->manageHistory($history, $request->message, $answer, $historyKey);
            return response()->json([
                'response' => $answer,
                'history_count' => (int) ceil(count($history) / 2),
            ]);
        } catch (\Exception $e) {
            Log::error('ChatController: sendMessage failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
            return response()->json([
                'response' => "AI service is temporarily unavailable. Please try again.\n\n> **Quick fix:** Add a free API key in [AI Settings](/ai-settings)",
                'error' => true,
            ], 200);
        }
    }

    public function loadHistory()
    {
        $userId = auth()->id();
        $history = session()->get('chat_history_' . $userId, []);

        $messages = [];
        foreach ($history as $turn) {
            if (($turn['role'] ?? '') === 'system') continue;
            $messages[] = [
                'role' => $turn['role'] ?? 'user',
                'content' => $turn['content'] ?? '',
                'timestamp' => null,
            ];
        }

        return response()->json([
            'messages' => $messages,
            'history_count' => (int) ceil(count($messages) / 2),
        ]);
    }

    public function regenerate(Request $request, ChatbotService $chatbot)
    {
        $userId = auth()->id();
        $historyKey = 'chat_history_' . $userId;
        $personaKey = 'chat_persona_' . $userId;
        $history = session()->get($historyKey, []);

        if (empty($history)) {
            return response()->json(['error' => 'No message to regenerate'], 400);
        }

        $lastUserMsg = null;
        for ($i = count($history) - 1; $i >= 0; $i--) {
            if (($history[$i]['role'] ?? '') === 'user') {
                $lastUserMsg = $history[$i]['content'];
                break;
            }
        }

        if (!$lastUserMsg) {
            return response()->json(['error' => 'No user message found'], 400);
        }

        $historyWithoutLast = [];
        $foundUser = false;
        for ($i = count($history) - 1; $i >= 0; $i--) {
            $turn = $history[$i];
            if (($turn['role'] ?? '') === 'user' && !$foundUser) {
                $foundUser = true;
                continue;
            }
            if (($turn['role'] ?? '') === 'assistant' && $foundUser) {
                array_unshift($historyWithoutLast, $turn);
                break;
            }
            array_unshift($historyWithoutLast, $turn);
        }

        $persona = session()->get($personaKey, 'default');

        try {
            $answer = $chatbot->chat($lastUserMsg, $historyWithoutLast, $persona);
            $history = $this->manageHistory($historyWithoutLast, $lastUserMsg, $answer, $historyKey);
            return response()->json([
                'response' => $answer,
                'history_count' => (int) ceil(count($history) / 2),
            ]);
        } catch (\Exception $e) {
            Log::error('ChatController: regenerate failed', ['error' => $e->getMessage()]);
            return response()->json([
                'response' => "AI service is temporarily unavailable. Please try again.\n\n> **Quick fix:** Add a free API key in [AI Settings](/ai-settings)",
                'error' => true,
            ], 200);
        }
    }

    public function clearChat()
    {
        $userId = auth()->id();
        session()->forget('chat_history_' . $userId);
        return response()->json(['status' => 'ok']);
    }

    public function showChatPage()
    {
        $providers = $this->ai->getProviderStatus();
        $hasWorkingProvider = $providers['gemini'] || $providers['groq'] || ($providers['openrouter']);
        return view('Backend.Ai.chat', [
            'providers' => $providers,
            'hasWorkingProvider' => $hasWorkingProvider,
        ]);
    }

    public function showSettings()
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Only administrators can access AI settings.');
        }
        $status = $this->ai->getProviderStatus();
        return view('Backend.Ai.settings', ['providers' => $status]);
    }

    public function updateSettings(Request $request)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Only administrators can access AI settings.');
        }
        if ($request->input('test_only')) {
            return $this->testProviders();
        }

        $request->validate([
            'gemini_api_key' => 'nullable|string',
            'groq_api_key' => 'nullable|string',
        ]);

        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        if ($request->has('gemini_api_key')) {
            $envContent = $this->updateEnvValue($envContent, 'GEMINI_API_KEY', $request->input('gemini_api_key', ''));
        }

        if ($request->has('groq_api_key')) {
            $envContent = $this->updateEnvValue($envContent, 'GROQ_API_KEY', $request->input('groq_api_key', ''));
        }

        file_put_contents($envPath, $envContent);

        $this->ai = app(AiService::class);

        $this->notificationService->notifyAiSettingsUpdated(auth()->id());

        $status = $this->ai->getProviderStatus();

        return response()->json([
            'success' => true,
            'providers' => $status,
            'message' => 'Settings updated. Provider status: Gemini ' . ($status['gemini'] ? 'ON' : 'OFF') . ', Groq ' . ($status['groq'] ? 'ON' : 'OFF') . ', OpenRouter ' . ($status['openrouter'] ? 'ON' : 'OFF'),
        ]);
    }

    private function testProviders(): \Illuminate\Http\JsonResponse
    {
        $results = [];

        $geminiKey = config('services.gemini.api_key');
        if (!empty($geminiKey)) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(15)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/" . config('services.gemini.model', 'gemini-3.1-flash-lite') . ":generateContent?key={$geminiKey}",
                    [
                        'contents' => [['role' => 'user', 'parts' => [['text' => 'Say OK']]]],
                        'generationConfig' => ['maxOutputTokens' => 5],
                    ]
                );
                if ($response->successful()) {
                    $text = $response->json('candidates.0.content.parts.0.text', '');
                    $results['Google Gemini'] = ['status' => 'ok', 'message' => 'Working! Model responded: ' . mb_substr(trim($text), 0, 30)];
                } else {
                    $results['Google Gemini'] = ['status' => 'error', 'message' => 'HTTP ' . $response->status() . ': ' . $response->json('error.message', 'Unknown error')];
                }
            } catch (\Exception $e) {
                $results['Google Gemini'] = ['status' => 'error', 'message' => $e->getMessage()];
            }
        } else {
            $results['Google Gemini'] = ['status' => 'off', 'message' => 'No API key configured'];
        }

        $groqKey = config('services.groq.api_key');
        if (!empty($groqKey)) {
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . $groqKey,
                ])->timeout(15)->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.groq.model', 'llama-3.3-70b-versatile'),
                    'messages' => [['role' => 'user', 'content' => 'Say OK']],
                    'max_tokens' => 5,
                ]);
                if ($response->successful()) {
                    $text = data_get($response->json(), 'choices.0.message.content', '');
                    $results['Groq'] = ['status' => 'ok', 'message' => 'Working! Model responded: ' . mb_substr(trim($text), 0, 30)];
                } else {
                    $results['Groq'] = ['status' => 'error', 'message' => 'HTTP ' . $response->status() . ': ' . $response->json('error.message', 'Unknown error')];
                }
            } catch (\Exception $e) {
                $results['Groq'] = ['status' => 'error', 'message' => $e->getMessage()];
            }
        } else {
            $results['Groq'] = ['status' => 'off', 'message' => 'No API key configured'];
        }

        $orKey = config('services.openrouter.api_key');
        if (!empty($orKey)) {
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . $orKey,
                ])->timeout(12)->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => config('services.openrouter.model'),
                    'messages' => [['role' => 'user', 'content' => 'Say OK']],
                    'max_tokens' => 5,
                ]);
                if ($response->successful()) {
                    $results['OpenRouter'] = ['status' => 'ok', 'message' => 'Working! Model responded.'];
                } else {
                    $results['OpenRouter'] = ['status' => 'error', 'message' => 'HTTP ' . $response->status() . ' — ' . ($response->json('error.message', '') ?: 'Rate limited or unavailable')];
                }
            } catch (\Exception $e) {
                $results['OpenRouter'] = ['status' => 'error', 'message' => $e->getMessage()];
            }
        } else {
            $results['OpenRouter'] = ['status' => 'off', 'message' => 'No API key configured'];
        }

        return response()->json(['test_results' => $results]);
    }

    private function updateEnvValue(string $envContent, string $key, string $value): string
    {
        $pattern = "/^{$key}=.*/m";
        $newLine = "{$key}={$value}";

        if (preg_match($pattern, $envContent)) {
            return preg_replace($pattern, $newLine, $envContent);
        }

        return rtrim($envContent) . "\n{$newLine}\n";
    }

    public function explainAnswer(Request $request, ChatbotService $chatbot)
    {
        $request->validate([
            'question' => 'required|string|max:2000',
            'user_answer' => 'required|string|max:5000',
            'correct_answer' => 'nullable|string|max:5000',
            'question_type' => 'nullable|string|max:50',
        ]);

        $question = e($request->question);
        $userAnswer = e($request->user_answer);
        $correctAnswer = $request->correct_answer ? e($request->correct_answer) : null;

        $message = "I answered this question: \"$question\"\n";
        $message .= "My answer was: \"$userAnswer\"\n";
        if ($correctAnswer) {
            $message .= "The correct answer is: \"$correctAnswer\"\n";
        }
        $message .= "\nPlease explain why my answer is " . ($correctAnswer ? 'wrong and explain the correct answer' : 'correct or incorrect') . ". Be detailed and educational.";

        try {
            $explanation = $chatbot->chat($message, [], 'friendly', false);
            return response()->json(['explanation' => $explanation]);
        } catch (\Exception $e) {
            Log::error('ChatController: explainAnswer failed', ['error' => $e->getMessage()]);
            return response()->json([
                'explanation' => 'AI service temporarily unavailable. Please try again.',
            ], 200);
        }
    }
}
