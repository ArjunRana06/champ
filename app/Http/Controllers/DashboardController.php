<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Emotion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Total memories
        $totalMemories = Activity::where('user_id', $user->id)->count();

        // Mood counts
        $moodStats = Emotion::whereHas('activity', fn($q) => $q->where('user_id', $user->id))
            ->selectRaw('emotion, count(*) as count')
            ->groupBy('emotion')
            ->get()
            ->pluck('count', 'emotion')
            ->toArray();

        $happyCount = $moodStats['happy'] ?? 0;
        $excitedCount = $moodStats['excited'] ?? 0;
        $neutralCount = $moodStats['neutral'] ?? 0;
        $sadCount = $moodStats['sad'] ?? 0;
        $stressedCount = $moodStats['stressed'] ?? 0;

        $moodData = [
            'Happy'    => $happyCount,
            'Excited'  => $excitedCount,
            'Neutral'  => $neutralCount,
            'Sad'      => $sadCount,
            'Stressed' => $stressedCount,
        ];

        // Monthly mood (line chart) – simplified
        $monthlyMood = Activity::where('user_id', $user->id)
            ->whereIn('type', ['text', 'emotion', 'emoji'])
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get()
            ->map(function ($item) {
                return [
                    'month' => date('M', mktime(0,0,0, $item->month, 1)),
                    'score' => rand(5,9) // replace with real mood scoring logic
                ];
            })->reverse()->values();

        // Recent activities
        $recentActivities = Activity::where('user_id', $user->id)
            ->with('emotions')
            ->latest()
            ->take(4)
            ->get();

        // Tags extraction
        $tagCounts = $this->extractTagsFromActivities($user->id);
        $topTags = collect($tagCounts)->sortDesc()->take(4)->map(function ($count, $tag) {
            return [
                'tag' => $tag,
                'count' => $count,
                'trend' => rand(5, 30) // placeholder
            ];
        })->values();
        $uniqueTagsCount = count($tagCounts);

        // Recent memories table
        $recentMemories = Activity::where('user_id', $user->id)
            ->latest()
            ->take(4)
            ->get()
            ->map(function ($activity) {
                $mood = $activity->emotions->first()->emotion ?? 'neutral';
                $moodIcon = [
                    'happy' => '😊', 'excited' => '🤩', 'neutral' => '😐',
                    'sad' => '😔', 'stressed' => '😫'
                ][$mood] ?? '😊';
                $moodColor = [
                    'happy' => 'success', 'excited' => 'success', 'neutral' => 'info',
                    'sad' => 'warning', 'stressed' => 'warning'
                ][$mood] ?? 'info';
                $tags = $this->extractTagsFromString($activity->parsed_content);
                return (object)[
                    'id' => $activity->id,
                    'title' => $activity->parsed_content ? Str::limit($activity->parsed_content, 40) : 'Untitled',
                    'mood' => $mood,
                    'mood_icon' => $moodIcon,
                    'mood_color' => $moodColor,
                    'date' => $activity->created_at->format('M d, Y'),
                    'tags' => implode(' ', array_map(fn($t) => '#' . $t, $tags)),
                ];
            });

        // Increases – compute from real data (or fallback)
        $happyIncrease = $this->getMoodIncrease($user->id, 'happy');
        $totalIncrease = $this->getTotalIncrease($user->id);
        $tagIncrease = '+6';
        $aiInsightsCount = 42;
        $aiInsightsIncrease = '+8%';

        // Pass all variables to the view
        return view('dashboard', [
            'totalMemories' => $totalMemories,
            'happyCount' => $happyCount,
            'excitedCount' => $excitedCount,
            'neutralCount' => $neutralCount,
            'sadCount' => $sadCount,
            'stressedCount' => $stressedCount,
            'moodData' => $moodData,
            'monthlyMood' => $monthlyMood,
            'recentActivities' => $recentActivities,
            'topTags' => $topTags,
            'uniqueTagsCount' => $uniqueTagsCount,
            'recentMemories' => $recentMemories,
            'happyIncrease' => $happyIncrease,
            'totalIncrease' => $totalIncrease,
            'tagIncrease' => $tagIncrease,
            'aiInsightsCount' => $aiInsightsCount,
            'aiInsightsIncrease' => $aiInsightsIncrease,
        ]);
    }

    private function extractTagsFromActivities($userId)
    {
        $activities = Activity::where('user_id', $userId)
            ->whereIn('type', ['text', 'emoji', 'emotion'])
            ->get();
        $tags = [];
        foreach ($activities as $act) {
            $found = $this->extractTagsFromString($act->parsed_content);
            foreach ($found as $tag) {
                $tags[$tag] = ($tags[$tag] ?? 0) + 1;
            }
        }
        return $tags;
    }

    private function extractTagsFromString($text)
    {
        preg_match_all('/#([a-zA-Z0-9_]+)/', $text, $matches);
        return $matches[1];
    }

    private function getMoodIncrease($userId, $mood)
    {
        $current = Emotion::whereHas('activity', fn($q) => $q->where('user_id', $userId))
            ->where('emotion', $mood)
            ->whereMonth('created_at', now()->month)
            ->count();
        $previous = Emotion::whereHas('activity', fn($q) => $q->where('user_id', $userId))
            ->where('emotion', $mood)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();
        if ($previous == 0) return '+100%';
        $change = round((($current - $previous) / $previous) * 100);
        return ($change >= 0 ? '+' : '') . $change . '%';
    }

    private function getTotalIncrease($userId)
    {
        $current = Activity::where('user_id', $userId)->whereMonth('created_at', now()->month)->count();
        $previous = Activity::where('user_id', $userId)->whereMonth('created_at', now()->subMonth()->month)->count();
        if ($previous == 0) return '+100%';
        $change = round((($current - $previous) / $previous) * 100);
        return ($change >= 0 ? '+' : '') . $change . '%';
    }
}
