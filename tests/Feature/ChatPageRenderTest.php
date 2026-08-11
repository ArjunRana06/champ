<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatPageRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_page_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('ai.chat'));

        $response->assertOk();
        $response->assertSee('Study Assistant for Students');
        $response->assertSee('New chat');
    }
}
