<?php

namespace App\Http\Controllers;

use App\Models\ShortAnswer;
use App\Services\QuestionGeneratorService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ShortAnswerController extends Controller
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
        $questions = ShortAnswer::where('user_id', auth()->id())->latest()->paginate(20);
        return view('Backend.short-answers.index', compact('questions'));
    }

    public function create()
    {
        $subjects = auth()->user()->subjects;
        return view('Backend.short-answers.create', compact('subjects'));
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

        $questions = $this->generatorService->generateShortAnswers($chunks, $request->count, $request->difficulty ?? 'medium');

        foreach ($questions as $data) {
            ShortAnswer::create([
                'user_id' => $userId,
                'subject_id' => $request->subject_id,
                'document_id' => $request->document_id,
                'question' => $data['question'],
                'expected_answer' => $data['expected_answer'],
                'difficulty' => $request->difficulty ?? 'medium',
            ]);
        }

        $this->notificationService->notifyQuizGenerated($userId, 'Short Answer', $request->count);

        return redirect()->route('short-answers.index')->with('success', $request->count . ' short answer questions generated successfully!');
    }

    public function edit(ShortAnswer $shortAnswer)
    {
        if ($shortAnswer->user_id !== auth()->id()) abort(403);
        $subjects = auth()->user()->subjects;
        return view('Backend.short-answers.edit', compact('shortAnswer', 'subjects'));
    }

    public function update(Request $request, ShortAnswer $shortAnswer)
    {
        if ($shortAnswer->user_id !== auth()->id()) abort(403);

        $request->validate([
            'question' => 'required|string',
            'expected_answer' => 'required|string',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $shortAnswer->update([
            'question' => $request->question,
            'expected_answer' => $request->expected_answer,
            'difficulty' => $request->difficulty ?? 'medium',
            'subject_id' => $request->subject_id,
        ]);

        $this->notificationService->notifyQuestionUpdated(auth()->id(), 'Short Answer');

        return redirect()->route('short-answers.index')->with('success', 'Short answer question updated successfully.');
    }

    public function destroy(ShortAnswer $shortAnswer)
    {
        if ($shortAnswer->user_id !== auth()->id()) abort(403);
        $shortAnswer->delete();

        $this->notificationService->notifyQuestionDeleted(auth()->id(), 'Short Answer');

        return back()->with('success', 'Question deleted.');
    }
}
