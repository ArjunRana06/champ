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

        return $this->successResponse($request, 'Study group created!', [
            'group' => $group,
            'redirect' => route('study-groups.index'),
        ]);
    }

    public function update(Request $request, StudyGroup $studyGroup)
    {
        $isAdmin = $studyGroup->members()->where('user_id', auth()->id())->where('role', 'admin')->exists();
        if (!$isAdmin) {
            return $this->errorResponse($request, 'Only group admins can edit the group.', 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $studyGroup->update($request->only(['name', 'description']));

        return $this->successResponse($request, 'Group updated!');
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

        if (!$member->wasRecentlyCreated) {
            return $this->errorResponse($request, 'You are already a member of this group.', 422);
        }

        if ($studyGroup->created_by !== auth()->id()) {
            $this->notificationService->notifyGroupJoinedToCreator(
                $studyGroup->created_by,
                auth()->user()->name,
                $studyGroup->name,
                $studyGroup->id
            );
        }

        $this->notificationService->notifyGroupJoined(auth()->id(), $studyGroup->name);

        return $this->successResponse($request, 'Joined group!');
    }

    public function leave(Request $request, StudyGroup $studyGroup)
    {
        $member = StudyGroupMember::where('study_group_id', $studyGroup->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$member) {
            return $this->errorResponse($request, 'You are not a member of this group.', 422);
        }

        $isLastAdmin = $member->role === 'admin'
            && $studyGroup->members()->where('role', 'admin')->count() <= 1;

        if ($isLastAdmin) {
            return $this->errorResponse(
                $request,
                'You are the only admin of this group. Delete the group or promote another member first.',
                422
            );
        }

        $member->delete();

        $this->notificationService->notifyGroupLeft(auth()->id(), $studyGroup->name);

        return $this->successResponse($request, 'You left the group.', ['redirect' => route('study-groups.index')]);
    }

    public function removeMember(Request $request, StudyGroup $studyGroup, StudyGroupMember $member)
    {
        $isAdmin = $studyGroup->members()->where('user_id', auth()->id())->where('role', 'admin')->exists();
        if (!$isAdmin) {
            return $this->errorResponse($request, 'Only group admins can remove members.', 403);
        }
        if ($member->study_group_id !== $studyGroup->id) {
            return $this->errorResponse($request, 'Member not found in this group.', 404);
        }
        if ($member->role === 'admin' && $studyGroup->members()->where('role', 'admin')->count() <= 1) {
            return $this->errorResponse($request, 'Cannot remove the last admin.', 422);
        }

        $member->delete();

        return $this->successResponse($request, 'Member removed.');
    }

    public function updateMemberRole(Request $request, StudyGroup $studyGroup, StudyGroupMember $member)
    {
        $isAdmin = $studyGroup->members()->where('user_id', auth()->id())->where('role', 'admin')->exists();
        if (!$isAdmin) {
            return $this->errorResponse($request, 'Only group admins can change roles.', 403);
        }
        if ($member->study_group_id !== $studyGroup->id) {
            return $this->errorResponse($request, 'Member not found in this group.', 404);
        }

        $request->validate(['role' => 'required|in:admin,member']);

        if ($member->role === 'admin' && $request->role === 'member'
            && $studyGroup->members()->where('role', 'admin')->count() <= 1) {
            return $this->errorResponse($request, 'Cannot demote the last admin.', 422);
        }

        $member->update(['role' => $request->role]);

        return $this->successResponse($request, 'Role updated!');
    }

    public function share(Request $request, StudyGroup $studyGroup)
    {
        $request->validate([
            'type' => 'required|string|in:mcqs,true_false,short_answers,fill_blanks,matching,flashcards',
            'id' => 'required|integer',
        ]);

        $isMember = $studyGroup->members()->where('user_id', auth()->id())->exists();
        if (!$isMember) {
            return $this->errorResponse($request, 'You must be a member of this group to share questions.', 403);
        }

        $model = $this->getModel($request->type);
        $item = $model::where('id', $request->id)->where('user_id', auth()->id())->first();

        if (!$item) {
            return $this->errorResponse($request, 'Question not found, or it does not belong to you.', 404);
        }

        $alreadyShared = GroupResource::where([
            'study_group_id' => $studyGroup->id,
            'resourceable_type' => $model,
            'resourceable_id' => $item->id,
        ])->exists();

        if ($alreadyShared) {
            return $this->errorResponse($request, 'This question is already shared with this group.', 422);
        }

        GroupResource::create([
            'study_group_id' => $studyGroup->id,
            'resourceable_type' => $model,
            'resourceable_id' => $item->id,
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

        return $this->successResponse($request, 'Question shared with group!');
    }

    public function unshare(Request $request, StudyGroup $studyGroup, GroupResource $resource)
    {
        if ($resource->study_group_id !== $studyGroup->id) {
            return $this->errorResponse($request, 'Resource not found in this group.', 404);
        }

        $isAdmin = $studyGroup->members()
            ->where('user_id', auth()->id())
            ->where('role', 'admin')
            ->exists();

        if ($resource->user_id !== auth()->id() && !$isAdmin) {
            return $this->errorResponse($request, 'You can only remove resources you shared.', 403);
        }

        $resource->delete();

        $this->notificationService->notifyGroupResourceUnshared(auth()->id(), $studyGroup->name);

        return $this->successResponse($request, 'Resource removed from group.');
    }

    public function moveToShared(Request $request, StudyGroup $studyGroup, GroupResource $resource)
    {
        if ($resource->study_group_id !== $studyGroup->id) {
            return $this->errorResponse($request, 'Resource not found in this group.', 404);
        }
        $isAdmin = $studyGroup->members()->where('user_id', auth()->id())->where('role', 'admin')->exists();
        if (!$isAdmin && $resource->user_id !== auth()->id()) {
            return $this->errorResponse($request, 'You can only share resources you own.', 403);
        }

        $modelClass = $resource->resourceable_type;
        $item = $modelClass::find($resource->resourceable_id);
        if (!$item) {
            return $this->errorResponse($request, 'The original question no longer exists.', 404);
        }
        $item->update(['is_public' => true]);

        return $this->successResponse($request, 'Resource made public to all students!');
    }

    public function destroy(Request $request, StudyGroup $studyGroup)
    {
        $isAdmin = $studyGroup->members()
            ->where('user_id', auth()->id())
            ->where('role', 'admin')
            ->exists();
        if (!$isAdmin) {
            return $this->errorResponse($request, 'Only group admins can delete the group.', 403);
        }

        $name = $studyGroup->name;

        $memberIds = $studyGroup->members()->pluck('user_id');
        foreach ($memberIds as $uid) {
            $this->notificationService->notifyGroupDeleted($uid, $name);
        }

        StudyGroupMember::where('study_group_id', $studyGroup->id)->delete();
        GroupResource::where('study_group_id', $studyGroup->id)->delete();
        $studyGroup->delete();

        return $this->successResponse($request, 'Group deleted.', ['redirect' => route('study-groups.index')]);
    }

    private function successResponse(Request $request, string $message, array $extra = [])
    {
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(array_merge(['success' => true, 'message' => $message], $extra));
        }
        $target = $extra['redirect'] ?? null;
        return ($target ? redirect($target) : redirect()->back())->with('success', $message);
    }

    private function errorResponse(Request $request, string $message, int $status = 422)
    {
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], $status);
        }
        abort($status, $message);
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
