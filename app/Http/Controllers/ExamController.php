<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Subject;
use Illuminate\Http\Request;

class ExamController extends Controller
{
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

        Exam::create(array_merge($request->all(), ['user_id' => auth()->id()]));

        return redirect()->route('exams.index')->with('success', 'Exam added to your calendar.');
    }

    public function update(Request $request, Exam $exam)
    {
        if ($exam->user_id !== auth()->id()) abort(403);
        $exam->update($request->only(['title', 'exam_date', 'time', 'location', 'notes', 'priority', 'is_completed']));
        return back()->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam)
    {
        if ($exam->user_id !== auth()->id()) abort(403);
        $exam->delete();
        return back()->with('success', 'Exam deleted.');
    }
}
