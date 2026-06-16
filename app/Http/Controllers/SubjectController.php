<?php
// app/Http/Controllers/SubjectController.php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{

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

        Auth::user()->subjects()->create($request->only(['name', 'semester', 'code']));

        return redirect()->route('subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    public function edit(Subject $subject)
    {
        // Ensure the subject belongs to the authenticated user
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

        $subject->update($request->only(['name', 'semester', 'code']));

        return redirect()->route('subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        if ($subject->user_id !== Auth::id()) {
            abort(403);
        }

        $subject->delete();

        return redirect()->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}
