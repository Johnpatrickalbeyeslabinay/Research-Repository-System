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

    /**
     * A TOPICAL catalog question used to embed its topic before counting,
     * so an empty repository still cost an embedding call — and failed
     * outright whenever the embedding provider was down, for an answer that
     * needs no AI at all.
     */
    public function test_a_topical_question_against_an_empty_repository_makes_no_api_call(): void
    {
        $response = $this->ask($this->conversation(), 'What research is available about artificial intelligence?');

        $response->assertOk();
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

    /**
     * The greetings the old anchored regex missed. Each of these used to cost
     * a query embedding plus a ~2,500-token answer call, to reply that the
     * evidence did not cover a greeting. Tagalog and Ilocano are ordinary
     * input here — the users are at CSU Aparri in Cagayan.
     *
     * @return array<string, array{0: string}>
     */
    public static function freeSmallTalkMessages(): array
    {
        return [
            'greeting with filler' => ['hi there'],
            'greeting with a particle' => ['hello po'],
            'greeting with an honorific' => ['good day sir'],
            'a typo' => ['helo'],
            'repeated letters' => ['hiii'],
            'tagalog' => ['kumusta'],
            'tagalog time of day' => ['magandang umaga'],
            'ilocano' => ['naimbag nga bigat'],
            'tagalog thanks' => ['salamat'],
            'a farewell' => ['see you'],
            'gibberish' => ['qwrtzxcvb'],
            'punctuation only' => ['???'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('freeSmallTalkMessages')]
    public function test_small_talk_never_reaches_the_api(string $message): void
    {
        $response = $this->ask($this->conversation(), $message);

        $response->assertOk();
        Http::assertNothingSent();
        $this->assertSame(
            0,
            $response->json('quota.used'),
            "\"{$message}\" spent quota — it should be handled without any API call."
        );
        $this->assertNotEmpty($response->json('assistant_message.content'));
    }

    /**
     * The regression that matters most in loosening greeting detection: a
     * greeting PREFIX on a real question must still be answered as the
     * question, never with a bare hello.
     */
    public function test_a_greeting_prefix_still_answers_the_real_question(): void
    {
        UserFile::factory()->approved()->publicVisibility()->create([
            'title' => 'Seaweed Diversity in Gonzaga',
            'search_index_status' => 'ready',
        ]);

        $response = $this->ask($this->conversation(), 'hi, list repository available');

        $response->assertOk()
            ->assertJsonPath('assistant_message.citations.0.title', 'Seaweed Diversity in Gonzaga');

        // It reached the LLM, i.e. it was treated as a real question.
        $this->assertSame(1, $response->json('quota.used'));
    }

    /**
     * The three intents added by the routing refactor. The first two were
     * outright bugs: both used to reach the API and spend a real answer call.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function newFreeIntents(): array
    {
        return [
            'identity' => ['who are you?', 'identity'],
            'identity chatgpt' => ['are you chatgpt', 'identity'],
            'capability' => ['what can you do?', 'identity'],
            'off topic restaurant' => ['recommend a good restaurant', 'out_of_scope'],
            'off topic weather' => ['what is the weather today', 'out_of_scope'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('newFreeIntents')]
    public function test_the_new_zero_token_intents_never_reach_the_api(string $message, string $expectedIntent): void
    {
        $response = $this->ask($this->conversation(), $message);

        $response->assertOk();
        Http::assertNothingSent();
        $this->assertSame(
            0,
            $response->json('quota.used'),
            "\"{$message}\" spent quota — it should be answered from a constant."
        );

        $meta = \App\Models\ChatMessage::where('role', 'assistant')->latest('id')->first()->retrieval_meta;
        $this->assertSame($expectedIntent, $meta['intent']);
    }

    /**
     * The count shortcut. Its answer is CatalogResult::$total, which PHP
     * already holds — but it must still go through CatalogService so the
     * number respects ResearchAccess.
     */
    public function test_a_bare_count_question_is_answered_without_the_llm(): void
    {
        UserFile::factory()->count(3)->approved()->publicVisibility()->create([
            'search_index_status' => 'ready',
        ]);

        $response = $this->ask($this->conversation(), 'how many studies are in the repository?');

        $response->assertOk();
        Http::assertNothingSent();
        $this->assertSame(0, $response->json('quota.used'));
        $this->assertStringContainsString('3', $response->json('assistant_message.content'));
    }

    /**
     * The count must be the asking user's count, never the table size —
     * otherwise it leaks how much private research exists.
     */
    public function test_the_count_respects_permissions(): void
    {
        UserFile::factory()->approved()->publicVisibility()->create(['search_index_status' => 'ready']);

        // Another student's private, unapproved work: invisible to this user.
        UserFile::factory()->count(4)->pending()->create([
            'user_id' => User::factory()->student()->create()->id,
            'search_index_status' => 'ready',
        ]);

        $response = $this->ask($this->conversation(), 'how many studies are in the repository?');

        $content = $response->json('assistant_message.content');

        $this->assertStringContainsString('1 research document', $content);
        $this->assertStringNotContainsString('5', $content);
    }

    /**
     * The structural guarantee the single call site buys.
     *
     * Three branches used to call ChatAnswerService, each with its own
     * try/catch and its own usage-log writes. Reviewing that chain could tell
     * you no branch called twice; it could not stop a fourth branch from being
     * added that did. There is now exactly one caller, and this asserts the
     * consequence.
     */
    public function test_a_turn_makes_exactly_one_llm_call(): void
    {
        UserFile::factory()->approved()->publicVisibility()->create([
            'title' => 'Seaweed Diversity in Gonzaga',
            'search_index_status' => 'ready',
        ]);

        $this->ask($this->conversation(), 'list repository available')->assertOk();

        Http::assertSentCount(1);
    }

    /**
     * A provider outage is reported the same way from every path, and — this is
     * the part worth pinning — the user's question survives it. The message is
     * persisted before any API call precisely so a 503 costs them their answer
     * and not their typing.
     */
    public function test_a_provider_failure_returns_503_and_keeps_the_question(): void
    {
        // An unset key makes ChatAnswerService throw notConfigured(), which is
        // the same ChatUnavailableException a provider outage produces and
        // therefore the same path through callAnswerer's catch. Chosen over a
        // 500 response fake because setUp() has already registered a success
        // stub for this URL and Http::fake() keeps the first matching stub —
        // a second fake here would silently not apply.
        config(['services.openai.api_key' => '']);

        UserFile::factory()->approved()->publicVisibility()->create(['search_index_status' => 'ready']);

        $conversation = $this->conversation();
        $this->ask($conversation, 'list repository available')->assertStatus(503);

        $this->assertSame(
            'list repository available',
            $conversation->messages()->where('role', 'user')->first()->content
        );

        // The failure is still accounted for, with its error code.
        $this->assertDatabaseHas('ai_usage_logs', [
            'operation' => 'catalog',
            'success' => false,
        ]);
    }

    /**
     * The two questions from the bug report. Both need a query embedding
     * before any answer call, and with a revoked key that embedding used to
     * throw a bare RuntimeException — a 500 whose message, under APP_DEBUG,
     * was OpenAI's raw error JSON rendered into the chat bubble.
     *
     * @return array<string, array{0: string}>
     */
    public static function questionsThatNeedAnEmbedding(): array
    {
        return [
            'topical catalog question' => ['What research is available about artificial intelligence?'],
            'research question' => ['Which studies support SDG 4?'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('questionsThatNeedAnEmbedding')]
    public function test_a_rejected_embedding_key_returns_the_friendly_503(string $question): void
    {
        Http::fake([
            'api.openai.com/v1/embeddings' => Http::response([
                'error' => [
                    'message' => 'Incorrect API key provided: sk-proj-****SYcA.',
                    'code' => 'invalid_api_key',
                ],
            ], 401),
        ]);

        // Tagged SDG 4 so the SDG question has something to rank — an empty
        // match is answered before any embedding is attempted.
        $file = UserFile::factory()->approved()->publicVisibility()->create(['search_index_status' => 'ready']);
        $file->sdgs()->attach(\App\Models\Sdg::create(['number' => 4]));

        $conversation = $this->conversation();
        $response = $this->ask($conversation, $question);

        $response->assertStatus(503)
            ->assertJsonPath('message', 'The AI research assistant is temporarily unavailable. Please try again in a moment.');
        $this->assertStringNotContainsString('sk-proj', $response->getContent());
        $this->assertStringNotContainsString('invalid_api_key', $response->getContent());

        // The question survives, and the outage is accounted for.
        $this->assertSame($question, $conversation->messages()->where('role', 'user')->first()->content);
        $this->assertDatabaseHas('ai_usage_logs', [
            'operation' => 'query_embedding',
            'success' => false,
            'error_code' => 'EmbeddingFailedException',
        ]);

        // No answer call was attempted on top of the failed embedding.
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'chat/completions'));
    }

    public function test_consecutive_greetings_do_not_repeat_the_same_reply(): void
    {
        $conversation = $this->conversation();

        $first = $this->ask($conversation, 'hi')->json('assistant_message.content');
        $second = $this->ask($conversation, 'hello')->json('assistant_message.content');

        $this->assertNotSame(
            $first,
            $second,
            'Two greetings in a row returned an identical reply, which reads as a broken bot.'
        );
        Http::assertNothingSent();
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
