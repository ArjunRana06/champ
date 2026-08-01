<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $exams = Exam::where('user_id', auth()->id())
            ->with('subject')
            ->orderBy('exam_date')
            ->get();
        return view('Backend.exams.index', compact('exams'));
    }

    public function create()
    {
        $subjects = auth()->user()->subjects;
        return view('Backend.exams.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'nullable|exists:subjects,id',
            'exam_date' => 'required|date',
            'time' => 'nullable|string|max:10',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'priority' => 'nullable|integer|min:1|max:5',
        ]);

        $exam = Exam::create(array_merge(
            $request->only(['title', 'subject_id', 'exam_date', 'time', 'location', 'notes', 'priority']),
            ['user_id' => auth()->id()]
        ));

        $this->notificationService->notifyExamAdded(auth()->id(), $exam->title);

        return redirect()->route('exams.index')->with('success', 'Exam added to your calendar.');
    }

    public function update(Request $request, Exam $exam)
    {
        if ($exam->user_id !== auth()->id()) abort(403);

        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'nullable|exists:subjects,id',
            'exam_date' => 'required|date',
            'time' => 'nullable|string|max:10',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'priority' => 'nullable|integer|min:1|max:5',
            'is_completed' => 'nullable|boolean',
        ]);

        $oldTitle = $exam->title;
        $exam->update($request->only(['title', 'subject_id', 'exam_date', 'time', 'location', 'notes', 'priority', 'is_completed']));

        $this->notificationService->notifyExamUpdated(auth()->id(), $oldTitle);

        return back()->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam)
    {
        if ($exam->user_id !== auth()->id()) abort(403);
        $title = $exam->title;
        $exam->delete();

        $this->notificationService->notifyExamDeleted(auth()->id(), $title);

        return back()->with('success', 'Exam deleted.');
    }
}
