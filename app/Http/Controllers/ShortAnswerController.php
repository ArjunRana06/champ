<?php

namespace App\Http\Controllers;

use App\Models\ShortAnswer;
use App\Services\NotificationService;
use App\Services\QuestionDeduplicator;
use App\Services\QuestionGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            $questions = $this->generatorService->generateShortAnswers($chunks, $request->count, $request->difficulty ?? 'medium', $promptExisting);
            $questions = $this->generatorService->validateGenerated('short_answers', $questions);
            $questions = app(QuestionDeduplicator::class)->filterDuplicatesSemantic($questions, $existingTexts, 'question');
        } catch (\Exception $e) {
            return back()->with('error', 'AI generation failed: '.$e->getMessage());
        }

        if (empty($questions)) {
            return back()->with('error', 'The AI did not generate new valid short answer questions — everything it produced was a duplicate of your existing questions. Please try again.');
        }

        $created = 0;
        foreach ($questions as $data) {
            ShortAnswer::create([
                'user_id' => $userId,
                'subject_id' => $request->subject_id,
                'document_id' => $request->document_id,
                'question' => $data['question'],
                'expected_answer' => $data['expected_answer'],
                'difficulty' => $request->difficulty ?? 'medium',
            ]);
            $created++;
        }

        $this->notificationService->notifyQuizGenerated($userId, 'Short Answer', $created);

        return redirect()->route('short-answers.index')->with('success', $created.' short answer questions generated successfully!');
    }

    public function edit(ShortAnswer $shortAnswer)
    {
        if ($shortAnswer->user_id !== auth()->id()) {
            abort(403);
        }
        $subjects = auth()->user()->subjects;

        return view('Backend.short-answers.edit', compact('shortAnswer', 'subjects'));
    }

    public function update(Request $request, ShortAnswer $shortAnswer)
    {
        if ($shortAnswer->user_id !== auth()->id()) {
            abort(403);
        }

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
        if ($shortAnswer->user_id !== auth()->id()) {
            abort(403);
        }
        $shortAnswer->delete();

        $this->notificationService->notifyQuestionDeleted(auth()->id(), 'Short Answer');

        return back()->with('success', 'Question deleted.');
    }

    public function destroyAll()
    {
        ShortAnswer::where('user_id', auth()->id())->delete();

        $this->notificationService->notifyAllQuestionsDeleted(auth()->id(), 'Short Answer');

        return back()->with('success', 'All short answer questions deleted.');
    }
}
