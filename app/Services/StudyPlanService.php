<?php

namespace App\Services;

use App\Models\DocumentChunk;
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

        // Gather document summaries for each subject to personalize the plan
        $documentContext = $this->getDocumentContext($subjects);

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an expert study planner and academic coach. Create a detailed weekly study plan based on the student\'s subjects, exam dates, available hours, and actual study materials. Return the plan as a JSON object with NO markdown formatting, NO code fences. The JSON structure must be:
{
  "title": "A concise, motivating title for this plan",
  "overview": "2-3 sentence overview of the plan approach",
  "weekly_schedule": [
    {
      "day": "Monday",
      "sessions": [
        {"time": "9:00 AM - 10:30 AM", "subject": "Subject Name", "topic": "Specific topic from their materials", "activity": "Read notes / Practice problems / Review flashcards", "priority": "high/medium/low"}
      ]
    }
  ],
  "tips": ["Tip 1", "Tip 2", "Tip 3"],
  "daily_goal": "A suggested daily goal",
  "focus_areas": ["Key area 1 to prioritize", "Key area 2"]
}'
            ],
            [
                'role' => 'user',
                'content' => "Create a study plan for me.\n\nSubjects: {$subjectsList}\n\nExam Dates:\n{$datesList}\n\nHours available per day: {$hoursPerDay}\n\nFocus area: {$focus}\n\nTheir study materials cover:\n{$documentContext}\n\nPlease create a balanced weekly schedule that covers all subjects, prioritizes subjects with upcoming exams, references specific topics from their materials, and includes breaks. Make each session concrete and actionable."
            ]
        ];

        $result = $this->ai->generateJson($messages, null, 0.3, 4096);

        $plan = StudyPlan::create([
            'user_id' => auth()->id(),
            'title' => $result['title'] ?? 'Study Plan',
            'plan_json' => json_encode($result),
            'subjects' => $subjects,
            'exam_dates' => $examDates,
            'hours_per_day' => $hoursPerDay,
            'model_used' => config('services.openrouter.model'),
        ]);

        return $plan;
    }

    private function getDocumentContext(array $subjects): string
    {
        $user = auth()->user();
        if (!$user) return 'No materials available.';

        $documents = $user->documents()
            ->where('status', 'completed')
            ->whereIn('subject_id', function ($q) use ($subjects) {
                $q->select('id')->from('subjects')
                  ->whereIn('name', $subjects);
            })
            ->latest()
            ->take(5)
            ->get();

        if ($documents->isEmpty()) {
            return 'No processed documents found for these subjects.';
        }

        $context = '';
        foreach ($documents as $doc) {
            $summary = $doc->summary;
            $context .= "- {$doc->original_name}";
            if ($summary) {
                $context .= ': ' . mb_substr(strip_tags($summary->summary), 0, 300);
            }
            $context .= "\n";
        }

        return $context ?: 'No material summaries available.';
    }
}
