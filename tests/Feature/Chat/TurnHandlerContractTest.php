<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\User;
use App\Models\UserFile;
use App\Services\Chat\ChatTurnService;
use App\Services\Chat\Routing\Intent;
use App\Services\Chat\Turn\TurnHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The guarantees the handler split exists to provide.
 *
 * Splitting a branch chain into classes is only worth the churn if it buys
 * something reviewing the chain could not. These are the three things it buys:
 * every intent has a handler, no handler can spend a token, and the orchestrator
 * is the only thing that talks to the model.
 */
class TurnHandlerContractTest extends TestCase
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
                'choices' => [['message' => ['content' => 'An answer [1].']]],
                'usage' => ['prompt_tokens' => 900, 'completion_tokens' => 20],
            ]),
        ]);

        $this->user = User::factory()->student()->create();
    }

    /**
     * Every intent must resolve to a handler. Adding an enum case without
     * registering a handler would otherwise silently fall through to the
     * research pipeline — a question answered by the wrong path entirely.
     */
    public function test_every_intent_has_a_registered_handler(): void
    {
        $covered = [];

        foreach (app()->tagged('chat.handlers') as $handler) {
            /** @var TurnHandler $handler */
            foreach ($handler->handles() as $intent) {
                $covered[$intent->value] = true;
            }
        }

        foreach (Intent::cases() as $intent) {
            $this->assertArrayHasKey(
                $intent->value,
                $covered,
                "Intent::{$intent->name} has no handler — it would silently fall through to research QA."
            );
        }
    }

    /**
     * Handlers describe what should happen; they never do it. A handler that
     * returned an outcome with no prompt but had already called the model would
     * break the one-call-per-turn guarantee invisibly.
     */
    public function test_a_zero_token_intent_produces_an_outcome_with_no_prompt(): void
    {
        $conversation = ChatConversation::create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->postJson("/chat/conversations/{$conversation->id}/messages", ['message' => 'hi'])
            ->assertOk();

        Http::assertNothingSent();
    }

    public function test_a_turn_makes_at_most_one_llm_call(): void
    {
        UserFile::factory()->approved()->publicVisibility()->create([
            'title' => 'Seaweed Diversity in Gonzaga',
            'search_index_status' => 'ready',
        ]);

        $conversation = ChatConversation::create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->postJson("/chat/conversations/{$conversation->id}/messages", [
                'message' => 'list repository available',
            ])
            ->assertOk();

        Http::assertSentCount(1);
    }

    /**
     * ExternalLiterature is claimed by two handlers on purpose. With the
     * feature off, the question must still be ANSWERED from the local corpus
     * rather than refused — so the research handler is the documented fallback,
     * and registration order is what decides between them.
     */
    public function test_external_literature_falls_back_to_research_when_disabled(): void
    {
        config(['external_literature.enabled' => false]);

        $reflection = new \ReflectionClass(ChatTurnService::class);
        $method = $reflection->getMethod('resolveHandler');
        $method->setAccessible(true);

        $handler = $method->invoke(app(ChatTurnService::class), Intent::ExternalLiterature);

        $this->assertInstanceOf(
            \App\Services\Chat\Handlers\ResearchQaHandler::class,
            $handler,
            'With the external feature off, the question should be answered locally, not refused.'
        );
    }

    public function test_external_literature_uses_its_own_handler_when_enabled(): void
    {
        config([
            'external_literature.enabled' => true,
            'external_literature.driver' => 'openalex',
        ]);

        $reflection = new \ReflectionClass(ChatTurnService::class);
        $method = $reflection->getMethod('resolveHandler');
        $method->setAccessible(true);

        $handler = $method->invoke(app(ChatTurnService::class), Intent::ExternalLiterature);

        $this->assertInstanceOf(
            \App\Services\Chat\Handlers\ExternalLiteratureHandler::class,
            $handler,
        );
    }

    /**
     * The empty-catalog turn spends nothing but still consulted the database.
     * An operation that vanishes from cost reports is one nobody notices
     * breaking.
     */
    public function test_an_empty_catalog_turn_is_still_recorded_as_a_catalog_operation(): void
    {
        $conversation = ChatConversation::create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->postJson("/chat/conversations/{$conversation->id}/messages", [
                'message' => 'list repository available',
            ])
            ->assertOk();

        Http::assertNothingSent();

        $this->assertDatabaseHas('ai_usage_logs', [
            'operation' => 'catalog',
            'model' => 'none',
            'success' => true,
        ]);
    }
}
