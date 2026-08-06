<?php

namespace Tests\Feature;

use App\Models\FillBlank;
use App\Models\GroupResource;
use App\Models\Mcq;
use App\Models\ShortAnswer;
use App\Models\StudyGroup;
use App\Models\StudyGroupMember;
use App\Models\TrueFalseQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $attrs = []): User
    {
        return User::factory()->create($attrs);
    }

    private function makeGroup(User $owner, array $attrs = []): StudyGroup
    {
        $group = StudyGroup::create(array_merge([
            'name' => 'Test Group',
            'description' => 'A test group',
            'created_by' => $owner->id,
        ], $attrs));

        StudyGroupMember::create([
            'study_group_id' => $group->id,
            'user_id' => $owner->id,
            'role' => 'admin',
        ]);

        return $group;
    }

    private function makeMcq(User $user): Mcq
    {
        return Mcq::create([
            'user_id' => $user->id,
            'subject_id' => null,
            'document_id' => null,
            'question' => 'Sample question?',
            'options' => ['A', 'B', 'C', 'D'],
            'correct_answer' => 'A',
            'explanation' => 'Because.',
            'difficulty' => 'easy',
            'is_public' => false,
        ]);
    }

    // ---------- Authentication ----------

    public function test_guests_are_redirected_from_study_groups(): void
    {
        $this->get(route('study-groups.index'))->assertRedirect(route('login'));
        $this->get(route('shared-questions.index'))->assertRedirect(route('login'));
    }

    public function test_guests_cannot_create_or_join_groups(): void
    {
        $this->post(route('study-groups.store'), ['name' => 'X'])->assertRedirect(route('login'));

        $owner = $this->makeUser();
        $group = $this->makeGroup($owner);

        $this->post(route('study-groups.join', $group))->assertRedirect(route('login'));
    }

    // ---------- Group creation / validation ----------

    public function test_user_can_create_group_and_becomes_admin_member(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->post(route('study-groups.store'), [
            'name' => 'Physics 101',
            'description' => 'Share physics notes',
        ]);

        $response->assertRedirect(route('study-groups.index'));

        $this->assertDatabaseHas('study_groups', [
            'name' => 'Physics 101',
            'created_by' => $user->id,
        ]);

        $group = StudyGroup::where('name', 'Physics 101')->first();
        $this->assertDatabaseHas('study_group_members', [
            'study_group_id' => $group->id,
            'user_id' => $user->id,
            'role' => 'admin',
        ]);
    }

    public function test_group_name_is_required(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->post(route('study-groups.store'), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('study_groups', 0);
    }

    // ---------- Membership gating ----------

    public function test_non_member_cannot_view_group(): void
    {
        $owner = $this->makeUser();
        $stranger = $this->makeUser();
        $group = $this->makeGroup($owner);

        $this->actingAs($stranger)
            ->get(route('study-groups.show', $group))
            ->assertForbidden();
    }

    public function test_member_can_view_group(): void
    {
        $owner = $this->makeUser();
        $group = $this->makeGroup($owner);

        $this->actingAs($owner)
            ->get(route('study-groups.show', $group))
            ->assertOk()
            ->assertSee($group->name);
    }

    public function test_user_can_join_group(): void
    {
        $owner = $this->makeUser();
        $joiner = $this->makeUser();
        $group = $this->makeGroup($owner);

        $this->actingAs($joiner)
            ->post(route('study-groups.join', $group))
            ->assertRedirect();

        $this->assertDatabaseHas('study_group_members', [
            'study_group_id' => $group->id,
            'user_id' => $joiner->id,
            'role' => 'member',
        ]);
    }

    public function test_joining_twice_does_not_duplicate(): void
    {
        $owner = $this->makeUser();
        $joiner = $this->makeUser();
        $group = $this->makeGroup($owner);

        $this->actingAs($joiner)->post(route('study-groups.join', $group));
        $this->actingAs($joiner)->post(route('study-groups.join', $group));

        $this->assertSame(2, StudyGroupMember::where('study_group_id', $group->id)->count());
    }

    public function test_user_can_leave_group(): void
    {
        $owner = $this->makeUser();
        $joiner = $this->makeUser();
        $group = $this->makeGroup($owner);
        StudyGroupMember::create([
            'study_group_id' => $group->id,
            'user_id' => $joiner->id,
            'role' => 'member',
        ]);

        $this->actingAs($joiner)
            ->post(route('study-groups.leave', $group))
            ->assertRedirect();

        $this->assertDatabaseMissing('study_group_members', [
            'study_group_id' => $group->id,
            'user_id' => $joiner->id,
        ]);
    }

    public function test_last_admin_cannot_leave_group(): void
    {
        $owner = $this->makeUser();
        $group = $this->makeGroup($owner);

        $this->actingAs($owner)
            ->post(route('study-groups.leave', $group))
            ->assertStatus(422);

        $this->assertDatabaseHas('study_group_members', [
            'study_group_id' => $group->id,
            'user_id' => $owner->id,
        ]);
    }

    public function test_non_member_cannot_leave_group(): void
    {
        $owner = $this->makeUser();
        $stranger = $this->makeUser();
        $group = $this->makeGroup($owner);

        $this->actingAs($stranger)
            ->post(route('study-groups.leave', $group))
            ->assertStatus(422);
    }

    public function test_sharing_same_question_again_returns_error(): void
    {
        $owner = $this->makeUser();
        $group = $this->makeGroup($owner);
        $mcq = $this->makeMcq($owner);

        $this->actingAs($owner)->post(route('study-groups.share', $group), ['type' => 'mcqs', 'id' => $mcq->id]);

        $this->actingAs($owner)
            ->post(route('study-groups.share', $group), ['type' => 'mcqs', 'id' => $mcq->id])
            ->assertStatus(422);

        $this->assertSame(1, GroupResource::where('study_group_id', $group->id)->count());
    }

    // ---------- Sharing ----------

    public function test_member_can_share_own_question_into_group(): void
    {
        $owner = $this->makeUser();
        $group = $this->makeGroup($owner);
        $mcq = $this->makeMcq($owner);

        $this->actingAs($owner)
            ->post(route('study-groups.share', $group), [
                'type' => 'mcqs',
                'id' => $mcq->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('group_resources', [
            'study_group_id' => $group->id,
            'resourceable_type' => Mcq::class,
            'resourceable_id' => $mcq->id,
            'user_id' => $owner->id,
        ]);
    }

    public function test_non_member_cannot_share_into_group(): void
    {
        $owner = $this->makeUser();
        $stranger = $this->makeUser();
        $group = $this->makeGroup($owner);
        $mcq = $this->makeMcq($stranger);

        $this->actingAs($stranger)
            ->post(route('study-groups.share', $group), [
                'type' => 'mcqs',
                'id' => $mcq->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('group_resources', 0);
    }

    public function test_member_cannot_share_someone_elses_question(): void
    {
        $owner = $this->makeUser();
        $other = $this->makeUser();
        $group = $this->makeGroup($owner);
        StudyGroupMember::create([
            'study_group_id' => $group->id,
            'user_id' => $other->id,
            'role' => 'member',
        ]);
        $mcq = $this->makeMcq($owner);

        $this->actingAs($other)
            ->post(route('study-groups.share', $group), [
                'type' => 'mcqs',
                'id' => $mcq->id,
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('group_resources', 0);
    }

    public function test_share_validates_type_and_id(): void
    {
        $owner = $this->makeUser();
        $group = $this->makeGroup($owner);
        $mcq = $this->makeMcq($owner);

        $this->actingAs($owner)
            ->post(route('study-groups.share', $group), ['type' => 'bogus', 'id' => $mcq->id])
            ->assertSessionHasErrors('type');

        $this->actingAs($owner)
            ->post(route('study-groups.share', $group), ['type' => 'mcqs', 'id' => 99999])
            ->assertNotFound();
    }

    public function test_sharing_same_question_twice_does_not_duplicate(): void
    {
        $owner = $this->makeUser();
        $group = $this->makeGroup($owner);
        $mcq = $this->makeMcq($owner);

        $this->actingAs($owner)->post(route('study-groups.share', $group), ['type' => 'mcqs', 'id' => $mcq->id]);
        $this->actingAs($owner)->post(route('study-groups.share', $group), ['type' => 'mcqs', 'id' => $mcq->id]);

        $this->assertSame(1, GroupResource::where('study_group_id', $group->id)->count());
    }

    // ---------- Unsharing ----------

    public function test_member_can_unshare_own_resource(): void
    {
        $owner = $this->makeUser();
        $group = $this->makeGroup($owner);
        $mcq = $this->makeMcq($owner);
        $resource = GroupResource::create([
            'study_group_id' => $group->id,
            'user_id' => $owner->id,
            'resourceable_type' => Mcq::class,
            'resourceable_id' => $mcq->id,
        ]);

        $this->actingAs($owner)
            ->delete(route('study-groups.unshare', [$group, $resource]))
            ->assertRedirect();

        $this->assertDatabaseMissing('group_resources', ['id' => $resource->id]);
    }

    public function test_non_admin_cannot_unshare_others_resource(): void
    {
        $owner = $this->makeUser();
        $member = $this->makeUser();
        $group = $this->makeGroup($owner);
        StudyGroupMember::create([
            'study_group_id' => $group->id,
            'user_id' => $member->id,
            'role' => 'member',
        ]);
        $mcq = $this->makeMcq($owner);
        $resource = GroupResource::create([
            'study_group_id' => $group->id,
            'user_id' => $owner->id,
            'resourceable_type' => Mcq::class,
            'resourceable_id' => $mcq->id,
        ]);

        $this->actingAs($member)
            ->delete(route('study-groups.unshare', [$group, $resource]))
            ->assertForbidden();

        $this->assertDatabaseHas('group_resources', ['id' => $resource->id]);
    }

    // ---------- Member management ----------

    public function test_only_admin_can_remove_members(): void
    {
        $owner = $this->makeUser();
        $member = $this->makeUser();
        $group = $this->makeGroup($owner);
        $memberRow = StudyGroupMember::create([
            'study_group_id' => $group->id,
            'user_id' => $member->id,
            'role' => 'member',
        ]);

        $this->actingAs($member)
            ->post(route('study-groups.remove-member', [$group, $memberRow]))
            ->assertForbidden();

        $this->assertDatabaseHas('study_group_members', ['id' => $memberRow->id]);

        $this->actingAs($owner)
            ->post(route('study-groups.remove-member', [$group, $memberRow]))
            ->assertRedirect();

        $this->assertDatabaseMissing('study_group_members', ['id' => $memberRow->id]);
    }

    public function test_cannot_remove_last_admin(): void
    {
        $owner = $this->makeUser();
        $group = $this->makeGroup($owner);
        $adminRow = $group->members()->where('user_id', $owner->id)->first();

        $this->actingAs($owner)
            ->post(route('study-groups.remove-member', [$group, $adminRow]))
            ->assertStatus(422);

        $this->assertDatabaseHas('study_group_members', ['id' => $adminRow->id]);
    }

    public function test_only_admin_can_delete_group(): void
    {
        $owner = $this->makeUser();
        $member = $this->makeUser();
        $group = $this->makeGroup($owner);
        StudyGroupMember::create([
            'study_group_id' => $group->id,
            'user_id' => $member->id,
            'role' => 'member',
        ]);

        $this->actingAs($member)
            ->delete(route('study-groups.destroy', $group))
            ->assertForbidden();

        $this->assertDatabaseHas('study_groups', ['id' => $group->id]);

        $this->actingAs($owner)
            ->delete(route('study-groups.destroy', $group))
            ->assertRedirect();

        $this->assertDatabaseMissing('study_groups', ['id' => $group->id]);
    }

    // ---------- Shared question bank ----------

    public function test_public_questions_are_listed_for_everyone(): void
    {
        $author = $this->makeUser();
        $viewer = $this->makeUser();
        $mcq = $this->makeMcq($author);
        $mcq->update(['is_public' => true]);

        $this->actingAs($viewer)
            ->get(route('shared-questions.index'))
            ->assertOk()
            ->assertSee($mcq->question);
    }

    public function test_private_questions_are_not_listed(): void
    {
        $author = $this->makeUser();
        $viewer = $this->makeUser();
        $mcq = $this->makeMcq($author);

        $this->actingAs($viewer)
            ->get(route('shared-questions.index'))
            ->assertOk()
            ->assertDontSee($mcq->question);
    }

    public function test_owner_can_toggle_visibility(): void
    {
        $author = $this->makeUser();
        $mcq = $this->makeMcq($author);

        $this->actingAs($author)
            ->post(route('shared-questions.toggle'), ['type' => 'mcqs', 'id' => $mcq->id],
                ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertDatabaseHas('mcqs', ['id' => $mcq->id, 'is_public' => true]);

        $this->actingAs($author)
            ->post(route('shared-questions.toggle'), ['type' => 'mcqs', 'id' => $mcq->id],
                ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertDatabaseHas('mcqs', ['id' => $mcq->id, 'is_public' => false]);
    }

    public function test_cannot_toggle_visibility_of_someone_elses_question(): void
    {
        $author = $this->makeUser();
        $other = $this->makeUser();
        $mcq = $this->makeMcq($author);

        $this->actingAs($other)
            ->post(route('shared-questions.toggle'), ['type' => 'mcqs', 'id' => $mcq->id])
            ->assertNotFound();

        $this->assertDatabaseHas('mcqs', ['id' => $mcq->id, 'is_public' => false]);
    }
}
