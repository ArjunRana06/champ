<?php

namespace App\Http\Controllers;

use App\Models\Flashcard;
use App\Models\SpacedRepetition;
use App\Services\NotificationService;
use App\Services\QuestionDeduplicator;
use App\Services\QuestionGeneratorService;
use App\Services\SpacedRepetitionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FlashcardController extends Controller
{
    protected $generatorService;

    protected $notificationService;

    public function __construct(QuestionGeneratorService $generatorService, NotificationService $notificationService)
    {
        $this->generatorService = $generatorService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $userId = auth()->id();

        $reps = SpacedRepetition::where('user_id', $userId)
            ->where('reviewable_type', Flashcard::class)
            ->get()
            ->keyBy('reviewable_id');

        $query = Flashcard::where('user_id', $userId);

        if ($request->boolean('due')) {
            $dueIds = $reps->filter(fn ($r) => $r->next_review_at <= now()->startOfDay())->keys();
            $unreviewedIds = Flashcard::where('user_id', $userId)
                ->whereNotIn('id', $reps->keys())
                ->pluck('id');

            $query->whereIn('id', $dueIds->merge($unreviewedIds)->unique());
        }

        $flashcards = $query->latest()->paginate(20);

        $dueCount = Flashcard::where('user_id', $userId)
            ->where(function ($q) use ($reps) {
                $dueIds = $reps->filter(fn ($r) => $r->next_review_at <= now()->startOfDay())->keys();

                $q->whereIn('id', $dueIds)
                    ->orWhereNotIn('id', $reps->keys());
            })
            ->count();

        return view('Backend.flashcards.index', compact('flashcards', 'reps', 'dueCount'));
    }

    public function review(Request $request, Flashcard $flashcard)
    {
        if ($flashcard->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate(['quality' => 'required|integer|min:0|max:5']);

        app(SpacedRepetitionService::class)->review(
            auth()->id(),
            Flashcard::class,
            $flashcard->id,
            (int) $request->quality
        );

        $rep = SpacedRepetition::where('user_id', auth()->id())
            ->where('reviewable_type', Flashcard::class)
            ->where('reviewable_id', $flashcard->id)
            ->first();

        return response()->json([
            'success' => true,
            'next_review_at' => $rep?->next_review_at?->format('M j, Y'),
        ]);
    }

    public function create()
    {
        $subjects = auth()->user()->subjects;

        return view('Backend.flashcards.create', compact('subjects'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'subject_id' => ['nullable', Rule::exists('subjects', 'id')->where('user_id', auth()->id())],
            'document_id' => ['nullable', Rule::exists('documents', 'id')->where('user_id', auth()->id())],
            'count' => 'required|integer|min:1|max:20',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'topic' => 'nullable|string|max:255',
        ]);

        $userId = auth()->id();

        $chunks = $this->generatorService->getRelevantChunks(
            $request->subject_id,
            $request->document_id,
            $request->topic,
            $request->count * 2
        );

        if (empty($chunks)) {
            return back()->with('error', 'No relevant content found in your uploaded materials for this subject/topic.');
        }

        $existingTexts = app(QuestionDeduplicator::class)->collectUserQuestionTexts(
            $userId,
            $request->subject_id,
            $request->document_id
        );

        $promptExisting = array_slice($existingTexts, 0, 60);

        try {
            $flashcards = $this->generatorService->generateFlashcards($chunks, $request->count, $request->difficulty ?? 'medium', $promptExisting);
            $flashcards = $this->generatorService->validateGenerated('flashcards', $flashcards);
            $flashcards = app(QuestionDeduplicator::class)->filterDuplicatesSemantic($flashcards, $existingTexts, 'front');
        } catch (\Exception $e) {
            return back()->with('error', 'AI generation failed: '.$e->getMessage());
        }

        if (empty($flashcards)) {
            return back()->with('error', 'The AI did not generate new valid flashcards — everything it produced was a duplicate of your existing flashcards. Please try again.');
        }

        $created = 0;
        foreach ($flashcards as $data) {
            Flashcard::create([
                'user_id' => $userId,
                'subject_id' => $request->subject_id,
                'document_id' => $request->document_id,
                'front' => $data['front'],
                'back' => $data['back'],
                'difficulty' => $request->difficulty ?? 'medium',
            ]);
            $created++;
        }

        $this->notificationService->notifyQuizGenerated($userId, 'Flashcard', $created);

        return redirect()->route('flashcards.index')->with('success', $created.' flashcards generated successfully!');
    }

    public function edit(Flashcard $flashcard)
    {
        if ($flashcard->user_id !== auth()->id()) {
            abort(403);
        }
        $subjects = auth()->user()->subjects;

        return view('Backend.flashcards.edit', compact('flashcard', 'subjects'));
    }

    public function update(Request $request, Flashcard $flashcard)
    {
        if ($flashcard->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'front' => 'required|string',
            'back' => 'required|string',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $flashcard->update([
            'front' => $request->front,
            'back' => $request->back,
            'difficulty' => $request->difficulty ?? 'medium',
            'subject_id' => $request->subject_id,
        ]);

        $this->notificationService->notifyQuestionUpdated(auth()->id(), 'Flashcard');

        return redirect()->route('flashcards.index')->with('success', 'Flashcard updated successfully.');
    }

    public function destroy(Flashcard $flashcard)
    {
        if ($flashcard->user_id !== auth()->id()) {
            abort(403);
        }
        SpacedRepetition::where('user_id', auth()->id())
            ->where('reviewable_type', Flashcard::class)
            ->where('reviewable_id', $flashcard->id)
            ->delete();
        $flashcard->delete();

        $this->notificationService->notifyQuestionDeleted(auth()->id(), 'Flashcard');

        return back()->with('success', 'Flashcard deleted.');
    }

    public function destroyAll()
    {
        $userId = auth()->id();

        SpacedRepetition::where('user_id', $userId)
            ->where('reviewable_type', Flashcard::class)
            ->whereIn('reviewable_id', Flashcard::where('user_id', $userId)->pluck('id'))
            ->delete();

        Flashcard::where('user_id', $userId)->delete();

        $this->notificationService->notifyAllQuestionsDeleted(auth()->id(), 'Flashcard');

        return back()->with('success', 'All flashcards deleted.');
    }
}
