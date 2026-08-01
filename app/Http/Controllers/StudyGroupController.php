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
use App\Services\NotificationService;
use App\Traits\HasQuestionType;
use Illuminate\Http\Request;

class StudyGroupController extends Controller
{
    use HasQuestionType;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $userId = auth()->id();

        $myGroups = StudyGroup::whereHas('members', fn($q) => $q->where('user_id', $userId))
            ->withCount(['members', 'resources'])
            ->with(['members' => fn($q) => $q->where('user_id', $userId)])
            ->latest()->get();

        $otherGroups = StudyGroup::whereDoesntHave('members', fn($q) => $q->where('user_id', $userId))
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

        $this->notificationService->notifyGroupCreated(auth()->id(), $group->name);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Study group created!', 'group' => $group]);
        }
        return redirect()->route('study-groups.index')->with('success', 'Study group created!');
    }

    public function update(Request $request, StudyGroup $studyGroup)
    {
        $isAdmin = $studyGroup->members()->where('user_id', auth()->id())->where('role', 'admin')->exists();
        if (!$isAdmin) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $studyGroup->update($request->only(['name', 'description']));

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Group updated!']);
        }
        return back()->with('success', 'Group updated!');
    }

    public function show(StudyGroup $studyGroup)
    {
        $isMember = $studyGroup->members()->where('user_id', auth()->id())->exists();
        if (!$isMember) abort(403, 'You must be a member to view this group.');

        $studyGroup->load(['members.user', 'resources.user', 'resources.resourceable']);

        $questions = $this->getUserQuestions();

        return view('Backend.study-groups.show', compact('studyGroup', 'questions'));
    }

    public function join(Request $request, StudyGroup $studyGroup)
    {
        $member = StudyGroupMember::firstOrCreate([
            'study_group_id' => $studyGroup->id,
            'user_id' => auth()->id(),
        ], ['role' => 'member']);

        if ($member->wasRecentlyCreated && $studyGroup->created_by !== auth()->id()) {
            $this->notificationService->notifyGroupJoinedToCreator(
                $studyGroup->created_by,
                auth()->user()->name,
                $studyGroup->name,
                $studyGroup->id
            );
        }

        $this->notificationService->notifyGroupJoined(auth()->id(), $studyGroup->name);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Joined group!']);
        }
        return back()->with('success', 'Joined group!');
    }

    public function leave(Request $request, StudyGroup $studyGroup)
    {
        StudyGroupMember::where('study_group_id', $studyGroup->id)
            ->where('user_id', auth()->id())
            ->delete();

        $this->notificationService->notifyGroupLeft(auth()->id(), $studyGroup->name);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'You left the group.']);
        }
        return redirect()->route('study-groups.index')->with('success', 'You left the group.');
    }

    public function removeMember(Request $request, StudyGroup $studyGroup, StudyGroupMember $member)
    {
        $isAdmin = $studyGroup->members()->where('user_id', auth()->id())->where('role', 'admin')->exists();
        if (!$isAdmin) abort(403);
        if ($member->study_group_id !== $studyGroup->id) abort(404);
        if ($member->role === 'admin' && $studyGroup->members()->where('role', 'admin')->count() <= 1) {
            return response()->json(['success' => false, 'message' => 'Cannot remove the last admin.'], 422);
        }

        $member->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Member removed.']);
        }
        return back()->with('success', 'Member removed.');
    }

    public function updateMemberRole(Request $request, StudyGroup $studyGroup, StudyGroupMember $member)
    {
        $isAdmin = $studyGroup->members()->where('user_id', auth()->id())->where('role', 'admin')->exists();
        if (!$isAdmin) abort(403);
        if ($member->study_group_id !== $studyGroup->id) abort(404);

        $request->validate(['role' => 'required|in:admin,member']);

        $member->update(['role' => $request->role]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Role updated!']);
        }
        return back()->with('success', 'Role updated.');
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

        $this->notificationService->notifyGroupResourceShared(auth()->id(), $studyGroup->name);

        $questionTitle = $item->question ?? $item->statement ?? $item->front ?? $item->sentence_with_blanks ?? 'Question';
        $memberIds = $studyGroup->members()->where('user_id', '!=', auth()->id())->pluck('user_id');
        foreach ($memberIds as $memberId) {
            $this->notificationService->notifyResourceSharedToMembers(
                $memberId,
                $studyGroup->name,
                $questionTitle,
                route('study-groups.show', $studyGroup->id)
            );
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Question shared with group!']);
        }
        return back()->with('success', 'Question shared with group!');
    }

    public function unshare(Request $request, StudyGroup $studyGroup, GroupResource $resource)
    {
        if ($resource->study_group_id !== $studyGroup->id) abort(404);

        $isAdmin = $studyGroup->members()
            ->where('user_id', auth()->id())
            ->where('role', 'admin')
            ->exists();

        if ($resource->user_id !== auth()->id() && !$isAdmin) abort(403);

        $resource->delete();

        $this->notificationService->notifyGroupResourceUnshared(auth()->id(), $studyGroup->name);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Resource removed from group.']);
        }
        return back()->with('success', 'Resource removed from group.');
    }

    public function moveToShared(Request $request, StudyGroup $studyGroup, GroupResource $resource)
    {
        if ($resource->study_group_id !== $studyGroup->id) abort(404);
        $isAdmin = $studyGroup->members()->where('user_id', auth()->id())->where('role', 'admin')->exists();
        if (!$isAdmin && $resource->user_id !== auth()->id()) abort(403);

        $modelClass = $resource->resourceable_type;
        $item = $modelClass::find($resource->resourceable_id);
        if ($item) {
            $item->update(['is_public' => true]);
        }

        return response()->json(['success' => true, 'message' => 'Resource made public to all students!']);
    }

    public function destroy(Request $request, StudyGroup $studyGroup)
    {
        $isAdmin = $studyGroup->members()
            ->where('user_id', auth()->id())
            ->where('role', 'admin')
            ->exists();
        if (!$isAdmin) abort(403);

        $name = $studyGroup->name;

        $memberIds = $studyGroup->members()->pluck('user_id');
        foreach ($memberIds as $uid) {
            $this->notificationService->notifyGroupDeleted($uid, $name);
        }

        StudyGroupMember::where('study_group_id', $studyGroup->id)->delete();
        GroupResource::where('study_group_id', $studyGroup->id)->delete();
        $studyGroup->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Group deleted.']);
        }
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
}
