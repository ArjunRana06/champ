<?php

namespace App\Http\Controllers;

use App\Models\TrueFalseQuestion;
use App\Services\NotificationService;
use App\Services\QuestionDeduplicator;
use App\Services\QuestionGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            $questions = $this->generatorService->generateTrueFalse($chunks, $request->count, $request->difficulty ?? 'medium', $promptExisting);
            $questions = $this->generatorService->validateGenerated('true_false', $questions);
            $questions = app(QuestionDeduplicator::class)->filterDuplicatesSemantic($questions, $existingTexts, 'statement');
        } catch (\Exception $e) {
            return back()->with('error', 'AI generation failed: '.$e->getMessage());
        }

        if (empty($questions)) {
            return back()->with('error', 'The AI did not generate new valid True/False questions — everything it produced was a duplicate of your existing questions. Please try again.');
        }

        $created = 0;
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
            $created++;
        }

        $this->notificationService->notifyQuizGenerated($userId, 'True/False', $created);

        return redirect()->route('true-false.index')->with('success', $created.' True/False questions generated successfully!');
    }

    public function edit(TrueFalseQuestion $trueFalse)
    {
        if ($trueFalse->user_id !== auth()->id()) {
            abort(403);
        }
        $subjects = auth()->user()->subjects;

        return view('Backend.true-false.edit', compact('trueFalse', 'subjects'));
    }

    public function update(Request $request, TrueFalseQuestion $trueFalse)
    {
        if ($trueFalse->user_id !== auth()->id()) {
            abort(403);
        }

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

        $this->notificationService->notifyQuestionUpdated(auth()->id(), 'True/False');

        return redirect()->route('true-false.index')->with('success', 'True/False question updated successfully.');
    }

    public function destroy(TrueFalseQuestion $trueFalse)
    {
        if ($trueFalse->user_id !== auth()->id()) {
            abort(403);
        }
        $trueFalse->delete();

        $this->notificationService->notifyQuestionDeleted(auth()->id(), 'True/False');

        return back()->with('success', 'Question deleted.');
    }

    public function destroyAll()
    {
        TrueFalseQuestion::where('user_id', auth()->id())->delete();

        $this->notificationService->notifyAllQuestionsDeleted(auth()->id(), 'True/False');

        return back()->with('success', 'All True/False questions deleted.');
    }
}
