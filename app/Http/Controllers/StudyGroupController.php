<?php

namespace App\Http\Controllers;

use App\Models\StudyGroup;
use App\Models\StudyGroupMember;
use App\Models\GroupResource;
use App\Models\Mcq;
use App\Models\TrueFalseQuestion;
use App\Models\ShortAnswer;
use App\Models\FillBlank;
use App\Models\MatchingQuestion;
use App\Models\Flashcard;
use Illuminate\Http\Request;

class StudyGroupController extends Controller
{
    public function index()
    {
        $myGroups = StudyGroup::whereHas('members', fn($q) => $q->where('user_id', auth()->id()))
            ->withCount(['members', 'members as is_admin' => fn($q) => $q->where('user_id', auth()->id())->where('role', 'admin'), 'resources'])
            ->latest()->get();

        $otherGroups = StudyGroup::whereDoesntHave('members', fn($q) => $q->where('user_id', auth()->id()))
            ->withCount('members')
            ->latest()
            ->get();

        return view('Backend.study-groups.index', compact('myGroups', 'otherGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $group = StudyGroup::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => auth()->id(),
        ]);

        StudyGroupMember::create([
            'study_group_id' => $group->id,
            'user_id' => auth()->id(),
            'role' => 'admin',
        ]);

        return redirect()->route('study-groups.index')->with('success', 'Study group created!');
    }

    public function show(StudyGroup $studyGroup)
    {
        $studyGroup->load('members.user', 'resources.user', 'resources.resourceable');

        $questions = $this->getUserQuestions();

        return view('Backend.study-groups.show', compact('studyGroup', 'questions'));
    }

    public function join(StudyGroup $studyGroup)
    {
        StudyGroupMember::firstOrCreate([
            'study_group_id' => $studyGroup->id,
            'user_id' => auth()->id(),
        ], ['role' => 'member']);

        return back()->with('success', 'Joined group!');
    }

    public function leave(StudyGroup $studyGroup)
    {
        StudyGroupMember::where('study_group_id', $studyGroup->id)
            ->where('user_id', auth()->id())
            ->delete();

        return redirect()->route('study-groups.index')->with('success', 'You left the group.');
    }

    public function share(Request $request, StudyGroup $studyGroup)
    {
        $request->validate([
            'type' => 'required|string|in:mcqs,true_false,short_answers,fill_blanks,matching,flashcards',
            'id' => 'required|integer',
        ]);

        $isMember = $studyGroup->members()->where('user_id', auth()->id())->exists();
        if (!$isMember) abort(403);

        $model = $this->getModel($request->type);
        $item = $model::where('id', $request->id)->where('user_id', auth()->id())->firstOrFail();

        GroupResource::firstOrCreate([
            'study_group_id' => $studyGroup->id,
            'resourceable_type' => $model,
            'resourceable_id' => $item->id,
        ], [
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Question shared with group!');
    }

    public function unshare(StudyGroup $studyGroup, GroupResource $resource)
    {
        if ($resource->study_group_id !== $studyGroup->id) abort(404);

        $isAdmin = $studyGroup->members()
            ->where('user_id', auth()->id())
            ->where('role', 'admin')
            ->exists();

        if ($resource->user_id !== auth()->id() && !$isAdmin) abort(403);

        $resource->delete();
        return back()->with('success', 'Resource removed from group.');
    }

    public function destroy(StudyGroup $studyGroup)
    {
        $isAdmin = $studyGroup->members()
            ->where('user_id', auth()->id())
            ->where('role', 'admin')
            ->exists();
        if (!$isAdmin) abort(403);
        $studyGroup->delete();
        return redirect()->route('study-groups.index')->with('success', 'Group deleted.');
    }

    private function getUserQuestions(): array
    {
        $types = [
            'mcqs' => Mcq::class,
            'true_false' => TrueFalseQuestion::class,
            'short_answers' => ShortAnswer::class,
            'fill_blanks' => FillBlank::class,
            'matching' => MatchingQuestion::class,
            'flashcards' => Flashcard::class,
        ];
        $questions = [];
        foreach ($types as $key => $model) {
            $questions[$key] = $model::where('user_id', auth()->id())
                ->with('subject')
                ->latest()
                ->get()
                ->map(fn($q) => [
                    'id' => $q->id,
                    'label' => $q->question ?? $q->statement ?? $q->front ?? $q->sentence_with_blanks ?? 'Question #'.$q->id,
                    'subject' => $q->subject?->name ?? 'No subject',
                ]);
        }
        return $questions;
    }

    private function getModel(string $type): string
    {
        return match ($type) {
            'mcqs' => Mcq::class,
            'true_false' => TrueFalseQuestion::class,
            'short_answers' => ShortAnswer::class,
            'fill_blanks' => FillBlank::class,
            'matching' => MatchingQuestion::class,
            'flashcards' => Flashcard::class,
            default => throw new \InvalidArgumentException("Invalid question type: {$type}"),
        };
    }
}
