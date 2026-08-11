<?php

namespace Tests\Feature;

use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_with_recent_searches_widget(): void
    {
        $user = User::factory()->create();
        SearchHistory::create([
            'user_id' => $user->id,
            'query' => 'calculus',
            'result_count' => 3,
            'searched_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Recent Searches');
        $response->assertSee('calculus');
    }

    public function test_dashboard_renders_without_search_history(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('No searches yet');
    }
}
