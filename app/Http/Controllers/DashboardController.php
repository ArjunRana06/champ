<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Document;
use App\Models\Mcq;
use App\Models\TrueFalseQuestion;
use App\Models\ShortAnswer;
use App\Models\FillBlank;
use App\Models\MatchingQuestion;
use App\Models\Flashcard;
use App\Models\Activity;
use App\Models\QuizAttempt;
use App\Models\StudyPlan;
use App\Models\Exam;
use App\Models\PomodoroSession;
use App\Models\TimeEntry;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;


class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Real counts
        $subjectsCount = Subject::where('user_id', $user->id)->count();
        $documentsCount = Document::where('user_id', $user->id)->count();

        $mcqsCount = Mcq::where('user_id', $user->id)->count();
        $tfCount = TrueFalseQuestion::where('user_id', $user->id)->count();
        $saCount = ShortAnswer::where('user_id', $user->id)->count();
        $fbCount = FillBlank::where('user_id', $user->id)->count();
        $matchCount = MatchingQuestion::where('user_id', $user->id)->count();
        $fcCount = Flashcard::where('user_id', $user->id)->count();

        $totalQuestions = $mcqsCount + $tfCount + $saCount + $fbCount + $matchCount + $fcCount;
        $activitiesCount = Activity::where('user_id', $user->id)->count();

        // Performance Analytics
        $quizAttempts = QuizAttempt::where('user_id', $user->id)->count();
        $avgScore = QuizAttempt::where('user_id', $user->id)->avg('score_percentage');
        $recentQuizzes = QuizAttempt::where('user_id', $user->id)->latest()->take(5)->get();
        $studyPlansCount = StudyPlan::where('user_id', $user->id)->count();
        $examsCount = Exam::where('user_id', $user->id)->count();
        $pomodoroCount = PomodoroSession::where('user_id', $user->id)->count();
        $focusMinutes = TimeEntry::where('user_id', $user->id)->sum('duration_minutes');

        // Monthly question generation for chart
        $monthlyQuestions = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $count = Mcq::where('user_id', $user->id)->whereBetween('created_at', [$start, $end])->count()
                + TrueFalseQuestion::where('user_id', $user->id)->whereBetween('created_at', [$start, $end])->count()
                + ShortAnswer::where('user_id', $user->id)->whereBetween('created_at', [$start, $end])->count()
                + FillBlank::where('user_id', $user->id)->whereBetween('created_at', [$start, $end])->count()
                + MatchingQuestion::where('user_id', $user->id)->whereBetween('created_at', [$start, $end])->count()
                + Flashcard::where('user_id', $user->id)->whereBetween('created_at', [$start, $end])->count();

            $monthlyQuestions->push([
                'month' => $month->format('M'),
                'count' => $count,
            ]);
        }

        // Quiz type distribution for pie chart
        $quizDistribution = [
            'MCQ' => $mcqsCount,
            'True/False' => $tfCount,
            'Short Answer' => $saCount,
            'Fill Blanks' => $fbCount,
            'Matching' => $matchCount,
            'Flashcards' => $fcCount,
        ];

        // Recent documents
        $recentDocuments = Document::where('user_id', $user->id)
            ->with('subject')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($doc) => (object)[
                'id' => $doc->id,
                'title' => $doc->original_name ?? 'Untitled',
                'subject' => $doc->subject?->name ?? 'Uncategorized',
                'date' => $doc->created_at->format('M d, Y'),
                'status' => $doc->status ?? 'completed',
            ]);

        // Top subjects by document count
        $topSubjects = Subject::where('user_id', $user->id)
            ->withCount('documents')
            ->orderBy('documents_count', 'desc')
            ->take(5)
            ->get()
            ->map(fn($sub) => [
                'name' => $sub->name,
                'code' => $sub->code,
                'documents' => $sub->documents_count,
                'questions' =>
                    Mcq::where('subject_id', $sub->id)->count()
                    + TrueFalseQuestion::where('subject_id', $sub->id)->count()
                    + ShortAnswer::where('subject_id', $sub->id)->count()
                    + FillBlank::where('subject_id', $sub->id)->count()
                    + MatchingQuestion::where('subject_id', $sub->id)->count()
                    + Flashcard::where('subject_id', $sub->id)->count(),
            ]);

        // Recent activity
        $recentActivities = Activity::where('user_id', $user->id)
            ->with('emotions')
            ->latest()
            ->take(5)
            ->get();

        // Subject distribution for donut chart
        $subjectNames = Subject::where('user_id', $user->id)
            ->withCount('documents')
            ->get()
            ->pluck('documents_count', 'name')
            ->toArray();

        // Recent notifications
        $recentNotifications = Notification::forUser($user->id)->latest()->take(5)->get();
        $unreadNotificationsCount = Notification::forUser($user->id)->unread()->count();

        // Trend calculations
        $lastMonth = now()->subMonth();
        $subjectsTrend = $this->getTrend(
            Subject::where('user_id', $user->id)->whereMonth('created_at', now()->month)->count(),
            Subject::where('user_id', $user->id)->whereMonth('created_at', $lastMonth->month)->count()
        );
        $documentsTrend = $this->getTrend(
            Document::where('user_id', $user->id)->whereMonth('created_at', now()->month)->count(),
            Document::where('user_id', $user->id)->whereMonth('created_at', $lastMonth->month)->count()
        );
        $questionsTrend = $this->getTrend(
            $this->questionsThisMonth($user->id, now()),
            $this->questionsThisMonth($user->id, $lastMonth)
        );
        $activitiesTrend = $this->getTrend(
            Activity::where('user_id', $user->id)->whereMonth('created_at', now()->month)->count(),
            Activity::where('user_id', $user->id)->whereMonth('created_at', $lastMonth->month)->count()
        );

        return view('dashboard', compact(
            'subjectsCount',
            'documentsCount',
            'totalQuestions',
            'activitiesCount',
            'monthlyQuestions',
            'quizDistribution',
            'recentDocuments',
            'topSubjects',
            'recentActivities',
            'recentNotifications',
            'unreadNotificationsCount',
            'subjectNames',
            'subjectsTrend',
            'documentsTrend',
            'questionsTrend',
            'activitiesTrend',
            'quizAttempts',
            'avgScore',
            'recentQuizzes',
            'studyPlansCount',
            'examsCount',
            'pomodoroCount',
            'focusMinutes',
        ));
    }

    private function questionsThisMonth($userId, $date)
    {
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();
        return Mcq::where('user_id', $userId)->whereBetween('created_at', [$start, $end])->count()
            + TrueFalseQuestion::where('user_id', $userId)->whereBetween('created_at', [$start, $end])->count()
            + ShortAnswer::where('user_id', $userId)->whereBetween('created_at', [$start, $end])->count()
            + FillBlank::where('user_id', $userId)->whereBetween('created_at', [$start, $end])->count()
            + MatchingQuestion::where('user_id', $userId)->whereBetween('created_at', [$start, $end])->count()
            + Flashcard::where('user_id', $userId)->whereBetween('created_at', [$start, $end])->count();
    }

    private function getTrend($current, $previous)
    {
        if ($previous == 0) return $current > 0 ? '+100%' : '0%';
        $change = round((($current - $previous) / $previous) * 100);
        return ($change >= 0 ? '+' : '') . $change . '%';
    }
}
