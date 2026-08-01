<?php

namespace App\Http\Controllers;

use App\Models\Mcq;
use App\Models\TrueFalseQuestion;
use App\Models\ShortAnswer;
use App\Models\FillBlank;
use App\Models\MatchingQuestion;
use App\Models\Flashcard;
use App\Models\PeerReview;
use App\Services\NotificationService;
use App\Services\GamificationService;
use App\Traits\HasQuestionType;
use Illuminate\Http\Request;

class PeerReviewController extends Controller
{
    use HasQuestionType;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $userId = auth()->id();
        $typeFilter = $request->get('type', '');
        $subjectFilter = $request->get('subject', '');

        $types = ['mcqs', 'true_false', 'short_answers', 'fill_blanks', 'matching', 'flashcards'];

        $available = collect();
        $modelTypeMap = [];
        foreach ($types as $type) {
            $model = $this->getModel($type);
            $modelTypeMap[$model] = $type;

            $query = $model::where('is_public', true)
                ->where('user_id', '!=', $userId)
                ->whereDoesntHave('peerReviews', fn($q) => $q->where('reviewer_id', $userId))
                ->with('user', 'subject');

            if ($typeFilter && $typeFilter !== $type) {
                continue;
            }
            if ($subjectFilter) {
                $query->whereHas('subject', fn($q) => $q->where('id', $subjectFilter));
            }

            $items = $query->inRandomOrder()->take(5)->get()
                ->map(fn($i) => ['type' => $type, 'item' => $i]);
            $available = $available->concat($items);
        }

        $receivedReviews = collect();
        $myQuestionModels = [Mcq::class, TrueFalseQuestion::class, ShortAnswer::class, FillBlank::class, MatchingQuestion::class, Flashcard::class];
        foreach ($myQuestionModels as $model) {
            $ids = $model::where('user_id', $userId)->pluck('id');
            if ($ids->isNotEmpty()) {
                $receivedReviews = $receivedReviews->concat(
                    PeerReview::where('reviewable_type', $model)
                        ->whereIn('reviewable_id', $ids)
                        ->with('reviewer', 'reviewable')
                        ->latest()
                        ->get()
                );
            }
        }
        $receivedReviews = $receivedReviews->sortByDesc('created_at');

        $myReviews = PeerReview::where('reviewer_id', $userId)
            ->with('reviewable')
            ->latest()
            ->paginate(20);

        return view('Backend.peer-reviews.index', compact('myReviews', 'available', 'receivedReviews', 'types', 'typeFilter', 'subjectFilter'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reviewable_type' => 'required|string',
            'reviewable_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $allowedTypes = [Mcq::class, TrueFalseQuestion::class, ShortAnswer::class, FillBlank::class, MatchingQuestion::class, Flashcard::class];
        if (!in_array($request->reviewable_type, $allowedTypes, true)) {
            $msg = 'Invalid reviewable type.';
            if ($request->ajax() || $request->expectsJson()) return response()->json(['success' => false, 'message' => $msg], 422);
            return back()->with('error', $msg);
        }

        $reviewable = (new $request->reviewable_type)->find($request->reviewable_id);
        if (!$reviewable) {
            $msg = 'The question you are trying to review does not exist.';
            if ($request->ajax() || $request->expectsJson()) return response()->json(['success' => false, 'message' => $msg], 404);
            return back()->with('error', $msg);
        }
        if ($reviewable->user_id === auth()->id()) {
            $msg = 'You cannot review your own question.';
            if ($request->ajax() || $request->expectsJson()) return response()->json(['success' => false, 'message' => $msg], 422);
            return back()->with('error', $msg);
        }

        $already = PeerReview::where('reviewer_id', auth()->id())
            ->where('reviewable_type', $request->reviewable_type)
            ->where('reviewable_id', $request->reviewable_id)
            ->exists();
        if ($already) {
            $msg = 'You have already reviewed this question.';
            if ($request->ajax() || $request->expectsJson()) return response()->json(['success' => false, 'message' => $msg], 422);
            return back()->with('error', $msg);
        }

        PeerReview::create([
            'reviewer_id' => auth()->id(),
            'reviewable_type' => $request->reviewable_type,
            'reviewable_id' => $request->reviewable_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        app(GamificationService::class)->awardXp(auth()->user(), 5);

        $this->notificationService->notifyPeerReviewSubmitted(auth()->id());

        $questionTitle = $reviewable->question ?? $reviewable->statement ?? $reviewable->front ?? $reviewable->sentence_with_blanks ?? 'Question';
        $this->notificationService->notifyPeerReviewReceived(
            $reviewable->user_id,
            auth()->user()->name,
            $questionTitle
        );

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Review submitted! +5 XP']);
        }
        return back()->with('success', 'Review submitted! +5 XP');
    }

    public function destroy(Request $request, PeerReview $peerReview)
    {
        if ($peerReview->reviewer_id !== auth()->id()) abort(403);
        $peerReview->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Review deleted.']);
        }
        return back()->with('success', 'Review deleted.');
    }
}
