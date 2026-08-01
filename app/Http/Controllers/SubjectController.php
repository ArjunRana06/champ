<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $subjects = Auth::user()->subjects()->latest()->get();
        return view('Backend.Subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('Backend.Subjects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'semester' => 'nullable|string|max:50',
            'code' => 'nullable|string|max:50',
        ]);

        $subject = Auth::user()->subjects()->create($request->only(['name', 'semester', 'code']));

        $this->notificationService->notifySubjectCreated(Auth::id(), $subject->name);

        return redirect()->route('subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    public function show(Subject $subject)
    {
        if ($subject->user_id !== Auth::id()) abort(403);
        return view('Backend.Subjects.edit', compact('subject'));
    }

    public function edit(Subject $subject)
    {
        if ($subject->user_id !== Auth::id()) {
            abort(403);
        }
        return view('Backend.Subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        if ($subject->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'semester' => 'nullable|string|max:50',
            'code' => 'nullable|string|max:50',
        ]);

        $oldName = $subject->name;
        $subject->update($request->only(['name', 'semester', 'code']));

        $this->notificationService->notifySubjectUpdated(Auth::id(), $oldName);

        return redirect()->route('subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        if ($subject->user_id !== Auth::id()) {
            abort(403);
        }

        $name = $subject->name;
        $subject->delete();

        $this->notificationService->notifySubjectDeleted(Auth::id(), $name);

        return redirect()->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}
