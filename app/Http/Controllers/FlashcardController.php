<?php

namespace App\Http\Controllers;

use App\Models\Flashcard;
use App\Services\QuestionGeneratorService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class FlashcardController extends Controller
{
    protected $generatorService;
    protected $notificationService;

    public function __construct(QuestionGeneratorService $generatorService, NotificationService $notificationService)
    {
        $this->generatorService = $generatorService;
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $flashcards = Flashcard::where('user_id', auth()->id())->latest()->paginate(20);
        return view('Backend.flashcards.index', compact('flashcards'));
    }

    public function create()
    {
        $subjects = auth()->user()->subjects;
        return view('Backend.flashcards.create', compact('subjects'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'subject_id' => 'nullable|exists:subjects,id',
            'document_id' => 'nullable|exists:documents,id',
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

        $flashcards = $this->generatorService->generateFlashcards($chunks, $request->count, $request->difficulty ?? 'medium');

        foreach ($flashcards as $data) {
            Flashcard::create([
                'user_id' => $userId,
                'subject_id' => $request->subject_id,
                'document_id' => $request->document_id,
                'front' => $data['front'],
                'back' => $data['back'],
                'difficulty' => $request->difficulty ?? 'medium',
            ]);
        }

        $this->notificationService->notifyQuizGenerated($userId, 'Flashcard', $request->count);

        return redirect()->route('flashcards.index')->with('success', $request->count . ' flashcards generated successfully!');
    }

    public function edit(Flashcard $flashcard)
    {
        if ($flashcard->user_id !== auth()->id()) abort(403);
        $subjects = auth()->user()->subjects;
        return view('Backend.flashcards.edit', compact('flashcard', 'subjects'));
    }

    public function update(Request $request, Flashcard $flashcard)
    {
        if ($flashcard->user_id !== auth()->id()) abort(403);

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
        if ($flashcard->user_id !== auth()->id()) abort(403);
        $flashcard->delete();

        $this->notificationService->notifyQuestionDeleted(auth()->id(), 'Flashcard');

        return back()->with('success', 'Flashcard deleted.');
    }
}
