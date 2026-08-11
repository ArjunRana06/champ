<?php

namespace App\Http\Controllers;

use App\Models\FillBlank;
use App\Models\Flashcard;
use App\Models\GroupResource;
use App\Models\MatchingQuestion;
use App\Models\Mcq;
use App\Models\PeerReview;
use App\Models\ShortAnswer;
use App\Models\StudyGroup;
use App\Models\StudyGroupMember;
use App\Models\TrueFalseQuestion;
use App\Services\GamificationService;
use App\Services\NotificationService;
use App\Traits\HasQuestionType;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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

        $myGroups = StudyGroup::whereHas('members', fn ($q) => $q->where('user_id', $userId))
            ->withCount('members')
            ->latest()
            ->get();

        $hasGroups = $myGroups->isNotEmpty();
        $memberIds = $this->groupMemberIds($userId);
        $myGroupIds = $this->userGroupIds($userId);

        $available = collect();
        $modelTypeMap = [];
        if ($hasGroups) {
            foreach ($types as $type) {
                $model = $this->getModel($type);
                $modelTypeMap[$model] = $type;

                $sharedIds = GroupResource::whereIn('study_group_id', $myGroupIds)
                    ->where('resourceable_type', $model)
                    ->pluck('resourceable_id');

                $query = $model::whereIn('user_id', $memberIds)
                    ->where('user_id', '!=', $userId)
                    ->whereIn('id', $sharedIds)
                    ->whereDoesntHave('peerReviews', fn ($q) => $q->where('reviewer_id', $userId))
                    ->with('user', 'subject');

                if ($typeFilter && $typeFilter !== $type) {
                    continue;
                }
                if ($subjectFilter) {
                    $query->whereHas('subject', fn ($q) => $q->where('id', $subjectFilter));
                }

                $items = $query->inRandomOrder()->take(5)->get()
                    ->map(fn ($i) => ['type' => $type, 'item' => $i]);
                $available = $available->concat($items);
            }
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

        return view('Backend.peer-reviews.index', compact('myReviews', 'available', 'receivedReviews', 'types', 'typeFilter', 'subjectFilter', 'myGroups', 'hasGroups'));
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
        if (! in_array($request->reviewable_type, $allowedTypes, true)) {
            $msg = 'Invalid reviewable type.';
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $reviewable = (new $request->reviewable_type)->find($request->reviewable_id);
        if (! $reviewable) {
            $msg = 'The question you are trying to review does not exist.';
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }

            return back()->with('error', $msg);
        }
        if ($reviewable->user_id === auth()->id()) {
            $msg = 'You cannot review your own question.';
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        if (! $this->userHasGroup(auth()->id())) {
            $msg = 'Join or create a study group to review questions.';
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }

            return back()->with('error', $msg);
        }

        if (! $this->groupMemberIds(auth()->id())->contains($reviewable->user_id)) {
            $msg = 'You can only review questions shared by members of your study groups.';
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }

            return back()->with('error', $msg);
        }

        if (! $this->isSharedWithUser($request->reviewable_type, $request->reviewable_id, auth()->id())) {
            $msg = 'Only shared questions can be reviewed.';
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }

            return back()->with('error', $msg);
        }

        $already = PeerReview::where('reviewer_id', auth()->id())
            ->where('reviewable_type', $request->reviewable_type)
            ->where('reviewable_id', $request->reviewable_id)
            ->exists();
        if ($already) {
            $msg = 'You have already reviewed this question.';
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

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
        if ($peerReview->reviewer_id !== auth()->id()) {
            abort(403);
        }
        $peerReview->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Review deleted.']);
        }

        return back()->with('success', 'Review deleted.');
    }

    private function userHasGroup(int $userId): bool
    {
        return StudyGroupMember::where('user_id', $userId)->exists();
    }

    private function userGroupIds(int $userId): Collection
    {
        return StudyGroupMember::where('user_id', $userId)->pluck('study_group_id')->values();
    }

    private function groupMemberIds(int $userId): Collection
    {
        $groupIds = $this->userGroupIds($userId);
        if ($groupIds->isEmpty()) {
            return collect();
        }

        return StudyGroupMember::whereIn('study_group_id', $groupIds)
            ->pluck('user_id')
            ->unique()
            ->values();
    }

    private function isSharedWithUser(string $modelClass, int $questionId, int $userId): bool
    {
        $item = $modelClass::where('id', $questionId)->first();
        if (! $item) {
            return false;
        }

        $groupIds = $this->userGroupIds($userId);

        return GroupResource::whereIn('study_group_id', $groupIds)
            ->where('resourceable_type', $modelClass)
            ->where('resourceable_id', $questionId)
            ->exists();
    }
}
