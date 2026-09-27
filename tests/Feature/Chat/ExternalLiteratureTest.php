<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\User;
use App\Models\UserFile;
use App\Services\Chat\CitationBuilder;
use App\Services\Chat\ExternalCitationBuilder;
use App\Services\Chat\Routing\Intent;
use App\Services\Chat\Routing\IntentRouter;
use App\Services\External\ExternalSearchResult;
use App\Services\External\ExternalWork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * External literature: when it fires, and — mostly — when it must not.
 *
 * The negative assertions are the point of this file. The whole trust model of
 * this chatbot is "answer only from REPOSITORY EVIDENCE", and bolting on an
 * outside index is the change most capable of quietly undermining that. So the
 * tests that matter are the ones proving no outbound call happens on the paths
 * where it would be wrong, and that an outside paper can never be mistaken for
 * one of this repository's own.
 */
class ExternalLiteratureTest extends TestCase
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
     * Registered per-test rather than in setUp(): Http::fake() keeps the FIRST
     * matching stub for a URL, so a success fake in setUp() could never be
     * overridden by a test that needs a 503.
     */
    private function fakeOpenAlexSuccess(): void
    {
        Http::fake([
            'api.openalex.org/*' => Http::response(
                json_decode(file_get_contents(base_path('tests/Fixtures/openalex/works-tilapia.json')), true)
            ),
        ]);
    }

    private function enableExternal(): void
    {
        config([
            'external_literature.enabled' => true,
            'external_literature.driver' => 'openalex',
        ]);
    }

    private function conversation(?int $studFileId = null): ChatConversation
    {
        return ChatConversation::create([
            'user_id' => $this->user->id,
            'stud_file_id' => $studFileId,
        ]);
    }

    private function ask(ChatConversation $conversation, string $message)
    {
        return $this->actingAs($this->user)
            ->postJson("/chat/conversations/{$conversation->id}/messages", ['message' => $message]);
    }

    private function externalQuestion(): string
    {
        return 'are there published studies about tilapia salinity outside this repository';
    }

    // --- When it must NOT fire ------------------------------------------

    /**
     * The inert default. With the feature off the app must behave exactly as it
     * did before it existed — no outbound call at all.
     */
    public function test_nothing_is_requested_when_the_feature_is_disabled(): void
    {
        // Asserted on the service rather than through the endpoint: with the
        // feature off this question falls through to research QA, which needs
        // pgvector and cannot run on the sqlite test database. What matters
        // here is the inert default, and that is entirely this service's job.
        $service = app(\App\Services\External\ExternalLiteratureService::class);

        $this->assertFalse($service->isAvailable());
        $this->assertTrue($service->search('tilapia salinity')->isEmpty());

        Http::assertNothingSent();
    }

    /** Enabled, but with the null driver still bound — also inert. */
    public function test_the_null_driver_makes_no_request_even_when_enabled(): void
    {
        config(['external_literature.enabled' => true, 'external_literature.driver' => 'null']);

        $service = app(\App\Services\External\ExternalLiteratureService::class);

        $this->assertFalse($service->isAvailable());
        Http::assertNothingSent();
    }

    /**
     * A pinned chat is scoped to one paper. Searching the wider world from
     * inside it is off-purpose, and the topic sent outward could carry a
     * private paper's title off-site.
     */
    public function test_a_pinned_conversation_never_calls_out(): void
    {
        $this->enableExternal();

        $file = UserFile::factory()->approved()->publicVisibility()->create([
            'search_index_status' => 'ready',
        ]);

        $routed = app(IntentRouter::class)->route($this->externalQuestion(), isPinnedConversation: true);

        $this->assertNotSame(
            Intent::ExternalLiterature,
            $routed->intent,
            'A document-scoped chat must never trigger an outbound search.'
        );
    }

    /** A bare inventory question has no topic — it would query literal stopwords. */
    public function test_a_bare_inventory_question_is_not_sent_outward(): void
    {
        $this->enableExternal();

        $routed = app(IntentRouter::class)->route('list repository available');

        $this->assertNotSame(Intent::ExternalLiterature, $routed->intent);
    }

    public function test_an_ordinary_research_question_does_not_call_out(): void
    {
        $this->enableExternal();
        UserFile::factory()->approved()->publicVisibility()->create(['search_index_status' => 'ready']);

        $this->ask($this->conversation(), 'list repository available')->assertOk();

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'openalex.org'));
    }

    // --- When it does fire ----------------------------------------------

    public function test_an_explicit_request_returns_external_sources(): void
    {
        $this->enableExternal();
        $this->fakeOpenAlexSuccess();

        $response = $this->ask($this->conversation(), $this->externalQuestion());

        $response->assertOk();

        $sources = $response->json('assistant_message.external_sources');

        $this->assertNotEmpty($sources);
        $this->assertSame('E1', $sources[0]['n']);
        $this->assertSame('external', $sources[0]['source']);
        $this->assertSame('openalex', $sources[0]['provider']);

        // Internal citations stay empty — nothing from this repository was used.
        $this->assertSame([], $response->json('assistant_message.citations'));
    }

    /** Zero OpenAI tokens: the works are rendered, not passed through a model. */
    public function test_an_external_turn_costs_no_openai_tokens(): void
    {
        $this->enableExternal();
        $this->fakeOpenAlexSuccess();

        $response = $this->ask($this->conversation(), $this->externalQuestion());

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'api.openai.com'));
        $this->assertSame(0, $response->json('quota.used'));
    }

    public function test_the_answer_says_plainly_that_these_are_not_ours(): void
    {
        $this->enableExternal();
        $this->fakeOpenAlexSuccess();

        $content = $this->ask($this->conversation(), $this->externalQuestion())
            ->json('assistant_message.content');

        $this->assertStringContainsString('None of this is in this repository', $content);
    }

    public function test_the_lookup_is_recorded_in_usage_logs(): void
    {
        $this->enableExternal();
        $this->fakeOpenAlexSuccess();

        $this->ask($this->conversation(), $this->externalQuestion())->assertOk();

        $this->assertDatabaseHas('ai_usage_logs', [
            'operation' => 'external_search',
            'model' => 'openalex',
            'success' => true,
        ]);
    }

    public function test_a_provider_outage_degrades_without_breaking_the_turn(): void
    {
        $this->enableExternal();

        Http::fake(['api.openalex.org/*' => Http::response('down', 503)]);

        $response = $this->ask($this->conversation(), $this->externalQuestion());

        $response->assertOk();
        $this->assertStringContainsString("couldn't reach", $response->json('assistant_message.content'));
        $this->assertDatabaseHas('ai_usage_logs', [
            'operation' => 'external_search',
            'success' => false,
        ]);
    }

    // --- The separation ---------------------------------------------------

    /**
     * The single most important assertion in this subsystem.
     *
     * CitationBuilder parses internal markers with /\[(\d+)\]/, which cannot
     * match "[E1]". So even when a model emits both, an external reference is
     * structurally invisible to internal citation parsing and an internal
     * number can never be re-pointed at an outside paper. This pins that
     * property rather than trusting it.
     */
    public function test_external_markers_can_never_become_internal_citations(): void
    {
        $built = app(CitationBuilder::class)->build(
            'See [E1] and [1] and [E9].',
            [], // no internal evidence at all
            collect(),
        );

        // [1] is a hallucinated internal marker and is stripped...
        $this->assertStringNotContainsString('[1]', $built['text']);
        $this->assertSame([], $built['citations']);

        // ...while the E-prefixed markers are left entirely alone.
        $this->assertStringContainsString('[E1]', $built['text']);
        $this->assertStringContainsString('[E9]', $built['text']);
    }

    public function test_external_sources_are_numbered_with_an_e_prefix(): void
    {
        $result = new ExternalSearchResult(
            works: [
                new ExternalWork(provider: 'openalex', externalId: 'W1', title: 'First paper'),
                new ExternalWork(provider: 'openalex', externalId: 'W2', title: 'Second paper'),
            ],
            provider: 'openalex',
        );

        [$text, $sources] = app(ExternalCitationBuilder::class)->build($result);

        $this->assertSame(['E1', 'E2'], array_column($sources, 'n'));
        $this->assertStringContainsString('[E1]', $text);
        $this->assertStringContainsString('[E2]', $text);

        // Never a bare number, which would collide with internal citations.
        $this->assertDoesNotMatchRegularExpression('/\[\d+\]/', $text);
    }

    public function test_an_empty_external_result_is_reported_honestly(): void
    {
        [$text, $sources] = app(ExternalCitationBuilder::class)
            ->build(new ExternalSearchResult(provider: 'openalex'));

        $this->assertSame([], $sources);
        $this->assertStringContainsString('found nothing', $text);
    }

    /**
     * "We could not reach the index" and "the index holds nothing" look
     * identical in an empty array but call for opposite messages.
     */
    public function test_a_degraded_result_is_distinguished_from_an_empty_one(): void
    {
        [$degradedText] = app(ExternalCitationBuilder::class)
            ->build(ExternalSearchResult::degraded('openalex', 'tilapia'));

        [$emptyText] = app(ExternalCitationBuilder::class)
            ->build(new ExternalSearchResult(provider: 'openalex'));

        $this->assertNotSame($degradedText, $emptyText);
        $this->assertStringContainsString("couldn't reach", $degradedText);
    }

    public function test_external_sources_survive_a_history_replay(): void
    {
        $this->enableExternal();
        $this->fakeOpenAlexSuccess();

        $conversation = $this->conversation();
        $this->ask($conversation, $this->externalQuestion())->assertOk();

        $response = $this->actingAs($this->user)
            ->getJson("/chat/conversations/{$conversation->id}");

        $response->assertOk();

        $assistant = collect($response->json('messages'))->firstWhere('role', 'assistant');

        $this->assertNotEmpty(
            $assistant['external_sources'],
            'Replaying history must not silently drop the external cards the user was shown.'
        );
    }
}
