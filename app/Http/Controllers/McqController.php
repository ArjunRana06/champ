<?php

namespace App\Http\Controllers;

use App\Models\Mcq;
use App\Models\Subject;
use App\Models\Document;
use App\Services\McqGeneratorService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

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
        // dd('MCQ Index');
        $mcqs = Mcq::where('user_id', auth()->id())->latest()->paginate(20);
        return view('Backend.mcq.index', compact('mcqs'));
    }

    public function create()
    {
        // dd('MCQ Create');
        $subjects = auth()->user()->subjects;
        return view('Backend.mcq.create', compact('subjects'));
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

        // Get relevant chunks based on subject/document
        $chunks = $this->mcqService->getRelevantChunks(
            $request->subject_id,
            $request->document_id,
            $request->topic,
            $request->count * 2 // fetch extra chunks for better coverage
        );

        if (empty($chunks)) {
            return back()->with('error', 'No relevant content found in your uploaded materials for this subject/topic.');
        }

        // Generate MCQs using AI
        $mcqs = $this->mcqService->generateMcqs($chunks, $request->count, $request->difficulty ?? 'medium');

        // Save to database
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
        }

        $this->notificationService->notifyQuizGenerated($userId, 'MCQ', $request->count);

        return redirect()->route('mcqs.index')->with('success', $request->count . ' MCQs generated successfully!');
    }

    public function edit(Mcq $mcq)
    {
        if ($mcq->user_id !== auth()->id()) abort(403);
        $subjects = auth()->user()->subjects;
        return view('Backend.mcq.edit', compact('mcq', 'subjects'));
    }

    public function update(Request $request, Mcq $mcq)
    {
        if ($mcq->user_id !== auth()->id()) abort(403);

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

        return redirect()->route('mcqs.index')->with('success', 'MCQ updated successfully.');
    }

    public function destroy(Mcq $mcq)
    {
        if ($mcq->user_id !== auth()->id()) abort(403);
        $mcq->delete();
        return back()->with('success', 'MCQ deleted.');
    }
}
