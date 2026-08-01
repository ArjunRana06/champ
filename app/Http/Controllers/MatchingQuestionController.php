<?php

namespace App\Http\Controllers;

use App\Models\MatchingQuestion;
use App\Services\QuestionGeneratorService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MatchingQuestionController extends Controller
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
        $questions = MatchingQuestion::where('user_id', auth()->id())->latest()->paginate(20);
        return view('Backend.matching.index', compact('questions'));
    }

    public function create()
    {
        $subjects = auth()->user()->subjects;
        return view('Backend.matching.create', compact('subjects'));
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

        $questions = $this->generatorService->generateMatchingQuestions($chunks, $request->count, $request->difficulty ?? 'medium');

        foreach ($questions as $data) {
            MatchingQuestion::create([
                'user_id' => $userId,
                'subject_id' => $request->subject_id,
                'document_id' => $request->document_id,
                'left_items' => $data['left_items'],
                'right_items' => $data['right_items'],
                'correct_pairs' => $data['correct_pairs'],
                'difficulty' => $request->difficulty ?? 'medium',
            ]);
        }

        $this->notificationService->notifyQuizGenerated($userId, 'Matching', $request->count);

        return redirect()->route('matching.index')->with('success', $request->count . ' matching questions generated successfully!');
    }

    public function edit(MatchingQuestion $matching)
    {
        if ($matching->user_id !== auth()->id()) abort(403);
        $subjects = auth()->user()->subjects;
        return view('Backend.matching.edit', compact('matching', 'subjects'));
    }

    public function update(Request $request, MatchingQuestion $matching)
    {
        if ($matching->user_id !== auth()->id()) abort(403);

        $request->validate([
            'left_items' => 'required|array',
            'right_items' => 'required|array',
            'correct_pairs' => 'required|array',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $matching->update([
            'left_items' => $request->left_items,
            'right_items' => $request->right_items,
            'correct_pairs' => $request->correct_pairs,
            'difficulty' => $request->difficulty ?? 'medium',
            'subject_id' => $request->subject_id,
        ]);

        $this->notificationService->notifyQuestionUpdated(auth()->id(), 'Matching');

        return redirect()->route('matching.index')->with('success', 'Matching question updated successfully.');
    }

    public function destroy(MatchingQuestion $matching)
    {
        if ($matching->user_id !== auth()->id()) abort(403);
        $matching->delete();

        $this->notificationService->notifyQuestionDeleted(auth()->id(), 'Matching');

        return back()->with('success', 'Question deleted.');
    }
}
