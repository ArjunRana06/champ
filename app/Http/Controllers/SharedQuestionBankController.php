<?php

namespace App\Http\Controllers;

use App\Models\GroupResource;
use App\Models\StudyGroup;
use App\Models\StudyGroupMember;
use App\Models\Subject;
use App\Traits\HasQuestionType;
use Illuminate\Http\Request;

class SharedQuestionBankController extends Controller
{
    use HasQuestionType;

    public function index(Request $request)
    {
        $types = ['mcqs', 'true_false', 'short_answers', 'fill_blanks', 'matching', 'flashcards'];
        $subjectId = $request->get('subject', '');
        $search = $request->get('search', '');

        $myGroups = StudyGroup::whereHas('members', fn ($q) => $q->where('user_id', auth()->id()))
            ->withCount('members')
            ->latest()
            ->get();

        $myGroupIds = $myGroups->pluck('id');

        $hasGroups = $myGroups->isNotEmpty();

        $shared = [];
        $myQuestions = [];
        $myShared = [];
        $subjects = collect();

        if ($hasGroups) {
            foreach ($types as $type) {
                $model = $this->getModel($type);

                $sharedIds = GroupResource::whereIn('study_group_id', $myGroupIds)
                    ->where('resourceable_type', $model)
                    ->pluck('resourceable_id');

                $sharedQuery = $model::whereIn('id', $sharedIds)->with('user', 'subject');
                if ($subjectId) {
                    $sharedQuery->where('subject_id', $subjectId);
                }
                if ($search) {
                    $this->applyQuestionSearch($sharedQuery, $type, $search);
                }
                $shared[$type] = $sharedQuery->latest()->take(20)->get();

                $myQuery = $model::where('user_id', auth()->id())->with('subject');
                if ($search) {
                    $this->applyQuestionSearch($myQuery, $type, $search);
                }
                $myQuestions[$type] = $myQuery->latest()->get();

                $myIds = $myQuestions[$type]->pluck('id');
                $myShared[$type] = collect();
                if ($myIds->isNotEmpty()) {
                    $resources = GroupResource::whereIn('study_group_id', $myGroupIds)
                        ->where('resourceable_type', $model)
                        ->whereIn('resourceable_id', $myIds)
                        ->with('group:id,name')
                        ->get();
                    $myShared[$type] = $resources->groupBy('resourceable_id');
                }
            }

            $sharedMcqIds = GroupResource::whereIn('study_group_id', $myGroupIds)
                ->where('resourceable_type', 'App\Models\Mcq')
                ->pluck('resourceable_id');

            $subjects = Subject::where(function ($q) use ($sharedMcqIds) {
                $q->where('user_id', auth()->id())
                    ->orWhereHas('mcqs', fn ($q) => $q->whereIn('id', $sharedMcqIds));
            })->orderBy('name')->get();
        }

        return view('Backend.shared-questions.index', compact('shared', 'types', 'myQuestions', 'myShared', 'subjects', 'subjectId', 'search', 'myGroups', 'hasGroups'));
    }

    public function fetchMore(Request $request)
    {
        if (! $this->userHasGroup(auth()->id())) {
            return response()->json(['error' => 'Join or create a study group to browse the community.'], 403);
        }

        $type = $request->get('type');
        if (! in_array($type, ['mcqs', 'true_false', 'short_answers', 'fill_blanks', 'matching', 'flashcards'], true)) {
            return response()->json(['error' => 'Invalid question type.'], 422);
        }
        $offset = max(0, (int) $request->get('offset', 0));
        $limit = 10;

        $model = $this->getModel($type);
        $myGroupIds = StudyGroupMember::where('user_id', auth()->id())->pluck('study_group_id');

        $sharedIds = GroupResource::whereIn('study_group_id', $myGroupIds)
            ->where('resourceable_type', $model)
            ->pluck('resourceable_id');

        $items = $model::whereIn('id', $sharedIds)
            ->with('user', 'subject')
            ->latest()
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'text' => $i->question ?? $i->statement ?? $i->front ?? $i->sentence_with_blanks ?? 'Question',
                'user' => $i->user?->name ?? 'Unknown',
                'subject' => $i->subject?->name ?? 'General',
            ]);

        return response()->json(['items' => $items, 'has_more' => $items->count() >= $limit]);
    }

    private function userHasGroup(int $userId): bool
    {
        return StudyGroupMember::where('user_id', $userId)->exists();
    }
}
