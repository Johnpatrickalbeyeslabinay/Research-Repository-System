<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\User;
use App\Models\UserFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The chat endpoint end to end, with OpenAI faked. Everything else in this
 * suite exercises services directly; only this reaches ChatController's
 * branch wiring, the quota accounting and the JSON contract the widget
 * depends on — which is where an undefined variable or a mis-ordered
 * branch would actually surface.
 */
class ChatEndpointTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.openai.api_key' => 'test-key']);

        Http::preventStrayRequests();
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'Here is what the repository holds [1].']]],
                'usage' => ['prompt_tokens' => 900, 'completion_tokens' => 20],
            ]),
        ]);

        $this->user = User::factory()->student()->create();
    }

    private function ask(ChatConversation $conversation, string $message)
    {
        return $this->actingAs($this->user)
            ->postJson("/chat/conversations/{$conversation->id}/messages", ['message' => $message]);
    }

    private function conversation(): ChatConversation
    {
        return ChatConversation::create(['user_id' => $this->user->id]);
    }

    public function test_a_catalog_question_answers_from_metadata_with_a_citation(): void
    {
        UserFile::factory()->approved()->publicVisibility()->create([
            'title' => 'Seaweed Diversity in Gonzaga',
            'search_index_status' => 'ready',
        ]);

        $response = $this->ask($this->conversation(), 'list repository available');

        $response->assertOk()
            ->assertJsonPath('assistant_message.citations.0.n', 1)
            ->assertJsonPath('assistant_message.citations.0.title', 'Seaweed Diversity in Gonzaga');

        // Catalog turns send document metadata, never chunk prose. Checked
        // on the final user turn specifically: the system prompt names both
        // blocks in its rules, so searching the whole payload proves
        // nothing.
        Http::assertSent(function ($request) {
            $messages = $request->data()['messages'];
            $userTurn = end($messages)['content'];

            return str_contains($userTurn, 'BEGIN REPOSITORY CATALOG')
                && ! str_contains($userTurn, 'BEGIN REPOSITORY EVIDENCE');
        });
    }

    public function test_an_empty_repository_answers_without_calling_the_llm_or_spending_quota(): void
    {
        $response = $this->ask($this->conversation(), 'list repository available');

        $response->assertOk();

        $this->assertStringContainsString(
            "doesn't have any research you can access",
            $response->json('assistant_message.content')
        );

        // The two properties that make this path worth having.
        Http::assertNothingSent();
        $this->assertSame(0, $response->json('quota.used'));
    }

    public function test_a_greeting_costs_nothing(): void
    {
        $response = $this->ask($this->conversation(), 'hi');

        $response->assertOk();
        Http::assertNothingSent();
        $this->assertSame(0, $response->json('quota.used'));
    }

    public function test_a_catalog_answer_does_spend_quota(): void
    {
        UserFile::factory()->approved()->publicVisibility()->create(['search_index_status' => 'ready']);

        $response = $this->ask($this->conversation(), 'list repository available');

        $this->assertSame(1, $response->json('quota.used'));
    }

    public function test_a_catalog_turn_records_what_is_in_scope_for_the_next_question(): void
    {
        $file = UserFile::factory()->approved()->publicVisibility()->create([
            'title' => 'Seaweed Diversity in Gonzaga',
            'search_index_status' => 'ready',
        ]);

        $conversation = $this->conversation();
        $this->ask($conversation, 'list repository available')->assertOk();

        $context = $conversation->fresh()->active_context;

        $this->assertSame([$file->id], $context['research_ids']);
        $this->assertSame('Seaweed Diversity in Gonzaga', $context['documents'][$file->id]);
    }

    public function test_a_conversation_belonging_to_someone_else_is_not_reachable(): void
    {
        $theirs = ChatConversation::create(['user_id' => User::factory()->student()->create()->id]);

        $this->ask($theirs, 'list repository available')->assertNotFound();
    }

    public function test_pinning_a_conversation_to_inaccessible_research_is_refused(): void
    {
        $private = UserFile::factory()->pending()->create([
            'user_id' => User::factory()->student()->create()->id,
        ]);

        $this->actingAs($this->user)
            ->postJson('/chat/conversations', ['stud_file_id' => $private->id])
            ->assertForbidden();
    }

    public function test_chat_requires_authentication(): void
    {
        $conversation = $this->conversation();

        $this->postJson("/chat/conversations/{$conversation->id}/messages", ['message' => 'hello'])
            ->assertUnauthorized();
    }
}
