<?php

namespace App\Http\Controllers;

use App\Models\QuizAttempt;
use App\Models\Mcq;
use App\Models\TrueFalseQuestion;
use App\Models\ShortAnswer;
use App\Models\FillBlank;
use App\Models\MatchingQuestion;
use App\Models\Flashcard;
use App\Services\NotificationService;
use App\Services\QuestionDeduplicator;
use Illuminate\Http\Request;

class QuizAttemptController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $attempts = QuizAttempt::where('user_id', auth()->id())->latest()->paginate(20);
        return view('Backend.quiz-attempts.index', compact('attempts'));
    }

    public function create()
    {
        $subjects = auth()->user()->subjects;
        $stats = [
            'mcqs' => Mcq::where('user_id', auth()->id())->count(),
            'true_false' => TrueFalseQuestion::where('user_id', auth()->id())->count(),
            'short_answers' => ShortAnswer::where('user_id', auth()->id())->count(),
            'fill_blanks' => FillBlank::where('user_id', auth()->id())->count(),
            'matching' => MatchingQuestion::where('user_id', auth()->id())->count(),
            'flashcards' => Flashcard::where('user_id', auth()->id())->count(),
        ];
        return view('Backend.quiz-attempts.create', compact('subjects', 'stats'));
    }

    public function start(Request $request)
    {
        $request->validate([
            'type' => 'required|in:mcq,true-false,mixed',
            'subject_id' => 'nullable|exists:subjects,id',
            'count' => 'required|integer|min:1|max:50',
            'time_limit' => 'nullable|integer|min:1|max:180',
            'is_exam_mode' => 'boolean',
        ]);

        $userId = auth()->id();
        $questions = [];

        if ($request->type === 'mcq' || $request->type === 'mixed') {
            $mcqs = Mcq::where('user_id', $userId)
                ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
                ->inRandomOrder()
                ->limit($request->type === 'mixed' ? ceil($request->count / 2) : $request->count)
                ->get();
            foreach ($mcqs as $mcq) {
                $correctIndex = null;
                foreach ($mcq->options as $idx => $opt) {
                    if ($opt === $mcq->correct_answer) {
                        $correctIndex = $idx;
                        break;
                    }
                    $letter = chr(65 + $idx);
                    if ($mcq->correct_answer === $letter) {
                        $correctIndex = $idx;
                        break;
                    }
                }
                $questions[] = [
                    'id' => $mcq->id,
                    'type' => 'mcq',
                    'question' => $mcq->question,
                    'options' => $mcq->options,
                    'correct_answer' => $correctIndex ?? 0,
                    'difficulty' => $mcq->difficulty,
                ];
            }
        }

        if ($request->type === 'true-false' || $request->type === 'mixed') {
            $tfs = TrueFalseQuestion::where('user_id', $userId)
                ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
                ->inRandomOrder()
                ->limit($request->type === 'mixed' ? floor($request->count / 2) : $request->count)
                ->get();
            foreach ($tfs as $tf) {
                $questions[] = [
                    'id' => $tf->id,
                    'type' => 'true-false',
                    'statement' => $tf->statement,
                    'correct_answer' => $tf->correct_answer ? 'true' : 'false',
                    'difficulty' => $tf->difficulty,
                ];
            }
        }

        shuffle($questions);

        $deduplicator = app(QuestionDeduplicator::class);
        $seen = [];
        $uniqueQuestions = [];
        foreach ($questions as $q) {
            $text = $q['question'] ?? $q['statement'] ?? '';
            $key = $deduplicator->normalize($text);
            if ($key === '') {
                continue;
            }
            $isDup = isset($seen[$key]) || collect($seen)->contains(
                fn ($other) => $deduplicator->isSimilar($text, $other)
            );
            if ($isDup) {
                continue;
            }
            $seen[$key] = $text;
            $uniqueQuestions[] = $q;
        }

        $questions = array_slice($uniqueQuestions, 0, $request->count);

        if (empty($questions)) {
            return back()->with('error', 'No questions available for the selected criteria. Generate some questions first.');
        }

        $attempt = QuizAttempt::create([
            'user_id' => $userId,
            'title' => $request->is_exam_mode ? 'Exam Mode' : 'Practice Quiz',
            'type' => $request->type,
            'total_questions' => count($questions),
            'correct_answers' => 0,
            'time_taken_seconds' => 0,
            'is_exam_mode' => $request->is_exam_mode ?? false,
            'answers_data' => ['questions' => $questions],
        ]);

        $this->notificationService->notifyQuizStarted($userId, $attempt->title);

        session(['current_attempt' => $attempt->id, 'time_limit' => $request->time_limit ?? 0]);

        return redirect()->route('quiz-attempts.take', $attempt);
    }

    public function take(QuizAttempt $quizAttempt)
    {
        if ($quizAttempt->user_id !== auth()->id()) abort(403);
        $questions = $quizAttempt->answers_data['questions'] ?? [];
        $timeLimit = session('time_limit', 0);
        return view('Backend.quiz-attempts.take', compact('quizAttempt', 'questions', 'timeLimit'));
    }

    public function submit(Request $request, QuizAttempt $quizAttempt)
    {
        if ($quizAttempt->user_id !== auth()->id()) abort(403);
        if (isset($quizAttempt->answers_data['results'])) {
            return redirect()->route('quiz-attempts.results', $quizAttempt)
                ->with('error', 'This quiz has already been submitted.');
        }

        $questions = $quizAttempt->answers_data['questions'] ?? [];
        $answers = $request->answers ?? [];
        $correctCount = 0;
        $results = [];

        foreach ($questions as $index => $q) {
            $userAnswer = $answers[$index] ?? null;
            $isCorrect = false;

            if ($q['type'] === 'mcq') {
                $isCorrect = isset($userAnswer) && (int)$userAnswer === (int)$q['correct_answer'];
            } elseif ($q['type'] === 'true-false') {
                $isCorrect = strtolower($userAnswer ?? '') === strtolower($q['correct_answer']);
            }

            if ($isCorrect) $correctCount++;
            $results[] = [
                'question_index' => $index,
                'user_answer' => $userAnswer,
                'correct_answer' => $q['correct_answer'] ?? null,
                'is_correct' => $isCorrect,
                'question' => $q,
            ];
        }

        $scorePercentage = $quizAttempt->total_questions > 0
            ? round(($correctCount / $quizAttempt->total_questions) * 100)
            : 0;

        $quizAttempt->update([
            'correct_answers' => $correctCount,
            'score_percentage' => $scorePercentage,
            'time_taken_seconds' => $request->time_taken ?? 0,
            'answers_data' => array_merge($quizAttempt->answers_data ?? [], ['results' => $results]),
        ]);

        $this->notificationService->notifyQuizSubmitted(auth()->id(), $quizAttempt->title, $scorePercentage);

        session()->forget(['current_attempt', 'time_limit']);

        return redirect()->route('quiz-attempts.results', $quizAttempt);
    }

    public function results(QuizAttempt $quizAttempt)
    {
        if ($quizAttempt->user_id !== auth()->id()) abort(403);
        $results = $quizAttempt->answers_data['results'] ?? [];
        $questions = $quizAttempt->answers_data['questions'] ?? [];
        return view('Backend.quiz-attempts.results', compact('quizAttempt', 'results', 'questions'));
    }

    public function destroy(QuizAttempt $quizAttempt)
    {
        if ($quizAttempt->user_id !== auth()->id()) abort(403);
        $quizAttempt->delete();

        $this->notificationService->notifyQuizDeleted(auth()->id());

        return back()->with('success', 'Quiz attempt deleted.');
    }
}
