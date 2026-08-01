<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    protected RAGService $ragService;
    protected AiService $ai;

    public function __construct(RAGService $ragService, AiService $ai)
    {
        $this->ragService = $ragService;
        $this->ai = $ai;
    }

    private function getUserProfileData(): string
    {
        $user = Auth::user();
        if (!$user) return "Student: Unknown\nSubjects: None\n";
        $subjects = $user->subjects()->pluck('name')->toArray();
        $subjectsList = !empty($subjects) ? implode(', ', $subjects) : 'None added yet';
        return "Student: {$user->name}\nSubjects: $subjectsList\n";
    }

    private function getMetadata(): string
    {
        $user = Auth::user();
        if (!$user) return '';
        $completedDocCount = $user->documents()->where('status', 'completed')->count();
        if ($completedDocCount === 0) return '';
        return "The student has $completedDocCount processed document(s) available for context.";
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
            '/^thanks?$/i', '/^thank you$/i', '/^how are you/i', '/^how r u/i',
            '/^kasto xa\??$/i', '/^kasto cha\??$/i', '/^ramro xa\??$/i', '/^thik xa\??$/i',
            '/^malai ni/i', '/^timi lai/i', '/^tapa[i]lai/i',
            '/^yeah\b/i', '/^ok\b/i', '/^okay\b/i', '/^fine/i',
            '/^got it/i',
            '/^what\'s up/i', '/^whats up/i',
            '/^hora\??$/i', '/^ho ra\??$/i', '/^ho\??$/i',
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
            '/^who\s+are\s+u/i',
            '/^tell\s+me\s+about\s+yourself/i',
            '/^who\s+am\s+i/i',
            '/^who\s+am\s+i/i',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($message))) {
                return true;
            }
        }
        if (preg_match('/\bwho\b/i', $message) && preg_match('/\bare\b|\bis\b/i', $message) && preg_match('/\byou\b/i', $message)) {
            return true;
        }
        return false;
    }

    private function isLanguageQuestion(string $message): bool
    {
        if (preg_match('/[\x{0900}-\x{097F}]/u', $message)) {
            return true;
        }
        $patterns = [
            '/understand\s+nepali/i', '/know\s+nepali/i', '/speak\s+nepali/i',
            '/understand\s+spanish/i', '/understand\s+hindi/i',
            '/do you (understand|know|speak) ([a-z]+)/i',
            '/can you (understand|speak) ([a-z]+)/i',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }
        return false;
    }

    private function answerLanguageQuestion(string $message): string
    {
        if (preg_match('/hindi/i', $message) && !preg_match('/nepali/i', $message)) {
            return "Yes, I can understand Hindi! You can ask your study questions in Hindi. I'll answer based on your uploaded study materials.\n\n*Note: Your notes appear to be in English, so my answers will reference them.*";
        }
        if (preg_match('/[\x{0900}-\x{097F}]/u', $message)) {
            return "Yes, I can understand Nepali! Feel free to ask your study questions in Nepali.\n\n*Note: Your uploaded notes are mostly in English, so my answers will be based on those, but I can converse in Nepali.*";
        }
        if (preg_match('/spanish/i', $message)) {
            return "Yes, I understand Spanish! Feel free to ask your study questions in Spanish.";
        }
        return "Yes, I can understand many languages! I can process and respond in English, Nepali, Hindi, Spanish, French, German, and more. Just ask your question in the language you're comfortable with.";
    }

    private function casualResponse(string $message): string
    {
        $user = Auth::user();
        $userName = $user?->name ?? 'there';
        $hasDocs = $user && $user->documents()->where('status', 'completed')->count() > 0;
        $subjects = $user ? $user->subjects()->pluck('name')->toArray() : [];
        $subjectHint = !empty($subjects) ? ' I see you\'re studying: ' . implode(', ', $subjects) . '.' : '';

        if (preg_match('/how\s+(are|r)\s+(you|u)/i', $message) || preg_match('/how\'?s\s+it\s+going/i', $message)) {
            $responses = [
                "I'm doing great, thanks for asking! Ready to help you study.$subjectHint What would you like to work on?",
                "All set to help you learn.$subjectHint Got any questions from your notes?",
                "Feeling sharp and ready to teach!$subjectHint What subject shall we dive into?",
            ];
            return $responses[array_rand($responses)];
        }

        if (preg_match('/kasto xa|kasto cha|ramro xa|thik xa/i', $message)) {
            return "I'm fine, thank you! How about you?\n\nDo you have any study questions? I'm ready to help!";
        }

        if (preg_match('/^(thanks?|thank you|got it|ok|okay|fine|i understand|yeah)$/i', trim($message))) {
            $responses = [
                "You're welcome! Let me know if you need any clarification.",
                "Glad I could help! Anything else you'd like to explore?",
                "No problem! Ready whenever you have more questions.",
            ];
            return $responses[array_rand($responses)];
        }

        if (!$hasDocs) {
            return "Hi $userName! I'm your AI study assistant.$subjectHint\n\nTo get started, upload your study materials (PDFs, notes, slides) and I can help explain concepts, generate practice questions, and create study plans from them.\n\n**What would you like to do?**\n- Upload your notes to get started\n- Ask a general study question\n- Get study tips or motivation";
        }

        $greetings = [
            "Hi $userName! I'm ready to help you study!$subjectHint\n\nWhat concept would you like me to explain from your notes? I can also generate quizzes, flashcards, or a study plan for you.",
            "Hey $userName!$subjectHint Ready to dive into some studying? Ask me anything from your notes!",
            "Hi $userName! I'm here to help.$subjectHint Would you like a quiz, a flashcard review, or an explanation of a concept?",
        ];
        return $greetings[array_rand($greetings)];
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

        return $this->callOpenRouter($systemPrompt, $history, $userMessage);
    }

    private function cleanResponse(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/^User\s+Safety\s*:\s*(safe|unsafe)\s*/i', '', $text);
        $text = preg_replace('/^Safety\s*:\s*safe\s*/i', '', $text);
        $text = preg_replace('/^Output\s*:\s*/i', '', $text);
        return trim($text);
    }

    private function callOpenRouter(string $systemPrompt, array $history, string $userMessage): string
    {
        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($history as $turn) {
            $role = $turn['role'] ?? 'user';
            if ($role === 'system') continue;
            $messages[] = ['role' => $role, 'content' => $turn['content'] ?? ''];
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            $answer = $this->ai->chat($messages, null, 0.4, 2048);
            return $this->cleanResponse($answer);
        } catch (\Exception $e) {
            Log::error('ChatbotService callOpenRouter failed', ['error' => $e->getMessage()]);
            return "I'm having trouble connecting to the AI service right now. Please try again in a moment.";
        }
    }

    private function answerIdentityQuestion(string $message): string
    {
        $user = Auth::user();
        $userName = $user?->name ?? 'there';
        $hasDocs = $user && $user->documents()->where('status', 'completed')->count() > 0;
        $subjects = $user ? $user->subjects()->pluck('name')->toArray() : [];
        $subjectHint = !empty($subjects) ? ' I see you\'re studying: ' . implode(', ', $subjects) . '.' : '';

        if (preg_match('/who\s+am\s+i/i', $message)) {
            $response = "You're **$userName**, a student!$subjectHint\n\n";
            $response .= $hasDocs
                ? "You have uploaded study materials. Ask me to explain concepts, generate quiz questions, or create a study plan!"
                : "You haven't uploaded any study materials yet. Upload your PDFs, notes, or slides and I'll help you master any topic!";
            return $response;
        }

        $response = "I'm your **AI Study Assistant**! I'm here to help you learn better.\n\n";
        $response .= "**What I can do:**\n";
        $response .= "- **Explain concepts** from your uploaded notes\n";
        $response .= "- **Generate practice questions** (MCQs, True/False, Short Answer, Flashcards)\n";
        $response .= "- **Create study plans** based on your subjects and exam dates\n";
        $response .= "- **Summarize** your documents\n";
        $response .= "- **Track your study time** with Pomodoro timer\n\n";

        $response .= $hasDocs
            ? "You've already uploaded materials$subjectHint — ask me anything about them!"
            : "To get started, upload your study materials and I'll analyze them for you!";
        return $response;
    }

    private function buildPrompt(string $userMessage, string $persona, bool $useRag): string
    {
        $personaIntro = $this->getPersonaIntro($persona);
        $userProfile = $this->getUserProfileData();
        $metadata = $this->getMetadata();

        $formatRules = "**Formatting rules:**\n- Use Markdown for structure (headers, bold, bullet points, numbered lists)\n- Use code blocks for code or formulas\n- Be thorough but organized\n- No HTML tags\n- Use emojis sparingly for emphasis\n";

        if (!$useRag) {
            return "$personaIntro\n\n$formatRules\nAnswer the student's question using your general knowledge. Be educational and engaging.\n\n**Student:** $userProfile\n" . ($metadata ? "**Context:** $metadata\n\n" : "\n") . "Student's question: $userMessage\n\nProvide a clear, detailed answer.";
        }

        $documentContext = $this->getDocumentContext($userMessage);
        $hasRelevantContent = !str_contains($documentContext, '__NO_RELEVANT_CONTENT__');

        $user = Auth::user();
        $hasAnyDocs = $user && $user->documents()->where('status', 'completed')->count() > 0;
        $subjectNames = $user ? $user->subjects()->pluck('name')->toArray() : [];

        if ($hasRelevantContent) {
            return "$personaIntro\n\n$formatRules\nThe student's uploaded notes contain relevant material. Use the excerpts below as your PRIMARY source. Supplement with general knowledge only when the notes don't fully cover the question.\n\n**Rules:**\n- Cite source after each key point: `[Source: DocumentName.pdf]`\n- Don't cite sources for general knowledge补充\n\n**Student:** $userProfile\n" . ($metadata ? "**Context:** $metadata\n" : '') . "\n**Excerpts from the student's notes:**\n$documentContext\n\nStudent's question: $userMessage\n\nProvide a detailed answer starting with what the notes say.";
        }

        if ($hasAnyDocs) {
            $topics = !empty($subjectNames) ? implode(', ', $subjectNames) : 'various topics';
            return "$personaIntro\n\n$formatRules\nThe student has notes on: $topics, but this specific question isn't covered. Answer using your general knowledge.\n\n**Student:** $userProfile\n\nStudent's question: $userMessage\n\nProvide a detailed, educational answer.";
        }

        return "$personaIntro\n\n$formatRules\nThe student hasn't uploaded study materials yet. Answer using your general knowledge, and invite them to upload notes for personalized help.\n\n**Student:** $userProfile\n\nStudent's question: $userMessage\n\nProvide a detailed, educational answer.";
    }

    private function getPersonaIntro(string $persona): string
    {
        $personas = [
            'default' => "You are a brilliant, enthusiastic professor. Respond in clear, well-organized Markdown.",
            'strict' => "You are a strict, no-nonsense professor. Be direct, precise, and formal. Use technical terminology. No emojis. Focus on accuracy.",
            'friendly' => "You are a friendly, encouraging peer tutor. Be warm and supportive. Use casual language and celebrate small wins.",
            'socratic' => "You are a Socratic tutor. Do NOT give direct answers. Guide the student by asking probing questions that lead them to discover the answer.",
            'simplifier' => "You are an expert at explaining complex topics simply. Use analogies, metaphors, and real-world examples. Break down difficult concepts into digestible chunks.",
        ];
        return $personas[$persona] ?? $personas['default'];
    }
}
