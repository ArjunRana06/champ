<?php

namespace App\Http\Controllers;

use App\Models\FillBlank;
use App\Services\QuestionGeneratorService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class FillBlankController extends Controller
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
        $questions = FillBlank::where('user_id', auth()->id())->latest()->paginate(20);
        return view('Backend.fill-blanks.index', compact('questions'));
    }

    public function create()
    {
        $subjects = auth()->user()->subjects;
        return view('Backend.fill-blanks.create', compact('subjects'));
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

        $questions = $this->generatorService->generateFillBlanks($chunks, $request->count, $request->difficulty ?? 'medium');

        foreach ($questions as $data) {
            FillBlank::create([
                'user_id' => $userId,
                'subject_id' => $request->subject_id,
                'document_id' => $request->document_id,
                'sentence_with_blanks' => $data['sentence_with_blanks'],
                'answers' => $data['answers'],
                'difficulty' => $request->difficulty ?? 'medium',
            ]);
        }

        $this->notificationService->notifyQuizGenerated($userId, 'Fill-in-the-Blank', $request->count);

        return redirect()->route('fill-blanks.index')->with('success', $request->count . ' fill-in-the-blank questions generated successfully!');
    }

    public function edit(FillBlank $fillBlank)
    {
        if ($fillBlank->user_id !== auth()->id()) abort(403);
        $subjects = auth()->user()->subjects;
        return view('Backend.fill-blanks.edit', compact('fillBlank', 'subjects'));
    }

    public function update(Request $request, FillBlank $fillBlank)
    {
        if ($fillBlank->user_id !== auth()->id()) abort(403);

        $request->validate([
            'sentence_with_blanks' => 'required|string',
            'answers' => 'required|array',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $fillBlank->update([
            'sentence_with_blanks' => $request->sentence_with_blanks,
            'answers' => $request->answers,
            'difficulty' => $request->difficulty ?? 'medium',
            'subject_id' => $request->subject_id,
        ]);

        $this->notificationService->notifyQuestionUpdated(auth()->id(), 'Fill-in-the-Blank');

        return redirect()->route('fill-blanks.index')->with('success', 'Fill-in-the-blank question updated successfully.');
    }

    public function destroy(FillBlank $fillBlank)
    {
        if ($fillBlank->user_id !== auth()->id()) abort(403);
        $fillBlank->delete();

        $this->notificationService->notifyQuestionDeleted(auth()->id(), 'Fill-in-the-Blank');

        return back()->with('success', 'Question deleted.');
    }
}
