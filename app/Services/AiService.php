<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    public function chat(array $messages, string|array $model = null, float $temperature = 0.3, int $maxTokens = 2048): string
    {
        $models = $model !== null ? (array) $model : [config('services.openrouter.model')];
        $fallbacks = config('services.openrouter.fallback_models', []);
        $models = array_values(array_unique(array_merge($models, $fallbacks)));

        $this->checkRateLimit();

        $lastException = null;
        foreach ($models as $attemptModel) {
            try {
                return $this->attemptChat($messages, $attemptModel, $temperature, $maxTokens);
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning("AiService: model fallback", [
                    'model' => $attemptModel,
                    'error' => $e->getMessage(),
                ]);
                usleep(500_000);
            }
        }

        Log::error('AiService: all models failed', ['error' => $lastException?->getMessage()]);
        return 'The AI service is temporarily unavailable. Please try again later.';
    }

    public function generateJson(array $messages, string|array $model = null, float $temperature = 0.3, int $maxTokens = 2048): array
    {
        $content = $this->chat($messages, $model, $temperature, $maxTokens);

        if (empty($content) || str_contains($content, 'temporarily unavailable')) {
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

    private function attemptChat(array $messages, string $model, float $temperature, int $maxTokens): string
    {
        $apiKey = config('services.openrouter.api_key');
        if (!$apiKey) {
            throw new \RuntimeException('OpenRouter API key not configured.');
        }

        $maxRetries = config('services.openrouter.max_retries', 2);
        $timeout = config('services.openrouter.timeout', 60);
        $retryDelay = 1;

        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout($timeout)->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => $temperature,
                    'max_tokens' => $maxTokens,
                ]);

                if ($response->successful()) {
                    return $response->json()['choices'][0]['message']['content'] ?? '';
                }

                $status = $response->status();
                $body = $response->body();

                if (in_array($status, [429, 502, 503, 504]) && $attempt < $maxRetries) {
                    Log::info("AiService: retrying model $model (attempt $attempt, status $status)");
                    sleep($retryDelay * pow(2, $attempt));
                    continue;
                }

                throw new \RuntimeException("AI service error ($status): $body");
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                if ($attempt < $maxRetries) {
                    Log::info("AiService: connection timeout retry $model (attempt $attempt)");
                    sleep($retryDelay * pow(2, $attempt));
                    continue;
                }
                throw new \RuntimeException('AI service connection timeout: ' . $e->getMessage());
            }
        }

        throw new \RuntimeException('AI service unavailable after retries');
    }

    private function checkRateLimit(): void
    {
        $limit = config('services.openrouter.rate_limit_per_minute', 60);
        $key = 'openrouter_rate_' . md5(request()->ip() ?? 'cli');
        $current = (int) Cache::get($key, 0);

        if ($current >= $limit) {
            throw new \RuntimeException('Rate limit exceeded. Please wait before sending another request.');
        }

        Cache::put($key, $current + 1, 60);
    }
}
