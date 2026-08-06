<?php

namespace App\Http\Controllers;

use App\Models\StudyPlan;
use App\Services\StudyPlanService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class StudyPlanController extends Controller
{
    protected StudyPlanService $studyPlanService;
    protected NotificationService $notificationService;

    public function __construct(StudyPlanService $studyPlanService, NotificationService $notificationService)
    {
        $this->studyPlanService = $studyPlanService;
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $plans = StudyPlan::where('user_id', auth()->id())->latest()->paginate(10);
        $totalPlans = StudyPlan::where('user_id', auth()->id())->count();
        $totalHoursDay = StudyPlan::where('user_id', auth()->id())->sum('hours_per_day');
        $uniqueSubjects = StudyPlan::where('user_id', auth()->id())
            ->pluck('subjects')->flatten()->unique()->filter()->count();
        return view('Backend.study-plans.index', compact('plans', 'totalPlans', 'totalHoursDay', 'uniqueSubjects'));
    }

    public function create()
    {
        $subjects = auth()->user()->subjects;
        return view('Backend.study-plans.create', compact('subjects'));
    }

    public function generate(Request $request)
    {
        $subjects = collect($request->subjects ?? [])
            ->map(fn($s) => trim((string) $s))
            ->filter(fn($s) => $s !== '')
            ->values()->all();

        $request->merge(['subjects' => $subjects]);

        $request->validate([
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'string|max:255',
            'exam_dates' => 'nullable|array',
            'hours_per_day' => 'required|integer|min:1|max:16',
            'focus' => 'nullable|string|max:500',
        ]);

        $examDates = $request->exam_dates ?? [];
        $hoursPerDay = $request->hours_per_day;
        $focus = $request->focus ?? 'General study';

        try {
            $plan = $this->studyPlanService->generate($subjects, $examDates, $hoursPerDay, $focus);

            $this->notificationService->notifyStudyPlanGenerated(auth()->id());

            return redirect()->route('study-plans.show', $plan)->with('success', 'Study plan generated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate study plan: ' . $e->getMessage());
        }
    }

    public function show(StudyPlan $studyPlan)
    {
        if ($studyPlan->user_id !== auth()->id()) abort(403);
        $planData = json_decode($studyPlan->plan_json, true);
        return view('Backend.study-plans.show', compact('studyPlan', 'planData'));
    }

    public function destroy(StudyPlan $studyPlan)
    {
        if ($studyPlan->user_id !== auth()->id()) abort(403);
        $studyPlan->delete();

        $this->notificationService->notifyStudyPlanDeleted(auth()->id());

        return redirect()->route('study-plans.index')->with('success', 'Study plan deleted.');
    }
}
