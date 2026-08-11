<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\AiService;
use App\Services\ChatbotService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
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

    private function summarizeHistory(array $turns): string
    {
        $text = '';
        foreach ($turns as $turn) {
            $role = $turn['role'] ?? 'unknown';
            $content = $turn['content'] ?? '';
            $text .= "$role: ".mb_substr($content, 0, 200)."\n";
        }

        $messages = [
            ['role' => 'system', 'content' => 'Summarize this study conversation in 2-3 sentences. Focus on topics discussed and what the student was studying. Be concise.'],
            ['role' => 'user', 'content' => "Conversation:\n$text\n\nSummary:"],
        ];

        return $this->ai->chat($messages, null, 0.3, 500);
    }

    /* ---------- Conversation helpers ---------- */

    private function activeKey(): string
    {
        return 'active_chat_conversation_'.auth()->id();
    }

    private function getActiveConversation(): ?ChatConversation
    {
        $id = session()->get($this->activeKey());

        if ($id) {
            $conv = ChatConversation::forUser(auth()->id())->find($id);
            if ($conv) {
                return $conv;
            }
        }

        return null;
    }

    private function setActiveConversation(ChatConversation $conv): void
    {
        session()->put($this->activeKey(), $conv->id);
    }

    private function clearActiveConversation(): void
    {
        session()->forget($this->activeKey());
    }

    private function makeTitle(string $message): string
    {
        $title = preg_replace('/\s+/', ' ', trim($message));

        return mb_strlen($title) > 45 ? mb_substr($title, 0, 45).'…' : $title;
    }

    private function conversationPayload(ChatConversation $conv): array
    {
        return [
            'id' => $conv->id,
            'title' => $conv->title,
            'message_count' => $conv->messages()->count(),
            'updated_at' => $conv->updated_at?->diffForHumans() ?? 'just now',
        ];
    }

    private function buildHistory(ChatConversation $conv): array
    {
        $turns = $conv->messages()
            ->orderBy('id')
            ->get()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->all();

        if (count($turns) <= 20) {
            return $turns;
        }

        $older = array_slice($turns, 0, -14);
        $recent = array_slice($turns, -14);

        try {
            $summary = $this->summarizeHistory($older);

            return array_merge(
                [['role' => 'user', 'content' => "[Earlier in this conversation:\n$summary]"]],
                $recent
            );
        } catch (\Exception $e) {
            Log::warning('ChatController: history summarization failed', ['error' => $e->getMessage()]);

            return $recent;
        }
    }

    /* ---------- Chat endpoints ---------- */

    public function sendMessage(Request $request, ChatbotService $chatbot)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'persona' => 'nullable|in:default,strict,friendly,socratic,simplifier',
        ]);

        $userId = auth()->id();
        $personaKey = 'chat_persona_'.$userId;

        $persona = $request->persona ?? session()->get($personaKey, 'default');
        session()->put($personaKey, $persona);

        $conv = $this->getActiveConversation();
        if (! $conv) {
            $conv = ChatConversation::create([
                'user_id' => $userId,
                'title' => $this->makeTitle($request->message),
            ]);
            $this->setActiveConversation($conv);
        } elseif ($conv->messages()->count() === 0) {
            $conv->update(['title' => $this->makeTitle($request->message)]);
        }

        $userMessage = ChatMessage::create([
            'conversation_id' => $conv->id,
            'role' => 'user',
            'content' => $request->message,
        ]);

        $history = $this->buildHistory($conv);

        try {
            $answer = $chatbot->chat($request->message, $history, $persona);
        } catch (\Exception $e) {
            Log::error('ChatController: sendMessage failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
            $userMessage->delete();

            return response()->json([
                'response' => "AI service is temporarily unavailable. Please try again.\n\n> **Quick fix:** Add a free API key in [AI Settings](/ai-settings)",
                'error' => true,
            ], 200);
        }

        ChatMessage::create([
            'conversation_id' => $conv->id,
            'role' => 'assistant',
            'content' => $answer,
        ]);

        $conv->touch();

        return response()->json([
            'response' => $answer,
            'conversation' => $this->conversationPayload($conv),
        ]);
    }

    public function loadHistory()
    {
        $conv = $this->getActiveConversation();
        $messages = [];

        if ($conv) {
            foreach ($conv->messages()->orderBy('id')->get() as $m) {
                $messages[] = [
                    'role' => $m->role,
                    'content' => $m->content,
                    'timestamp' => $m->created_at?->toISOString(),
                ];
            }
        }

        return response()->json([
            'messages' => $messages,
            'conversation' => $conv ? $this->conversationPayload($conv) : null,
        ]);
    }

    public function conversations()
    {
        $conversations = ChatConversation::forUser(auth()->id())
            ->latest('updated_at')
            ->get();

        $ids = $conversations->pluck('id');
        $lastMessages = ChatMessage::whereIn('conversation_id', $ids)
            ->select('conversation_id', 'content')
            ->orderBy('id', 'desc')
            ->get()
            ->unique('conversation_id')
            ->keyBy('conversation_id');

        $payload = $conversations->map(function ($conv) use ($lastMessages) {
            return [
                'id' => $conv->id,
                'title' => $conv->title,
                'message_count' => $conv->messages()->count(),
                'updated_at' => $conv->updated_at?->diffForHumans() ?? 'just now',
                'updated_iso' => $conv->updated_at?->toIso8601String() ?? null,
                'last_message' => $lastMessages->get($conv->id)?->content
                    ? mb_strimwidth($lastMessages->get($conv->id)->content, 0, 60, '…')
                    : '',
            ];
        });

        return response()->json(['conversations' => $payload]);
    }

    public function showConversation($id)
    {
        $conv = ChatConversation::forUser(auth()->id())->findOrFail($id);
        $this->setActiveConversation($conv);

        $messages = [];
        foreach ($conv->messages()->orderBy('id')->get() as $m) {
            $messages[] = [
                'role' => $m->role,
                'content' => $m->content,
                'timestamp' => $m->created_at?->toISOString(),
            ];
        }

        return response()->json([
            'conversation' => $this->conversationPayload($conv),
            'messages' => $messages,
        ]);
    }

    public function renameConversation(Request $request, $id)
    {
        $request->validate(['title' => 'required|string|max:255']);

        $conv = ChatConversation::forUser(auth()->id())->findOrFail($id);
        $conv->update(['title' => trim($request->title)]);

        return response()->json(['conversation' => $this->conversationPayload($conv)]);
    }

    public function destroyConversation($id)
    {
        $conv = ChatConversation::forUser(auth()->id())->findOrFail($id);

        if ($this->getActiveConversation()?->id === $conv->id) {
            $this->clearActiveConversation();
        }

        $conv->delete();

        return response()->json(['success' => true]);
    }

    public function regenerate(Request $request, ChatbotService $chatbot)
    {
        $userId = auth()->id();
        $personaKey = 'chat_persona_'.$userId;
        $conv = $this->getActiveConversation();

        if (! $conv) {
            return response()->json(['error' => 'No message to regenerate'], 400);
        }

        $messages = $conv->messages()->orderBy('id')->get();
        if ($messages->isEmpty()) {
            return response()->json(['error' => 'No message to regenerate'], 400);
        }

        $lastUserIndex = null;
        for ($i = $messages->count() - 1; $i >= 0; $i--) {
            if ($messages[$i]->role === 'user') {
                $lastUserIndex = $i;
                break;
            }
        }

        if ($lastUserIndex === null) {
            return response()->json(['error' => 'No user message found'], 400);
        }

        $lastUserMsg = $messages[$lastUserIndex]->content;

        if ($messages->count() > $lastUserIndex + 1) {
            $messages[$lastUserIndex + 1]->delete();
        }
        $messages[$lastUserIndex]->delete();

        $history = $this->buildHistory($conv);
        $persona = session()->get($personaKey, 'default');

        try {
            $answer = $chatbot->chat($lastUserMsg, $history, $persona);
            ChatMessage::create([
                'conversation_id' => $conv->id,
                'role' => 'assistant',
                'content' => $answer,
            ]);
            $conv->touch();

            return response()->json([
                'response' => $answer,
                'conversation' => $this->conversationPayload($conv),
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
        $this->clearActiveConversation();

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
        if (! auth()->user()->hasRole('Admin')) {
            abort(403, 'Only administrators can access AI settings.');
        }
        $status = $this->ai->getProviderStatus();

        return view('Backend.Ai.settings', ['providers' => $status]);
    }

    public function updateSettings(Request $request)
    {
        if (! auth()->user()->hasRole('Admin')) {
            abort(403, 'Only administrators can access AI settings.');
        }
        if ($request->input('test_only')) {
            return $this->testProviders();
        }

        $request->validate([
            'gemini_api_key' => 'nullable|string|max:500',
            'groq_api_key' => 'nullable|string|max:500',
        ]);

        $envPath = base_path('.env');
        if (! is_writable($envPath)) {
            return response()->json(['error' => 'The .env file is not writable.'], 500);
        }
        $envContent = file_get_contents($envPath);

        if ($request->has('gemini_api_key')) {
            $envContent = $this->updateEnvValue($envContent, 'GEMINI_API_KEY', (string) $request->input('gemini_api_key', ''));
        }

        if ($request->has('groq_api_key')) {
            $envContent = $this->updateEnvValue($envContent, 'GROQ_API_KEY', (string) $request->input('groq_api_key', ''));
        }

        file_put_contents($envPath, $envContent);

        Artisan::call('config:clear');

        $this->ai = app(AiService::class);

        $this->notificationService->notifyAiSettingsUpdated(auth()->id());

        $status = $this->ai->getProviderStatus();

        return response()->json([
            'success' => true,
            'providers' => $status,
            'message' => 'Settings updated. Provider status: Gemini '.($status['gemini'] ? 'ON' : 'OFF').', Groq '.($status['groq'] ? 'ON' : 'OFF').', OpenRouter '.($status['openrouter'] ? 'ON' : 'OFF'),
        ]);
    }

    private function testProviders(): JsonResponse
    {
        $results = [];

        $geminiKey = config('services.gemini.api_key');
        if (! empty($geminiKey)) {
            try {
                $response = Http::timeout(15)
                    ->withHeaders(['x-goog-api-key' => $geminiKey])
                    ->post(
                        'https://generativelanguage.googleapis.com/v1beta/models/'.config('services.gemini.model', 'gemini-3.1-flash-lite').':generateContent',
                        [
                            'contents' => [['role' => 'user', 'parts' => [['text' => 'Say OK']]]],
                            'generationConfig' => ['maxOutputTokens' => 5],
                        ]
                    );
                if ($response->successful()) {
                    $text = $response->json('candidates.0.content.parts.0.text', '');
                    $results['Google Gemini'] = ['status' => 'ok', 'message' => 'Working! Model responded: '.mb_substr(trim($text), 0, 30)];
                } else {
                    $results['Google Gemini'] = ['status' => 'error', 'message' => 'HTTP '.$response->status().': '.$response->json('error.message', 'Unknown error')];
                }
            } catch (\Exception $e) {
                $results['Google Gemini'] = ['status' => 'error', 'message' => $e->getMessage()];
            }
        } else {
            $results['Google Gemini'] = ['status' => 'off', 'message' => 'No API key configured'];
        }

        $groqKey = config('services.groq.api_key');
        if (! empty($groqKey)) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$groqKey,
                ])->timeout(15)->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.groq.model', 'llama-3.3-70b-versatile'),
                    'messages' => [['role' => 'user', 'content' => 'Say OK']],
                    'max_tokens' => 5,
                ]);
                if ($response->successful()) {
                    $text = data_get($response->json(), 'choices.0.message.content', '');
                    $results['Groq'] = ['status' => 'ok', 'message' => 'Working! Model responded: '.mb_substr(trim($text), 0, 30)];
                } else {
                    $results['Groq'] = ['status' => 'error', 'message' => 'HTTP '.$response->status().': '.$response->json('error.message', 'Unknown error')];
                }
            } catch (\Exception $e) {
                $results['Groq'] = ['status' => 'error', 'message' => $e->getMessage()];
            }
        } else {
            $results['Groq'] = ['status' => 'off', 'message' => 'No API key configured'];
        }

        $orKey = config('services.openrouter.api_key');
        if (! empty($orKey)) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$orKey,
                ])->timeout(12)->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => config('services.openrouter.model'),
                    'messages' => [['role' => 'user', 'content' => 'Say OK']],
                    'max_tokens' => 5,
                ]);
                if ($response->successful()) {
                    $results['OpenRouter'] = ['status' => 'ok', 'message' => 'Working! Model responded.'];
                } else {
                    $results['OpenRouter'] = ['status' => 'error', 'message' => 'HTTP '.$response->status().' — '.($response->json('error.message', '') ?: 'Rate limited or unavailable')];
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
        $value = str_replace(["\r", "\n", '#', '='], '', trim($value));
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
        $newLine = "{$key}={$value}";

        if (preg_match($pattern, $envContent)) {
            return preg_replace($pattern, $newLine, $envContent);
        }

        return rtrim($envContent)."\n{$newLine}\n";
    }

    public function explainAnswer(Request $request, ChatbotService $chatbot)
    {
        $request->validate([
            'question' => 'required|string|max:2000',
            'user_answer' => 'required|string|max:5000',
            'correct_answer' => 'nullable|string|max:5000',
            'question_type' => 'nullable|string|max:50',
        ]);

        $question = $request->question;
        $userAnswer = $request->user_answer;
        $correctAnswer = $request->correct_answer;

        $message = "I answered this question: \"$question\"\n";
        $message .= "My answer was: \"$userAnswer\"\n";
        if ($correctAnswer) {
            $message .= "The correct answer is: \"$correctAnswer\"\n";
        }
        $message .= "\nPlease explain why my answer is ".($correctAnswer ? 'wrong and explain the correct answer' : 'correct or incorrect').'. Be detailed and educational.';

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
