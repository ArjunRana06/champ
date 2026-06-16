<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    public function chat(array $messages, string $model = null, float $temperature = 0.3, int $maxTokens = 2000): string
    {
        $apiKey = env('OPENROUTER_API_KEY');
        if (!$apiKey) {
            throw new \Exception('OpenRouter API key not configured.');
        }

        $model = $model ?? env('OPENROUTER_MODEL', 'openai/gpt-3.5-turbo');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);

        if (!$response->successful()) {
            Log::error('OpenRouter API error', ['body' => $response->body()]);
            throw new \Exception('AI service error: ' . $response->body());
        }

        return $response->json()['choices'][0]['message']['content'] ?? '';
    }

    public function generateJson(array $messages, string $model = null, float $temperature = 0.3, int $maxTokens = 2000): array
    {
        $content = $this->chat($messages, $model, $temperature, $maxTokens);

        $content = preg_replace('/```json\s*|\s*```/', '', $content);
        $json = json_decode($content, true);

        if (!is_array($json)) {
            throw new \Exception('Invalid JSON response from AI: ' . $content);
        }

        return $json;
    }
}
