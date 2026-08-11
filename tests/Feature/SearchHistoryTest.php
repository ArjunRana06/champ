<?php

namespace Tests\Feature;

use App\Models\SearchHistory;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_records_history_and_shows_results(): void
    {
        $user = User::factory()->create();
        Subject::create(['user_id' => $user->id, 'name' => 'Linear Algebra', 'code' => 'MATH101', 'semester' => 'Fall']);

        $response = $this->actingAs($user)->get(route('search', ['q' => 'linear']));

        $response->assertOk();
        $response->assertSee('Linear Algebra');

        $this->assertDatabaseHas('search_histories', [
            'user_id' => $user->id,
            'query' => 'linear',
            'result_count' => 1,
        ]);
    }

    public function test_history_endpoint_returns_recent_searches(): void
    {
        $user = User::factory()->create();
        SearchHistory::create([
            'user_id' => $user->id,
            'query' => 'calculus',
            'result_count' => 3,
            'searched_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson(route('search.history'));

        $response->assertOk()
            ->assertJsonPath('history.0.query', 'calculus')
            ->assertJsonPath('history.0.result_count', 3);
    }

    public function test_delete_single_history_item(): void
    {
        $user = User::factory()->create();
        $history = SearchHistory::create([
            'user_id' => $user->id,
            'query' => 'physics',
            'result_count' => 2,
            'searched_at' => now(),
        ]);

        $this->actingAs($user)->deleteJson(route('search.history.destroy', $history->id))->assertOk();

        $this->assertDatabaseMissing('search_histories', ['id' => $history->id]);
    }

    public function test_clear_all_history(): void
    {
        $user = User::factory()->create();
        SearchHistory::create([
            'user_id' => $user->id,
            'query' => 'chemistry',
            'result_count' => 1,
            'searched_at' => now(),
        ]);

        $this->actingAs($user)->deleteJson(route('search.history.clear'))->assertOk();

        $this->assertDatabaseCount('search_histories', 0);
    }
}
