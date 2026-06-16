<?php

namespace App\Http\Controllers;

use App\Models\TrueFalseQuestion;
use App\Models\Subject;
use App\Services\QuestionGeneratorService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class TrueFalseController extends Controller
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
        $questions = TrueFalseQuestion::where('user_id', auth()->id())->latest()->paginate(20);
        return view('Backend.true-false.index', compact('questions'));
    }

    public function create()
    {
        $subjects = auth()->user()->subjects;
        return view('Backend.true-false.create', compact('subjects'));
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

        $questions = $this->generatorService->generateTrueFalse($chunks, $request->count, $request->difficulty ?? 'medium');

        foreach ($questions as $data) {
            TrueFalseQuestion::create([
                'user_id' => $userId,
                'subject_id' => $request->subject_id,
                'document_id' => $request->document_id,
                'statement' => $data['statement'],
                'correct_answer' => $data['correct_answer'],
                'explanation' => $data['explanation'] ?? null,
                'difficulty' => $request->difficulty ?? 'medium',
            ]);
        }

        $this->notificationService->notifyQuizGenerated($userId, 'True/False', $request->count);

        return redirect()->route('true-false.index')->with('success', $request->count . ' True/False questions generated successfully!');
    }

    public function edit(TrueFalseQuestion $trueFalse)
    {
        if ($trueFalse->user_id !== auth()->id()) abort(403);
        $subjects = auth()->user()->subjects;
        return view('Backend.true-false.edit', compact('trueFalse', 'subjects'));
    }

    public function update(Request $request, TrueFalseQuestion $trueFalse)
    {
        if ($trueFalse->user_id !== auth()->id()) abort(403);

        $request->validate([
            'statement' => 'required|string',
            'correct_answer' => 'required|boolean',
            'explanation' => 'nullable|string',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $trueFalse->update([
            'statement' => $request->statement,
            'correct_answer' => $request->correct_answer,
            'explanation' => $request->explanation,
            'difficulty' => $request->difficulty ?? 'medium',
            'subject_id' => $request->subject_id,
        ]);

        return redirect()->route('true-false.index')->with('success', 'True/False question updated successfully.');
    }

    public function destroy(TrueFalseQuestion $trueFalse)
    {
        if ($trueFalse->user_id !== auth()->id()) abort(403);
        $trueFalse->delete();
        return back()->with('success', 'Question deleted.');
    }
}
