<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_message_creates_conversation_and_saves_both_roles(): void
    {
        $user = User::factory()->create();

        $this->mock(\App\Services\ChatbotService::class)
            ->shouldReceive('chat')
            ->once()
            ->andReturn('That is correct!');

        $this->actingAs($user)
            ->postJson(route('chat.send'), ['message' => 'What is photosynthesis?'])
            ->assertOk()
            ->assertJsonPath('conversation.title', 'What is photosynthesis?')
            ->assertJsonPath('response', 'That is correct!');

        $this->assertDatabaseCount('chat_conversations', 1);
        $this->assertDatabaseCount('chat_messages', 2);
        $this->assertDatabaseHas('chat_messages', ['role' => 'user', 'content' => 'What is photosynthesis?']);
        $this->assertDatabaseHas('chat_messages', ['role' => 'assistant', 'content' => 'That is correct!']);
    }

    public function test_second_message_reuses_active_conversation(): void
    {
        $user = User::factory()->create();
        $conv = ChatConversation::create(['user_id' => $user->id, 'title' => 'First message']);
        ChatMessage::create(['conversation_id' => $conv->id, 'role' => 'user', 'content' => 'Hello']);
        ChatMessage::create(['conversation_id' => $conv->id, 'role' => 'assistant', 'content' => 'Hi!']);

        session()->put('active_chat_conversation_'.$user->id, $conv->id);

        $this->mock(\App\Services\ChatbotService::class)
            ->shouldReceive('chat')
            ->once()
            ->andReturn('Sure thing!');

        $this->actingAs($user)
            ->postJson(route('chat.send'), ['message' => 'Explain gravity'])
            ->assertOk()
            ->assertJsonPath('conversation.id', $conv->id);

        $this->assertDatabaseCount('chat_conversations', 1);
        $this->assertDatabaseCount('chat_messages', 4);
    }

    public function test_conversations_endpoint_lists_only_own_conversations(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $own = ChatConversation::create(['user_id' => $user->id, 'title' => 'Mine']);
        ChatMessage::create(['conversation_id' => $own->id, 'role' => 'user', 'content' => 'A question']);
        ChatMessage::create(['conversation_id' => $own->id, 'role' => 'assistant', 'content' => 'An answer']);

        ChatConversation::create(['user_id' => $other->id, 'title' => 'Not mine']);

        $response = $this->actingAs($user)
            ->getJson(route('chat.conversations'))
            ->assertOk();

        $this->assertCount(1, $response->json('conversations'));
        $this->assertEquals('Mine', $response->json('conversations.0.title'));
    }

    public function test_cannot_access_another_users_conversation(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $conv = ChatConversation::create(['user_id' => $other->id, 'title' => 'Secret']);

        $this->actingAs($user)
            ->getJson(route('chat.conversations.show', $conv->id))
            ->assertNotFound();
    }

    public function test_rename_conversation(): void
    {
        $user = User::factory()->create();
        $conv = ChatConversation::create(['user_id' => $user->id, 'title' => 'Old title']);

        $this->actingAs($user)
            ->patchJson(route('chat.conversations.rename', $conv->id), ['title' => 'New title'])
            ->assertOk()
            ->assertJsonPath('conversation.title', 'New title');

        $this->assertDatabaseHas('chat_conversations', ['id' => $conv->id, 'title' => 'New title']);
    }

    public function test_delete_conversation_cascades_messages(): void
    {
        $user = User::factory()->create();
        $conv = ChatConversation::create(['user_id' => $user->id, 'title' => 'Temp']);
        ChatMessage::create(['conversation_id' => $conv->id, 'role' => 'user', 'content' => 'Hi']);
        ChatMessage::create(['conversation_id' => $conv->id, 'role' => 'assistant', 'content' => 'Hello']);

        $this->actingAs($user)
            ->deleteJson(route('chat.conversations.destroy', $conv->id))
            ->assertOk();

        $this->assertDatabaseMissing('chat_conversations', ['id' => $conv->id]);
        $this->assertDatabaseCount('chat_messages', 0);
    }

    public function test_clear_chat_resets_active_conversation(): void
    {
        $user = User::factory()->create();
        $conv = ChatConversation::create(['user_id' => $user->id, 'title' => 'Active']);
        session()->put('active_chat_conversation_'.$user->id, $conv->id);

        $this->actingAs($user)->postJson(route('chat.clear'))->assertOk();

        $this->assertNull(session()->get('active_chat_conversation_'.$user->id));
    }
}
