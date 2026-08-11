<?php

namespace App\Http\Controllers;

use App\Models\MatchingQuestion;
use App\Services\NotificationService;
use App\Services\QuestionDeduplicator;
use App\Services\QuestionGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        $dedupField = fn ($item) => implode('|', $item['left_items'] ?? []).'|'.implode('|', $item['right_items'] ?? []);

        try {
            $questions = $this->generatorService->generateMatchingQuestions($chunks, $request->count, $request->difficulty ?? 'medium', $promptExisting);
            $questions = $this->generatorService->validateGenerated('matching', $questions);
            $questions = app(QuestionDeduplicator::class)->filterDuplicatesSemantic($questions, $existingTexts, $dedupField);
        } catch (\Exception $e) {
            return back()->with('error', 'AI generation failed: '.$e->getMessage());
        }

        if (empty($questions)) {
            return back()->with('error', 'The AI did not generate new valid matching questions — everything it produced was a duplicate of your existing questions. Please try again.');
        }

        $created = 0;
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
            $created++;
        }

        $this->notificationService->notifyQuizGenerated($userId, 'Matching', $created);

        return redirect()->route('matching.index')->with('success', $created.' matching questions generated successfully!');
    }

    public function edit(MatchingQuestion $matching)
    {
        if ($matching->user_id !== auth()->id()) {
            abort(403);
        }
        $subjects = auth()->user()->subjects;

        return view('Backend.matching.edit', compact('matching', 'subjects'));
    }

    public function update(Request $request, MatchingQuestion $matching)
    {
        if ($matching->user_id !== auth()->id()) {
            abort(403);
        }

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
        if ($matching->user_id !== auth()->id()) {
            abort(403);
        }
        $matching->delete();

        $this->notificationService->notifyQuestionDeleted(auth()->id(), 'Matching');

        return back()->with('success', 'Question deleted.');
    }

    public function destroyAll()
    {
        MatchingQuestion::where('user_id', auth()->id())->delete();

        $this->notificationService->notifyAllQuestionsDeleted(auth()->id(), 'Matching');

        return back()->with('success', 'All matching questions deleted.');
    }
}
