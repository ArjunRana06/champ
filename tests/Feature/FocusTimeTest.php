<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FocusTimeTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::factory()->create();
    }

    public function test_pomodoro_page_requires_authentication(): void
    {
        $this->get(route('pomodoro.index'))->assertRedirect(route('login'));
    }

    public function test_time_entries_page_requires_authentication(): void
    {
        $this->get(route('time-entries.index'))->assertRedirect(route('login'));
    }

    public function test_pomodoro_complete_records_session_and_awards_xp(): void
    {
        $user = $this->makeUser();
        $subject = Subject::create(['user_id' => $user->id, 'name' => 'Math']);

        $response = $this->actingAs($user)->postJson(route('pomodoro.complete'), [
            'duration_minutes' => 25,
            'break_minutes' => 5,
            'subject_id' => $subject->id,
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('pomodoro_sessions', [
            'user_id' => $user->id,
            'subject_id' => $subject->id,
            'duration_minutes' => 25,
            'status' => 'completed',
        ]);
        $this->assertSame(10, $user->fresh()->xp);
    }

    public function test_pomodoro_complete_rejects_another_users_subject(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser();
        $subject = Subject::create(['user_id' => $other->id, 'name' => 'Foreign']);

        $this->actingAs($user)
            ->postJson(route('pomodoro.complete'), ['duration_minutes' => 25, 'subject_id' => $subject->id])
            ->assertStatus(422);

        $this->assertDatabaseCount('pomodoro_sessions', 0);
        $this->assertSame(0, $user->fresh()->xp);
    }

    public function test_time_entry_start_requires_own_subject(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser();
        $subject = Subject::create(['user_id' => $other->id, 'name' => 'Foreign']);

        $this->actingAs($user)
            ->postJson(route('time-entries.start'), ['subject_id' => $subject->id])
            ->assertStatus(422);

        $this->assertDatabaseCount('time_entries', 0);
    }

    public function test_time_entry_start_closes_stale_active_entries(): void
    {
        $user = $this->makeUser();
        TimeEntry::create([
            'user_id' => $user->id,
            'started_at' => now()->subMinutes(30),
        ]);

        $this->actingAs($user)
            ->postJson(route('time-entries.start'), ['description' => 'Study'])
            ->assertOk();

        $this->assertSame(2, TimeEntry::where('user_id', $user->id)->count());
        $this->assertNotNull(TimeEntry::where('user_id', $user->id)->whereNotNull('ended_at')->first());
        $this->assertSame(1, TimeEntry::where('user_id', $user->id)->whereNull('ended_at')->count());
    }

    public function test_time_entry_stop_saves_active_entry(): void
    {
        $user = $this->makeUser();
        $entry = TimeEntry::create([
            'user_id' => $user->id,
            'started_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($user)->post(route('time-entries.stop'))->assertOk();

        $this->assertDatabaseHas('time_entries', [
            'id' => $entry->id,
            'duration_minutes' => 10,
        ]);
        $this->assertNotNull($entry->fresh()->ended_at);
    }

    public function test_time_entry_discard_deletes_active_entry(): void
    {
        $user = $this->makeUser();
        $entry = TimeEntry::create([
            'user_id' => $user->id,
            'started_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($user)->post(route('time-entries.discard'))->assertOk();

        $this->assertDatabaseMissing('time_entries', ['id' => $entry->id]);
    }

    public function test_destroy_deletes_own_entry_only(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser();
        $entry = TimeEntry::create([
            'user_id' => $other->id,
            'started_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($user)
            ->delete(route('time-entries.destroy', $entry))
            ->assertStatus(403);

        $this->assertDatabaseHas('time_entries', ['id' => $entry->id]);
    }
}
