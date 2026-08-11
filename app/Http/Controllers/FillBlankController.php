<?php

namespace App\Http\Controllers;

use App\Models\FillBlank;
use App\Services\NotificationService;
use App\Services\QuestionDeduplicator;
use App\Services\QuestionGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            $questions = $this->generatorService->generateFillBlanks($chunks, $request->count, $request->difficulty ?? 'medium', $promptExisting);
            $questions = $this->generatorService->validateGenerated('fill_blanks', $questions);
            $questions = app(QuestionDeduplicator::class)->filterDuplicatesSemantic($questions, $existingTexts, 'sentence_with_blanks');
        } catch (\Exception $e) {
            return back()->with('error', 'AI generation failed: '.$e->getMessage());
        }

        if (empty($questions)) {
            return back()->with('error', 'The AI did not generate new valid fill-in-the-blank questions — everything it produced was a duplicate of your existing questions. Please try again.');
        }

        $created = 0;
        foreach ($questions as $data) {
            FillBlank::create([
                'user_id' => $userId,
                'subject_id' => $request->subject_id,
                'document_id' => $request->document_id,
                'sentence_with_blanks' => $data['sentence_with_blanks'],
                'answers' => $data['answers'],
                'difficulty' => $request->difficulty ?? 'medium',
            ]);
            $created++;
        }

        $this->notificationService->notifyQuizGenerated($userId, 'Fill-in-the-Blank', $created);

        return redirect()->route('fill-blanks.index')->with('success', $created.' fill-in-the-blank questions generated successfully!');
    }

    public function edit(FillBlank $fillBlank)
    {
        if ($fillBlank->user_id !== auth()->id()) {
            abort(403);
        }
        $subjects = auth()->user()->subjects;

        return view('Backend.fill-blanks.edit', compact('fillBlank', 'subjects'));
    }

    public function update(Request $request, FillBlank $fillBlank)
    {
        if ($fillBlank->user_id !== auth()->id()) {
            abort(403);
        }

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
        if ($fillBlank->user_id !== auth()->id()) {
            abort(403);
        }
        $fillBlank->delete();

        $this->notificationService->notifyQuestionDeleted(auth()->id(), 'Fill-in-the-Blank');

        return back()->with('success', 'Question deleted.');
    }

    public function destroyAll()
    {
        FillBlank::where('user_id', auth()->id())->delete();

        $this->notificationService->notifyAllQuestionsDeleted(auth()->id(), 'Fill-in-the-Blank');

        return back()->with('success', 'All fill-in-the-blank questions deleted.');
    }
}
