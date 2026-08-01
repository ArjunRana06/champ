<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    private const GEMINI_TIMEOUT = 30;
    private const GROQ_TIMEOUT = 15;
    private const OPENROUTER_TIMEOUT = 12;
    private const OPENROUTER_MAX_MODELS = 3;
    private const CACHE_TTL = 3600;
    private const RATE_LIMIT_WINDOW = 60;
    private const RATE_LIMIT_MAX = 20;

    public function chat(array $messages, string|array $model = null, float $temperature = 0.3, int $maxTokens = 2048): string
    {
        $cacheKey = $this->getCacheKey($messages);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $this->checkRateLimit();

        $lastException = null;

        if ($this->isGeminiConfigured()) {
            try {
                $result = $this->chatGemini($messages, $temperature, $maxTokens);
                Cache::put($cacheKey, $result, self::CACHE_TTL);
                Log::info('AiService: Gemini responded');
                return $result;
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning('AiService: Gemini failed', ['error' => $e->getMessage()]);
            }
        }

        if ($this->isGroqConfigured()) {
            try {
                $result = $this->chatGroq($messages, $temperature, $maxTokens);
                Cache::put($cacheKey, $result, self::CACHE_TTL);
                Log::info('AiService: Groq responded');
                return $result;
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning('AiService: Groq failed', ['error' => $e->getMessage()]);
            }
        }

        if ($this->isOpenRouterConfigured()) {
            try {
                $result = $this->chatOpenRouter($messages, $model, $temperature, $maxTokens);
                Cache::put($cacheKey, $result, self::CACHE_TTL);
                Log::info('AiService: OpenRouter responded');
                return $result;
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning('AiService: OpenRouter failed', ['error' => $e->getMessage()]);
            }
        }

        Log::error('AiService: all providers failed', [
            'last_error' => $lastException?->getMessage(),
        ]);

        return $this->getFallbackResponse($messages);
    }

    public function generateJson(array $messages, string|array $model = null, float $temperature = 0.3, int $maxTokens = 2048): array
    {
        $content = $this->chat($messages, $model, $temperature, $maxTokens);

        if (empty($content) || str_contains($content, 'temporarily unavailable') || str_contains($content, 'trouble connecting')) {
            return [];
        }

        $content = preg_replace('/```json\s*|\s*```/', '', $content);
        $json = json_decode($content, true);

        if (!is_array($json)) {
            Log::warning('AiService: invalid JSON response', ['content' => substr($content, 0, 200)]);
            return [];
        }

        return $json;
    }

    public function getProviderStatus(): array
    {
        return [
            'gemini' => $this->isGeminiConfigured(),
            'groq' => $this->isGroqConfigured(),
            'openrouter' => $this->isOpenRouterConfigured(),
        ];
    }

    private function isGeminiConfigured(): bool
    {
        return !empty(config('services.gemini.api_key'));
    }

    private function isGroqConfigured(): bool
    {
        return !empty(config('services.groq.api_key'));
    }

    private function isOpenRouterConfigured(): bool
    {
        return !empty(config('services.openrouter.api_key'));
    }

    private function chatGemini(array $messages, float $temperature, int $maxTokens): string
    {
        $apiKey = config('services.gemini.api_key');
        $primaryModel = config('services.gemini.model', 'gemini-3.1-flash-lite');
        $fallbacks = config('services.gemini.fallback_models', []);
        $models = array_values(array_unique(array_merge([$primaryModel], $fallbacks)));

        $lastException = null;

        foreach ($models as $model) {
            try {
                return $this->attemptGeminiChat($messages, $model, $temperature, $maxTokens, $apiKey);
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning("AiService: Gemini model $model failed", ['error' => $e->getMessage()]);
            }
        }

        throw $lastException ?? new \RuntimeException('All Gemini models failed');
    }

    private function attemptGeminiChat(array $messages, string $model, float $temperature, int $maxTokens, string $apiKey): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $geminiMessages = $this->convertToGeminiFormat($messages);

        $payload = [
            'contents' => $geminiMessages,
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => max($maxTokens, 512),
            ],
        ];

        $response = Http::timeout(self::GEMINI_TIMEOUT)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        if ($response->successful()) {
            $body = $response->json();
            $parts = $body['candidates'][0]['content']['parts'] ?? [];
            foreach ($parts as $part) {
                $text = $part['text'] ?? '';
                if (!empty(trim($text))) {
                    return trim($text);
                }
            }
            throw new \RuntimeException("Gemini $model returned empty content");
        }

        $status = $response->status();
        $errorBody = $response->json('error.message', 'Unknown error');

        if ($status === 429) {
            throw new \RuntimeException("Gemini $model rate limited (429)");
        }

        if ($status === 404) {
            throw new \RuntimeException("Gemini $model not found (404)");
        }

        throw new \RuntimeException("Gemini $model HTTP $status: $errorBody");
    }

    private function convertToGeminiFormat(array $messages): array
    {
        $systemInstruction = null;
        $contents = [];

        foreach ($messages as $msg) {
            $role = $msg['role'] ?? 'user';
            $content = $msg['content'] ?? '';

            if ($role === 'system') {
                $systemInstruction = $content;
                continue;
            }

            $contents[] = [
                'role' => $role === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $content]],
            ];
        }

        if ($systemInstruction && !empty($contents)) {
            $contents[0]['parts'][0]['text'] = $systemInstruction . "\n\n---\n\n" . $contents[0]['parts'][0]['text'];
        }

        if (empty($contents)) {
            $contents[] = ['role' => 'user', 'parts' => [['text' => 'Hello']]];
        }

        return $contents;
    }

    private function chatGroq(array $messages, float $temperature, int $maxTokens): string
    {
        $apiKey = config('services.groq.api_key');
        $model = config('services.groq.model', 'llama-3.3-70b-versatile');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(self::GROQ_TIMEOUT)->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);

        if ($response->successful()) {
            $body = $response->json();
            $content = data_get($body, 'choices.0.message.content', '');
            if (!empty($content)) {
                return $content;
            }
            throw new \RuntimeException("Empty Groq response from $model");
        }

        $status = $response->status();
        $errorBody = $response->json('error.message', 'Unknown error');

        if ($status === 429) {
            $fallbacks = config('services.groq.fallback_models', []);
            foreach ($fallbacks as $fbModel) {
                try {
                    return $this->attemptGroqChat($messages, $fbModel, $temperature, $maxTokens);
                } catch (\Exception $e) {
                    Log::warning("AiService: Groq fallback $fbModel failed", ['error' => $e->getMessage()]);
                }
            }
            throw new \RuntimeException("Groq rate limited, all fallbacks exhausted");
        }

        throw new \RuntimeException("Groq HTTP $status: $errorBody");
    }

    private function attemptGroqChat(array $messages, string $model, float $temperature, int $maxTokens): string
    {
        $apiKey = config('services.groq.api_key');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(self::GROQ_TIMEOUT)->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);

        if ($response->successful()) {
            $content = data_get($response->json(), 'choices.0.message.content', '');
            if (!empty($content)) {
                return $content;
            }
        }

        throw new \RuntimeException("Groq fallback $model failed: HTTP " . $response->status());
    }

    private function chatOpenRouter(array $messages, string|array|null $model, float $temperature, int $maxTokens): string
    {
        $apiKey = config('services.openrouter.api_key');
        if (!$apiKey) {
            throw new \RuntimeException('OpenRouter API key not configured');
        }

        $primaryModel = $model !== null ? (array)$model : [config('services.openrouter.model')];
        $fallbacks = config('services.openrouter.fallback_models', []);
        $models = array_values(array_unique(array_merge($primaryModel, $fallbacks)));
        $models = array_slice($models, 0, self::OPENROUTER_MAX_MODELS);

        $startTime = microtime(true);
        $lastException = null;
        $attempted = 0;

        foreach ($models as $attemptModel) {
            $elapsed = microtime(true) - $startTime;
            if ($elapsed > 30 || $attempted >= self::OPENROUTER_MAX_MODELS) {
                break;
            }
            $attempted++;

            try {
                return $this->attemptOpenRouterChat($messages, $attemptModel, $temperature, $maxTokens);
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning("AiService: OpenRouter model failed", [
                    'model' => $attemptModel,
                    'attempt' => $attempted,
                    'elapsed' => round($elapsed, 1),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        throw $lastException ?? new \RuntimeException('All OpenRouter models failed');
    }

    private function attemptOpenRouterChat(array $messages, string $model, float $temperature, int $maxTokens): string
    {
        $apiKey = config('services.openrouter.api_key');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(self::OPENROUTER_TIMEOUT)->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);

        if ($response->successful()) {
            $body = $response->json();
            $content = data_get($body, 'choices.0.message.content', '');
            if (!empty($content)) {
                return $content;
            }
            $reasoning = data_get($body, 'choices.0.message.reasoning', '');
            if (!empty($reasoning)) {
                return $reasoning;
            }
            throw new \RuntimeException("Empty response from $model");
        }

        $status = $response->status();

        if ($status === 429) {
            throw new \RuntimeException("Rate limited (429): $model");
        }

        throw new \RuntimeException("HTTP $status from $model");
    }

    private function getCacheKey(array $messages): string
    {
        $lastUserMsg = '';
        foreach (array_reverse($messages) as $msg) {
            if (($msg['role'] ?? '') === 'user') {
                $lastUserMsg = $msg['content'] ?? '';
                break;
            }
        }
        $normalized = mb_strtolower(trim($lastUserMsg));
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        return 'ai_chat_' . md5($normalized);
    }

    private function checkRateLimit(): void
    {
        $key = 'ai_rate_' . md5(request()->ip() ?? 'cli');
        $count = Cache::increment($key);
        if ($count === 1) {
            Cache::put($key, 1, self::RATE_LIMIT_WINDOW);
        }
        if ($count > self::RATE_LIMIT_MAX) {
            throw new \RuntimeException('Rate limit exceeded. Please wait before sending another request.');
        }
    }

    private function getFallbackResponse(array $messages): string
    {
        $lastUserMsg = '';
        foreach (array_reverse($messages) as $msg) {
            if (($msg['role'] ?? '') === 'user') {
                $lastUserMsg = $msg['content'] ?? '';
                break;
            }
        }

        $lower = mb_strtolower($lastUserMsg);

        if (preg_match('/^(hi|hello|hey|sup|yo|namaste)\b/i', $lower)) {
            return "Hello! I'm your AI Study Assistant. I'm experiencing some connectivity issues with the AI service right now, but I'm still here to help! Try asking your question again in a moment, or upload your study materials for personalized assistance.";
        }

        if (preg_match('/who (are|r) (you|u)/i', $lower)) {
            return "I'm your **AI Study Assistant**! I help students learn by explaining concepts, generating quizzes, and creating study plans. The AI service is temporarily having issues, but please try your question again shortly.";
        }

        $providers = [];
        if ($this->isGeminiConfigured()) {
            $providers[] = 'Google Gemini';
        }
        if ($this->isGroqConfigured()) {
            $providers[] = 'Groq';
        }
        if ($this->isOpenRouterConfigured()) {
            $providers[] = 'OpenRouter';
        }
        $providerList = !empty($providers) ? implode(' + ', $providers) : 'No AI providers configured';

        return "I'm having trouble connecting to the AI service right now.\n\n**Active providers:** $providerList\n\n**Please try again in a moment.** In the meantime, you can:\n- Upload study materials to process\n- Review your existing flashcards and quizzes\n- Check your study progress and stats\n\n---\n\n**To fix this permanently, add a free API key:**\n\n1. Go to [AI Settings](/ai-settings) (gear icon above)\n2. Get a free key from [Google AI Studio](https://aistudio.google.com/app/apikey) or [Groq](https://console.groq.com/keys)\n3. Paste it in and save\n\nNo credit card needed. 1,500+ free AI requests per day.";
    }
}
