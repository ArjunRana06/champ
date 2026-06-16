<?php

namespace App\Http\Controllers;

use App\Models\PomodoroSession;
use App\Models\Subject;
use Illuminate\Http\Request;
use App\Services\GamificationService;

class PomodoroController extends Controller
{
    public function index()
    {
        $sessions = PomodoroSession::where('user_id', auth()->id())->latest()->paginate(20);
        $todayCount = PomodoroSession::where('user_id', auth()->id())
            ->whereDate('created_at', today())->count();
        $subjects = auth()->user()->subjects;
        return view('Backend.pomodoro.index', compact('sessions', 'todayCount', 'subjects'));
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

        return response()->json(['success' => true]);
    }
}
