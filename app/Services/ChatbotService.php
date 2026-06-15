<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    protected $ragService;

    public function __construct(RAGService $ragService)
    {
        $this->ragService = $ragService;
    }

    private function getUserProfileData(): string
    {
        $user = Auth::user();
        if (!$user) return "User not logged in.";
        return "User's identity:\n- Name: " . ($user->name ?? 'Not provided') . "\n- Email: " . ($user->email ?? 'Not provided') . "\n";
    }

    private function getMetadata(): string
    {
        $user = Auth::user();
        $subjectCount = $user->subjects()->count();
        $documentCount = $user->documents()->count();
        $completedDocCount = $user->documents()->where('status', 'completed')->count();

        return "You have $subjectCount subject(s) and $documentCount document(s) uploaded. " .
               "Of these, $completedDocCount document(s) have been fully processed and are searchable.";
    }

    private function getDocumentContext(string $userMessage): string
    {
        return $this->ragService->getContextString($userMessage);
    }

    private function isCasualConversation(string $message): bool
    {
        $patterns = [
            '/^hi\b/i', '/^hello\b/i', '/^hey\b/i',
            '/^good morning/i', '/^good afternoon/i', '/^good evening/i',
            '/^thanks?/i', '/^thank you/i', '/^how are you/i', '/^how r u/i', '/^how do you do/i',
            '/^kasto xa\??/i', '/^kasto cha\??/i', '/^ramro xa\??/i', '/^thik xa\??/i',
            '/^malai ni/i', '/^timi lai/i', '/^tapa[i]lai/i',
            '/^yeah\b/i', '/^ok\b/i', '/^okay\b/i', '/^fine/i',
            '/^i understand/i', '/^got it/i',
            '/^what\'s up/i', '/^whats up/i',
            '/^how\'s it going/i', '/^how is it going/i',
            '/^hora\??/i', '/^ho ra\??/i', '/^ho\??/i'
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($message))) {
                return true;
            }
        }
        if (strlen(trim($message)) <= 10) {
            return true;
        }
        return false;
    }

    private function isLanguageQuestion(string $message): bool
    {
        $languagePatterns = [
            '/understand\s+nepali/i', '/know\s+nepali/i', '/speak\s+nepali/i',
            '/understand\s+spanish/i', '/know\s+french/i', '/understand\s+hindi/i',
            '/do you (understand|know|speak) ([a-z]+)/i',
            '/can you (understand|speak) ([a-z]+)/i',
            '/nepali language/i', '/hindi language/i', '/other language/i',
            '/nepali\s+ma/i', '/nepali\s+maa/i', '/nepalima/i'
        ];
        foreach ($languagePatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }
        if (preg_match('/[\x{0900}-\x{097F}]/u', $message)) {
            return true;
        }
        return false;
    }

    private function answerLanguageQuestion(string $message): string
    {
        if (preg_match('/nepali|[\x{0900}-\x{097F}]/iu', $message)) {
            return "हो, म नेपाली बुझ्छु! 🇳🇵\n\nYes, I can understand Nepali. I can read, understand, and respond in Nepali if you prefer. Feel free to ask your study questions in Nepali – I'll do my best to help based on your uploaded notes.\n\n(Note: Your uploaded notes are mostly in English, so my answers will be based on those, but I can converse in Nepali.)";
        }
        if (preg_match('/hindi|[\x{0900}-\x{097F}]/iu', $message)) {
            return "हाँ, म हिंदी समझता हूँ! 🇮🇳\n\nYes, I can understand Hindi. You can ask questions in Hindi as well. I'll answer based on your uploaded study materials.\n\n(Note: Your notes appear to be in English, so my answers will reference them.)";
        }
        if (preg_match('/spanish|español/i', $message)) {
            return "¡Sí, entiendo español! 🇪🇸\n\nYes, I understand Spanish. Feel free to ask your study questions in Spanish – I'll do my best based on your uploaded notes.";
        }
        return "Yes, I can understand many languages! I can process and respond in English, Nepali, Hindi, Spanish, French, German, and more. Just ask your question in the language you're comfortable with, and I'll help based on your uploaded study materials. 📚";
    }

    private function casualResponse(string $message): string
    {
        $userName = Auth::user()->name ?? 'there';
        if (preg_match('/kasto xa|kasto cha|ramro xa|thik xa|timi lai|malai ni|tapa[i]lai/i', $message)) {
            return "😊 म ठीक छु, तपाईलाई कस्तो छ?\n\n(I'm fine, thank you. How about you?)\n\nके तपाईलाई अध्ययन सम्बन्धी कुनै प्रश्न सोध्नु छ? 📚";
        }
        return "😊 $userName, I'm here to help with your studies! Feel free to ask about any topic from your uploaded notes.\n\n📝 What subject or concept can I help you with today?";
    }

    public function chat(string $userMessage, array $history = []): string
    {
        $userProfile = $this->getUserProfileData();
        $metadata = $this->getMetadata();

        // 1. Handle language questions directly
        if ($this->isLanguageQuestion($userMessage)) {
            return $this->answerLanguageQuestion($userMessage);
        }

        // 2. Handle pure casual conversation (greetings, small talk)
        if ($this->isCasualConversation($userMessage)) {
            return $this->casualResponse($userMessage);
        }

        // 3. Try to find relevant content in uploaded notes
        $documentContext = $this->getDocumentContext($userMessage);
        $hasRelevantContent = !str_contains($documentContext, "No relevant content found") &&
                              !str_contains($documentContext, "You have not uploaded any processed documents");

        // 4. Choose the appropriate system prompt
        if ($hasRelevantContent) {
            // Answer using only the user's notes
            $systemPrompt = "You are a brilliant, enthusiastic professor. Your answers must be based **only** on the provided excerpts from the student's uploaded materials. If the excerpts contain the answer, explain it thoroughly with examples. Use emojis, numbered lists, and bullet points. Never invent facts. Use plain text only, no HTML.

**Format:** Use 📚 for overview, 🔍 for details, 💡 for examples, 📊 for takeaways, 🎓 for summary. Use double line breaks.

**Context:** $userProfile\n$metadata

**User question:** $userMessage

**Excerpts from notes:**
$documentContext

Now produce a detailed answer based strictly on those excerpts.";
        } else {
            // No relevant notes – force general knowledge answer
            $systemPrompt = "⚠️ IMPORTANT: The user's uploaded study materials do NOT contain the answer to the current question. You are now **required** to answer using your own general knowledge.

**Rule:** Do NOT say that the information is missing from their notes. Instead, answer the question directly, clearly, and educationally. Start your answer with: '⚠️ Your uploaded notes do not cover this topic. Here is a general answer based on my knowledge:'

Then provide a detailed, well-structured answer. Use emojis, bullet points, and numbered lists where appropriate. Do not use HTML tags.

**User profile:** $userProfile
**Metadata:** $metadata

**User's question:** $userMessage

Now answer the question thoroughly using your general knowledge.";
        }

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
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 2000,
                'temperature' => 0.4,
            ]);

            if ($response->successful()) {
                $answer = $response->json()['choices'][0]['message']['content'] ?? "No response.";
                $answer = preg_replace('/<br\s*\/?>/i', "\n", $answer);
                $answer = strip_tags($answer);
                $answer = preg_replace('/\n{3,}/', "\n\n", $answer);
                return trim($answer);
            } else {
                Log::error('OpenRouter error', ['body' => $response->body()]);
                return "AI service temporarily unavailable.";
            }
        } catch (\Exception $e) {
            Log::error('Chat exception: ' . $e->getMessage());
            return "Sorry, I encountered an error: " . $e->getMessage();
        }
    }
}
