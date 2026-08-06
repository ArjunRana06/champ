<?php

namespace App\Http\Controllers;

use App\Models\PomodoroSession;
use App\Models\Subject;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Services\GamificationService;

class PomodoroController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $userId = auth()->id();
        $sessions = PomodoroSession::where('user_id', $userId)->with('subject')->latest()->paginate(20);
        $todayCount = PomodoroSession::where('user_id', $userId)->whereDate('created_at', today())->count();
        $todayMinutes = PomodoroSession::where('user_id', $userId)->whereDate('created_at', today())->sum('duration_minutes');
        $weekMinutes = PomodoroSession::where('user_id', $userId)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('duration_minutes');
        $totalMinutes = PomodoroSession::where('user_id', $userId)->sum('duration_minutes');

        $weekDaily = collect();
        $start = now()->startOfWeek();
        for ($i = 0; $i < 7; $i++) {
            $day = $start->copy()->addDays($i);
            $weekDaily->push([
                'label' => $day->format('D'),
                'minutes' => PomodoroSession::where('user_id', $userId)->whereDate('created_at', $day)->sum('duration_minutes'),
            ]);
        }

        $subjects = auth()->user()->subjects;
        return view('Backend.pomodoro.index', compact(
            'sessions', 'todayCount', 'todayMinutes', 'weekMinutes', 'totalMinutes', 'weekDaily', 'subjects'
        ));
    }

    public function complete(Request $request)
    {
        $request->validate([
            'duration_minutes' => 'required|integer|min:1',
            'break_minutes' => 'nullable|integer|min:0',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        PomodoroSession::create([
            'user_id' => auth()->id(),
            'subject_id' => $request->subject_id,
            'duration_minutes' => $request->duration_minutes,
            'break_minutes' => $request->break_minutes ?? 5,
            'status' => 'completed',
        ]);

        app(GamificationService::class)->awardXp(auth()->user(), 10);

        $this->notificationService->notifyPomodoroCompleted(auth()->id(), $request->duration_minutes, 10);

        return response()->json(['success' => true]);
    }
}
