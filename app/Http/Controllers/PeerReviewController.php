<?php

namespace App\Http\Controllers;

use App\Models\Mcq;
use App\Models\TrueFalseQuestion;
use App\Models\ShortAnswer;
use App\Models\FillBlank;
use App\Models\MatchingQuestion;
use App\Models\Flashcard;
use App\Models\PeerReview;
use Illuminate\Http\Request;

class PeerReviewController extends Controller
{
    public function index()
    {
        $reviews = PeerReview::where('reviewer_id', auth()->id())
            ->with('reviewable')
            ->latest()->paginate(20);

        $available = collect();
        $types = ['mcqs', 'true_false', 'short_answers', 'fill_blanks', 'matching', 'flashcards'];
        foreach ($types as $type) {
            $model = $this->getModel($type);
            $items = $model::where('is_public', true)
                ->where('user_id', '!=', auth()->id())
                ->whereDoesntHave('peerReviews', fn($q) => $q->where('reviewer_id', auth()->id()))
                ->with('user', 'subject')
                ->inRandomOrder()
                ->take(5)
                ->get()
                ->map(fn($i) => ['type' => $type, 'item' => $i]);
            $available = $available->concat($items);
        }

        $receivedReviews = collect();
        foreach ($types as $type) {
            $model = $this->getModel($type);
            $ids = $model::where('user_id', auth()->id())->pluck('id');
            if ($ids->isNotEmpty()) {
                $receivedReviews = $receivedReviews->concat(
                    PeerReview::whereIn('reviewable_id', $ids)
                        ->where('reviewable_type', $model)
                        ->with('reviewer', 'reviewable')
                        ->latest()
                        ->get()
                );
            }
        }
        $receivedReviews = $receivedReviews->sortByDesc('created_at');

        return view('Backend.peer-reviews.index', compact('reviews', 'available', 'receivedReviews'));
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
            return back()->with('error', 'Invalid reviewable type.');
        }

        $reviewable = (new $request->reviewable_type)->find($request->reviewable_id);
        if (!$reviewable) {
            return back()->with('error', 'The question you are trying to review does not exist.');
        }
        if ($reviewable->user_id === auth()->id()) {
            return back()->with('error', 'You cannot review your own question.');
        }

        $already = PeerReview::where('reviewer_id', auth()->id())
            ->where('reviewable_type', $request->reviewable_type)
            ->where('reviewable_id', $request->reviewable_id)
            ->exists();
        if ($already) {
            return back()->with('error', 'You have already reviewed this question.');
        }

        PeerReview::create([
            'reviewer_id' => auth()->id(),
            'reviewable_type' => $request->reviewable_type,
            'reviewable_id' => $request->reviewable_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        app(\App\Services\GamificationService::class)->awardXp(auth()->user(), 5);

        return back()->with('success', 'Review submitted! +5 XP');
    }

    private function getModel(string $type): string
    {
        return match ($type) {
            'mcqs' => Mcq::class,
            'true_false' => TrueFalseQuestion::class,
            'short_answers' => ShortAnswer::class,
            'fill_blanks' => FillBlank::class,
            'matching' => MatchingQuestion::class,
            'flashcards' => Flashcard::class,
            default => throw new \InvalidArgumentException("Invalid question type: {$type}"),
        };
    }
}
