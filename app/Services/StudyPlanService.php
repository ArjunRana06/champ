<?php

namespace App\Services;

use App\Models\StudyPlan;

class StudyPlanService
{
    protected AiService $ai;

    public function __construct(AiService $ai)
    {
        $this->ai = $ai;
    }

    public function generate(array $subjects, array $examDates, int $hoursPerDay, string $focus): StudyPlan
    {
        $subjectsList = implode(', ', $subjects);
        $datesList = '';
        foreach ($examDates as $subject => $date) {
            $datesList .= "- {$subject}: {$date}\n";
        }

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an expert study planner and academic coach. Create a detailed weekly study plan based on the student\'s subjects, exam dates, and available hours. Return the plan as a JSON object with NO markdown formatting, NO code fences. The JSON structure must be:
{
  "title": "A concise, motivating title for this plan",
  "overview": "2-3 sentence overview of the plan approach",
  "weekly_schedule": [
    {
      "day": "Monday",
      "sessions": [
        {"time": "9:00 AM - 10:30 AM", "subject": "Subject Name", "topic": "Specific topic to study", "activity": "Read notes / Practice problems / Review flashcards", "priority": "high/medium/low"}
      ]
    }
  ],
  "tips": ["Tip 1", "Tip 2", "Tip 3"],
  "daily_goal": "A suggested daily goal"
}'
            ],
            [
                'role' => 'user',
                'content' => "Create a study plan for me.\n\nSubjects: {$subjectsList}\n\nExam Dates:\n{$datesList}\n\nHours available per day: {$hoursPerDay}\n\nFocus area: {$focus}\n\nPlease create a balanced weekly schedule that covers all subjects, prioritizes subjects with upcoming exams, and includes breaks."
            ]
        ];

        $result = $this->ai->generateJson($messages, env('OPENROUTER_MODEL', 'openai/gpt-3.5-turbo'), 0.3, 1000);

        $plan = StudyPlan::create([
            'user_id' => auth()->id(),
            'title' => $result['title'] ?? 'Study Plan',
            'plan_json' => json_encode($result),
            'subjects' => $subjects,
            'exam_dates' => $examDates,
            'hours_per_day' => $hoursPerDay,
            'model_used' => env('OPENROUTER_MODEL', 'openai/gpt-3.5-turbo'),
        ]);

        return $plan;
    }
}
