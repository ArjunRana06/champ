<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use App\Models\Subject;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $userId = auth()->id();

        $query = TimeEntry::where('user_id', $userId)->with('subject');

        if ($request->filled('subject')) {
            $query->where('subject_id', $request->subject);
        }

        $period = $request->get('period', 'all');
        if ($period === 'today') {
            $query->whereDate('started_at', today());
        } elseif ($period === 'week') {
            $query->whereBetween('started_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereBetween('started_at', [now()->startOfMonth(), now()->endOfMonth()]);
        }

        $entries = $query->latest()->paginate(20)->withQueryString();

        $todayMinutes = TimeEntry::where('user_id', $userId)->whereDate('started_at', today())->sum('duration_minutes');
        $weekMinutes = TimeEntry::where('user_id', $userId)
            ->whereBetween('started_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('duration_minutes');
        $monthMinutes = TimeEntry::where('user_id', $userId)
            ->whereBetween('started_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('duration_minutes');
        $totalMinutes = TimeEntry::where('user_id', $userId)->sum('duration_minutes');
        $totalSessions = TimeEntry::where('user_id', $userId)->whereNotNull('ended_at')->count();

        $activeEntry = TimeEntry::where('user_id', $userId)->whereNull('ended_at')->with('subject')->latest()->first();

        $weekDaily = collect();
        $start = now()->startOfWeek();
        for ($i = 0; $i < 7; $i++) {
            $day = $start->copy()->addDays($i);
            $weekDaily->push([
                'label' => $day->format('D'),
                'minutes' => TimeEntry::where('user_id', $userId)->whereDate('started_at', $day)->sum('duration_minutes'),
            ]);
        }

        $subjectStats = TimeEntry::where('user_id', $userId)
            ->whereNotNull('ended_at')
            ->whereHas('subject')
            ->with('subject')
            ->get()
            ->groupBy(fn($e) => $e->subject_id)
            ->map(fn($group) => [
                'name' => $group->first()->subject->name,
                'minutes' => $group->sum('duration_minutes'),
            ])
            ->filter(fn($g) => $g['minutes'] > 0)
            ->sortByDesc('minutes')
            ->values()
            ->all();

        $subjects = Subject::where('user_id', $userId)->get();
        return view('Backend.time-entries.index', compact(
            'entries', 'todayMinutes', 'weekMinutes', 'monthMinutes', 'totalMinutes', 'totalSessions',
            'activeEntry', 'weekDaily', 'subjectStats', 'subjects'
        ));
    }

    public function start(Request $request)
    {
        $request->validate([
            'subject_id' => 'nullable|exists:subjects,id',
            'description' => 'nullable|string|max:255',
        ]);

        $staleEntries = TimeEntry::where('user_id', auth()->id())->whereNull('ended_at')->get();
        foreach ($staleEntries as $entry) {
            $entry->update([
                'ended_at' => now(),
                'duration_minutes' => max(1, now()->diffInMinutes($entry->started_at)),
            ]);
        }

        TimeEntry::create([
            'user_id' => auth()->id(),
            'subject_id' => $request->subject_id,
            'description' => $request->description,
            'started_at' => now(),
        ]);

        $this->notificationService->notifyTimerStarted(auth()->id());

        return response()->json(['success' => true]);
    }

    public function stop()
    {
        $entry = TimeEntry::where('user_id', auth()->id())->whereNull('ended_at')->latest()->first();
        $minutes = 0;
        if ($entry) {
            $minutes = now()->diffInMinutes($entry->started_at);
            $entry->update([
                'ended_at' => now(),
                'duration_minutes' => $minutes,
            ]);
        }

        $this->notificationService->notifyTimerStopped(auth()->id(), max(1, $minutes));

        return response()->json(['success' => true]);
    }

    public function destroy(TimeEntry $timeEntry)
    {
        if ($timeEntry->user_id !== auth()->id()) abort(403);
        $timeEntry->delete();

        $this->notificationService->notifyTimeEntryDeleted(auth()->id());

        return back()->with('success', 'Entry deleted.');
    }
}
