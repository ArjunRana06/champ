<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use App\Models\Subject;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $entries = TimeEntry::where('user_id', $userId)->with('subject')->latest()->paginate(20);
        $todayMinutes = TimeEntry::where('user_id', $userId)
            ->whereDate('started_at', today())
            ->sum('duration_minutes');
        $weekMinutes = TimeEntry::where('user_id', $userId)
            ->whereBetween('started_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('duration_minutes');
        $subjects = Subject::where('user_id', $userId)->get();
        return view('Backend.time-entries.index', compact('entries', 'todayMinutes', 'weekMinutes', 'subjects'));
    }

    public function start(Request $request)
    {
        $request->validate([
            'subject_id' => 'nullable|exists:subjects,id',
            'description' => 'nullable|string|max:255',
        ]);

        // Stop any active timer
        TimeEntry::where('user_id', auth()->id())->whereNull('ended_at')->update([
            'ended_at' => now(),
            'duration_minutes' => 0,
        ]);

        TimeEntry::create([
            'user_id' => auth()->id(),
            'subject_id' => $request->subject_id,
            'description' => $request->description,
            'started_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function stop()
    {
        $entry = TimeEntry::where('user_id', auth()->id())->whereNull('ended_at')->latest()->first();
        if ($entry) {
            $entry->update([
                'ended_at' => now(),
                'duration_minutes' => now()->diffInMinutes($entry->started_at),
            ]);
        }
        return response()->json(['success' => true]);
    }

    public function destroy(TimeEntry $timeEntry)
    {
        if ($timeEntry->user_id !== auth()->id()) abort(403);
        $timeEntry->delete();
        return back()->with('success', 'Entry deleted.');
    }
}
