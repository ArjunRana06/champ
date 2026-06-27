<?php

namespace App\Services;

use GuzzleHttp\Client;
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
        $subjects = $user->subjects()->pluck('name')->toArray();
        $subjectsList = !empty($subjects) ? implode(', ', $subjects) : 'None added yet';
        return "Student: {$user->name}\nSubjects: $subjectsList\n";
    }

    private function getMetadata(): string
    {
        $user = Auth::user();
        $subjectCount = $user->subjects()->count();
        $documentCount = $user->documents()->count();
        $completedDocCount = $user->documents()->where('status', 'completed')->count();
        $recentDocs = $user->documents()
            ->where('status', 'completed')
            ->latest()
            ->take(3)
            ->pluck('original_name')
            ->toArray();
        $recentDocsList = !empty($recentDocs)
            ? "\nRecent documents: " . implode(', ', $recentDocs)
            : '';

        return "You have $subjectCount subject(s) and $documentCount document(s) uploaded. " .
               "Of these, $completedDocCount document(s) have been fully processed and are searchable.$recentDocsList";
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
        return false;
    }

    private function isIdentityQuestion(string $message): bool
    {
        $patterns = [
            '/^(who|what)\s+(are|is)\s+(you|this)/i',
            '/^who\s+(r|are)\s+u/i',
            '/^(who|what)\s+are\s+you/i',
            '/^(who|what)\s+is\s+this/i',
            '/^tell\s+me\s+about\s+yourself/i',
            '/^who\s+(a|a)m\s+i/i',
            '/^who\s+i\s+(a|a)m/i',
            '/^do\s+you\s+know\s+(who|what)\s+(i|this)\s+(a|a)m/i',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($message))) {
                return true;
            }
        }
        if (preg_match('/\bwho\b.*\b(?:r|are|is)\b/i', $message) && preg_match('/\bu\b|you/i', $message)) {
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
        $user = Auth::user();
        $userName = $user->name ?? 'there';
        $hasDocs = $user && $user->documents()->where('status', 'completed')->count() > 0;
        $subjects = $user ? $user->subjects()->pluck('name')->toArray() : [];
        $subjectHint = !empty($subjects)
            ? ' I see you\'re studying: ' . implode(', ', $subjects) . '.'
            : '';

        // "How are you" responses
        if (preg_match('/how\s+(are|r)\s+(you|u)/i', $message) || preg_match('/how\'?s\s+it\s+going/i', $message)) {
            $responses = [
                "I'm doing great, thanks for asking! 😊 Ready to help you study.$subjectHint What would you like to work on?",
                "I'm awesome! 💪 All set to help you learn.$subjectHint Got any questions from your notes?",
                "Feeling sharp and ready to teach! 🧠$subjectHint What subject shall we dive into?",
            ];
            return $responses[array_rand($responses)];
        }

        // Nepali casual responses
        if (preg_match('/kasto xa|kasto cha|ramro xa|thik xa|timi lai|malai ni|tapa[i]lai/i', $message)) {
            return "😊 म ठीक छु, तपाईलाई कस्तो छ?\n\n(I'm fine, thank you. How about you?)\n\nके तपाईलाई अध्ययन सम्बन्धी कुनै प्रश्न सोध्नु छ? 📚";
        }

        // Thanks / acknowledgments
        if (preg_match('/^(thanks?|thank you|got it|ok|okay|fine|i understand|yeah)\b/i', trim($message))) {
            $responses = [
                "You're welcome! 😊 Let me know if you need any clarification.",
                "Glad I could help! 🎉 Anything else you'd like to explore?",
                "No problem! 📚 Ready whenever you have more questions.",
            ];
            return $responses[array_rand($responses)];
        }

        // Default greeting
        if (!$hasDocs) {
            return "👋 Hi $userName! I'm your AI study assistant.$subjectHint\n\n📚 To get started, upload your study materials (PDFs, notes, slides) and I can help explain concepts, generate practice questions, and create study plans from them.\n\n📝 What would you like to do?\n• Upload your notes to get started\n• Ask a general study question\n• Get study tips or motivation";
        }

        $greetings = [
            "😊 Hi $userName! I'm ready to help you study!$subjectHint\n\n📝 What concept would you like me to explain from your notes? I can also generate quizzes, flashcards, or a study plan for you.",
            "👋 Hey $userName!$subjectHint Ready to dive into some studying? Ask me anything from your notes!",
            "📚 Hi $userName! I'm here to help.$subjectHint Would you like a quiz, a flashcard review, or an explanation of a concept?",
        ];
        return $greetings[array_rand($greetings)];
    }

    private function buildPrompt(string $userMessage, string $persona, bool $useRag): string
    {
        $personaIntro = $this->getPersonaIntro($persona);
        $userProfile = $this->getUserProfileData();
        $metadata = $this->getMetadata();

        if (!$useRag) {
            return "{$personaIntro}

Answer the student's question using your general knowledge. Be thorough, educational, and engaging. Use emojis, bullet points, and clear examples.

**Student:** $userProfile
**Study info:** $metadata

Student's question: $userMessage

Provide a detailed, clear answer.";
        }

        $documentContext = $this->getDocumentContext($userMessage);
        $hasRelevantContent = !str_contains($documentContext, "__NO_RELEVANT_CONTENT__");

        $user = Auth::user();
        $hasAnyDocs = $user && $user->documents()->where('status', 'completed')->count() > 0;
        $subjectNames = $user ? $user->subjects()->pluck('name')->toArray() : [];

        if ($hasRelevantContent) {
            return "{$personaIntro}

The student's uploaded notes contain relevant material. Use the excerpts below as your PRIMARY source, but if the question goes beyond what the excerpts cover, you may supplement with your general knowledge.

**Rules:**
- Cite source after each key point from notes: `[Source: DocumentName.pdf]`
- If you add general knowledge beyond the notes, don't cite a source for that part.
- Use emojis, bullet points, numbered lists.
- Plain text only, no HTML.
- Be thorough and educational.

**Student:** $userProfile
**Study info:** $metadata

**Excerpts from the student's notes:**
$documentContext

Student's question: $userMessage

Provide a detailed answer. Start with what the notes say, then add any helpful context from your knowledge if needed. Always cite sources for note content.";
        }

        if ($hasAnyDocs) {
            return "{$personaIntro}

The student has uploaded notes on: " . (!empty($subjectNames) ? implode(', ', $subjectNames) : 'various topics') . ", but this specific question isn't covered in those materials.

Answer their question using your general knowledge. First briefly mention their notes don't cover this, then give a full helpful answer. Do NOT cite sources since the info isn't from their notes.

**Student:** $userProfile
**Study info:** $metadata

Student's question: $userMessage

Provide a detailed, educational answer.";
        }

        return "{$personaIntro}

The student hasn't uploaded any study materials yet. Answer their question using your general knowledge, and warmly invite them to upload notes for more personalized help.

**Student:** $userProfile
**Study info:** $metadata

Student's question: $userMessage

Provide a detailed, educational answer.";
    }

    private function buildGeneralPrompt(string $userMessage, string $persona): ?string
    {
        return $this->buildPrompt($userMessage, $persona, false);
    }

    public function chat(string $userMessage, array $history = [], string $persona = 'default', bool $useRag = true): string
    {
        if ($this->isLanguageQuestion($userMessage)) {
            return $this->answerLanguageQuestion($userMessage);
        }

        if ($this->isIdentityQuestion($userMessage)) {
            return $this->answerIdentityQuestion($userMessage);
        }

        if ($this->isCasualConversation($userMessage)) {
            return $this->casualResponse($userMessage);
        }

        $systemPrompt = $this->buildPrompt($userMessage, $persona, $useRag);

        $answer = $this->callOpenRouter($systemPrompt, $history, $userMessage);

        // If answer is empty or looks like an error/annotation, retry without RAG
        if (empty($this->cleanResponse($answer)) || strlen(trim($answer)) < 10) {
            $fallbackPrompt = $this->buildPrompt($userMessage, $persona, false);
            $answer = $this->callOpenRouter($fallbackPrompt, $history, $userMessage);
        }

        return $answer;
    }

    public function chatStream(string $userMessage, array $history, string $persona, callable $onChunk, bool $useRag = true): string
    {
        if ($this->isLanguageQuestion($userMessage)) {
            $response = $this->answerLanguageQuestion($userMessage);
            $onChunk($response);
            return $response;
        }

        if ($this->isIdentityQuestion($userMessage)) {
            $response = $this->answerIdentityQuestion($userMessage);
            $onChunk($response);
            return $response;
        }

        if ($this->isCasualConversation($userMessage)) {
            $response = $this->casualResponse($userMessage);
            $onChunk($response);
            return $response;
        }

        $systemPrompt = $this->buildPrompt($userMessage, $persona, $useRag);

        $answer = $this->callOpenRouterStream($systemPrompt, $history, $userMessage, $onChunk);

        // If answer is empty or looks like an error/annotation, retry without RAG
        if (empty($this->cleanResponse($answer)) || strlen(trim($answer)) < 10) {
            $fallbackPrompt = $this->buildPrompt($userMessage, $persona, false);
            $answer = $this->callOpenRouterStream($fallbackPrompt, $history, $userMessage, $onChunk);
        }

        return $answer;
    }

    private function cleanResponse(string $text): string
    {
        $text = trim($text);
        // Remove "User Safety: safe" or similar safety annotations from some free models
        $text = preg_replace('/^User\s+Safety\s*:\s*safe\s*/i', '', $text);
        $text = preg_replace('/^User\s+Safety\s*:\s*unsafe\s*/i', '', $text);
        $text = preg_replace('/^Safety\s*:\s*safe\s*/i', '', $text);
        $text = preg_replace('/^Output\s*:\s*/i', '', $text);
        return trim($text);
    }

    private function callOpenRouter(string $systemPrompt, array $history, string $userMessage): string
    {
        $apiKey = config('services.openrouter.api_key');
        if (!$apiKey) {
            return "AI service is not configured.";
        }

        $models = [
            config('services.openrouter.model'),
            ...config('services.openrouter.fallback_models', []),
        ];

        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($history as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => $turn['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $lastError = null;
        foreach ($models as $model) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(60)->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'max_tokens' => 2048,
                    'temperature' => 0.4,
                ]);

                if ($response->successful()) {
                    $answer = $response->json()['choices'][0]['message']['content'] ?? "No response.";
                    return $this->cleanResponse($answer);
                }

                $lastError = $response->body();
                Log::warning("OpenRouter model $model failed", ['body' => $lastError]);
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("OpenRouter model $model exception", ['error' => $lastError]);
            }
            usleep(300_000); // 0.3s delay before fallback
        }

        Log::error('OpenRouter: all models failed', ['error' => $lastError]);
        return "AI service temporarily unavailable.";
    }

    private function callOpenRouterStream(string $systemPrompt, array $history, string $userMessage, callable $onChunk): string
    {
        $apiKey = config('services.openrouter.api_key');
        if (!$apiKey) {
            $error = "AI service is not configured.";
            $onChunk($error);
            return $error;
        }

        $models = [
            config('services.openrouter.model'),
            ...config('services.openrouter.fallback_models', []),
        ];

        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($history as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => $turn['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $lastError = null;
        foreach ($models as $model) {
            try {
                $client = new Client();
                $response = $client->post('https://openrouter.ai/api/v1/chat/completions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => $model,
                        'messages' => $messages,
                        'max_tokens' => 2048,
                        'temperature' => 0.4,
                        'stream' => true,
                    ],
                    'stream' => true,
                    'timeout' => 60,
                ]);

                $stream = $response->getBody()->detach();
                $fullResponse = '';

                while (!feof($stream)) {
                    $line = fgets($stream);
                    if ($line === false) break;
                    $line = trim($line);

                    if (empty($line)) continue;
                    if ($line === 'data: [DONE]') break;

                    if (str_starts_with($line, 'data: ')) {
                        $json = json_decode(substr($line, 6), true);
                        if ($json && isset($json['choices'][0]['delta']['content'])) {
                            $content = $json['choices'][0]['delta']['content'];
                            $fullResponse .= $content;
                            $onChunk($content);
                        }
                    }
                }
                fclose($stream);

                return $this->cleanResponse($fullResponse);
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("OpenRouter stream model $model failed", ['error' => $lastError]);
                usleep(300_000);
            }
        }

        $error = "AI service temporarily unavailable.";
        $onChunk($error);
        return $error;
    }

    private function answerIdentityQuestion(string $message): string
    {
        $user = Auth::user();
        $userName = $user->name ?? 'there';
        $hasDocs = $user && $user->documents()->where('status', 'completed')->count() > 0;
        $subjects = $user ? $user->subjects()->pluck('name')->toArray() : [];
        $subjectHint = !empty($subjects) ? ' I see you\'re studying: ' . implode(', ', $subjects) . '.' : '';

        // "Who am I" type questions
        if (preg_match('/who\s+(a|a)m\s+i/i', $message) || preg_match('/who\s+i\s+(a|a)m/i', $message)) {
            $response = "🎓 You're **$userName**, a student!$subjectHint\n\n";
            if ($hasDocs) {
                $response .= "You have uploaded study materials that I can help you with. Ask me to explain concepts from your notes, generate quiz questions, or create a study plan!";
            } else {
                $response .= "You haven't uploaded any study materials yet. Upload your PDFs, notes, or slides and I'll help you master any topic!";
            }
            return $response;
        }

        // "Who are you" type questions
        $response = "🤖 I'm your **AI Study Assistant**! I'm here to help you learn better by working with your own study materials.\n\n";
        $response .= "**What I can do:**\n";
        $response .= "📖 **Explain concepts** from your uploaded notes\n";
        $response .= "❓ **Generate practice questions** (MCQs, True/False, Short Answer, Fill-in-Blanks, Matching, Flashcards)\n";
        $response .= "📋 **Create study plans** based on your subjects and exam dates\n";
        $response .= "📝 **Summarize** your documents\n";
        $response .= "⏱️ **Track your study time** with Pomodoro timer\n\n";

        if ($hasDocs) {
            $response .= "You've already uploaded some materials$subjectHint — ask me anything about them!";
        } else {
            $response .= "To get started, upload your study materials and I'll analyze them for you!";
        }
        return $response;
    }

    private function getPersonaIntro(string $persona): string
    {
        $personas = [
            'default' => "You are a brilliant, enthusiastic professor.",
            'strict' => "You are a strict, no-nonsense professor. Be direct, precise, and formal. Use technical terminology. Do not use emojis. Focus on accuracy and completeness.",
            'friendly' => "You are a friendly, encouraging peer tutor. Be warm, supportive, and use casual language. Celebrate small wins with the student. Use emojis freely.",
            'socratic' => "You are a Socratic tutor. Do NOT give direct answers. Instead, guide the student by asking probing questions that lead them to discover the answer themselves. Use questions like 'What do you think...?', 'How would you define...?', 'What evidence supports...?'.",
            'simplifier' => "You are an expert at explaining complex topics simply. Use analogies, metaphors, and real-world examples. Break down difficult concepts into digestible chunks. Assume the student has no prior knowledge of the topic.",
        ];
        return $personas[$persona] ?? $personas['default'];
    }
}
