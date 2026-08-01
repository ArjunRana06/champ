<?php

namespace App\Http\Controllers;

use App\Models\Mcq;
use App\Models\TrueFalseQuestion;
use App\Models\ShortAnswer;
use App\Models\FillBlank;
use App\Models\MatchingQuestion;
use App\Models\Flashcard;
use App\Models\Subject;
use App\Services\NotificationService;
use App\Traits\HasQuestionType;
use Illuminate\Http\Request;

class SharedQuestionBankController extends Controller
{
    use HasQuestionType;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $types = ['mcqs', 'true_false', 'short_answers', 'fill_blanks', 'matching', 'flashcards'];
        $subjectId = $request->get('subject', '');
        $search = $request->get('search', '');

        $shared = [];
        $myQuestions = [];
        foreach ($types as $type) {
            $model = $this->getModel($type);

            $sharedQuery = $model::where('is_public', true)->with('user', 'subject');
            if ($subjectId) {
                $sharedQuery->where('subject_id', $subjectId);
            }
            if ($search) {
                $sharedQuery->where(function ($q) use ($search) {
                    $q->where('question', 'like', "%{$search}%")
                      ->orWhere('statement', 'like', "%{$search}%")
                      ->orWhere('front', 'like', "%{$search}%")
                      ->orWhere('sentence_with_blanks', 'like', "%{$search}%");
                });
            }
            $shared[$type] = $sharedQuery->latest()->take(20)->get();

            $myQuery = $model::where('user_id', auth()->id())->with('subject');
            if ($search) {
                $myQuery->where(function ($q) use ($search) {
                    $q->where('question', 'like', "%{$search}%")
                      ->orWhere('statement', 'like', "%{$search}%")
                      ->orWhere('front', 'like', "%{$search}%")
                      ->orWhere('sentence_with_blanks', 'like', "%{$search}%");
                });
            }
            $myQuestions[$type] = $myQuery->latest()->get();
        }

        $subjects = Subject::where(function ($q) {
            $q->where('user_id', auth()->id())->orWhereHas('mcqs', fn($q) => $q->where('is_public', true));
        })->orderBy('name')->get();

        return view('Backend.shared-questions.index', compact('shared', 'types', 'myQuestions', 'subjects', 'subjectId', 'search'));
    }

    public function fetchMore(Request $request)
    {
        $type = $request->get('type');
        $offset = $request->get('offset', 0);
        $limit = 10;

        $model = $this->getModel($type);
        $items = $model::where('is_public', true)
            ->with('user', 'subject')
            ->latest()
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(fn($i) => [
                'id' => $i->id,
                'text' => $i->question ?? $i->statement ?? $i->front ?? $i->sentence_with_blanks ?? 'Question',
                'user' => $i->user?->name ?? 'Unknown',
                'subject' => $i->subject?->name ?? 'General',
            ]);

        return response()->json(['items' => $items, 'has_more' => $items->count() >= $limit]);
    }

    public function toggleVisibility(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:mcqs,true_false,short_answers,fill_blanks,matching,flashcards',
            'id' => 'required|integer',
        ]);

        $model = $this->getModel($request->type);
        $item = $model::where('id', $request->id)->where('user_id', auth()->id())->firstOrFail();
        $wasPublic = $item->is_public;
        $item->update(['is_public' => !$item->is_public]);

        $this->notificationService->notifyVisibilityToggled(auth()->id(), !$wasPublic);

        $nowPublic = !$wasPublic;
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $nowPublic ? 'Question is now public.' : 'Question is now private.',
                'is_public' => $nowPublic,
            ]);
        }
        return back()->with('success', 'Visibility updated.');
    }
}
