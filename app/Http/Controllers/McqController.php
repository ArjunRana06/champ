<?php

namespace App\Http\Controllers;

use App\Models\Mcq;
use App\Services\McqGeneratorService;
use App\Services\NotificationService;
use App\Services\QuestionDeduplicator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class McqController extends Controller
{
    protected $mcqService;

    protected $notificationService;

    public function __construct(McqGeneratorService $mcqService, NotificationService $notificationService)
    {
        $this->mcqService = $mcqService;
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $mcqs = Mcq::where('user_id', auth()->id())->latest()->paginate(20);

        return view('Backend.mcq.index', compact('mcqs'));
    }

    public function create()
    {
        $subjects = auth()->user()->subjects;

        return view('Backend.mcq.create', compact('subjects'));
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

        $chunks = $this->mcqService->getRelevantChunks(
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
            $mcqs = $this->mcqService->generateMcqs($chunks, $request->count, $request->difficulty ?? 'medium', $promptExisting);
            $mcqs = $this->mcqService->validateGenerated($mcqs);
            $mcqs = app(QuestionDeduplicator::class)->filterDuplicatesSemantic($mcqs, $existingTexts, 'question');
        } catch (\Exception $e) {
            return back()->with('error', 'AI generation failed: '.$e->getMessage());
        }

        if (empty($mcqs)) {
            return back()->with('error', 'The AI did not generate new valid MCQs — everything it produced was a duplicate of your existing questions. Please try again.');
        }

        $created = 0;
        foreach ($mcqs as $mcqData) {
            Mcq::create([
                'user_id' => $userId,
                'subject_id' => $request->subject_id,
                'document_id' => $request->document_id,
                'question' => $mcqData['question'],
                'options' => $mcqData['options'],
                'correct_answer' => $mcqData['correct_answer'],
                'explanation' => $mcqData['explanation'] ?? null,
                'difficulty' => $request->difficulty ?? 'medium',
            ]);
            $created++;
        }

        $this->notificationService->notifyQuizGenerated($userId, 'MCQ', $created);

        return redirect()->route('mcqs.index')->with('success', $created.' MCQs generated successfully!');
    }

    public function edit(Mcq $mcq)
    {
        if ($mcq->user_id !== auth()->id()) {
            abort(403);
        }
        $subjects = auth()->user()->subjects;

        return view('Backend.mcq.edit', compact('mcq', 'subjects'));
    }

    public function update(Request $request, Mcq $mcq)
    {
        if ($mcq->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'question' => 'required|string',
            'options' => 'required|array|min:2',
            'correct_answer' => 'required|string',
            'explanation' => 'nullable|string',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $mcq->update([
            'question' => $request->question,
            'options' => $request->options,
            'correct_answer' => $request->correct_answer,
            'explanation' => $request->explanation,
            'difficulty' => $request->difficulty ?? 'medium',
            'subject_id' => $request->subject_id,
        ]);

        $this->notificationService->notifyQuestionUpdated(auth()->id(), 'MCQ');

        return redirect()->route('mcqs.index')->with('success', 'MCQ updated successfully.');
    }

    public function destroy(Mcq $mcq)
    {
        if ($mcq->user_id !== auth()->id()) {
            abort(403);
        }
        $mcq->delete();

        $this->notificationService->notifyQuestionDeleted(auth()->id(), 'MCQ');

        return back()->with('success', 'MCQ deleted.');
    }

    public function destroyAll()
    {
        Mcq::where('user_id', auth()->id())->delete();

        $this->notificationService->notifyAllQuestionsDeleted(auth()->id(), 'MCQ');

        return back()->with('success', 'All MCQs deleted.');
    }
}
