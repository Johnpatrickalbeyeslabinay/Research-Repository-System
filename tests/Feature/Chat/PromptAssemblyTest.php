<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\User;
use App\Models\UserFile;
use App\Services\Chat\PromptBuilder;
use App\Services\Retrieval\CatalogResult;
use App\Services\Retrieval\RetrievalResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * What actually goes into the payload.
 *
 * These are regression tests for waste that was invisible from outside: the
 * prompt still produced correct answers, it just paid for the same text
 * twice and carried forward content no later turn could use. Nothing in the
 * existing suite would have caught any of it, because every assertion was
 * about the answer rather than the request.
 *
 * Two constraints shape how these are written:
 *
 *  - The research-QA path cannot run here. It needs pgvector's <=> operator
 *    and a tsvector column, and phpunit.xml points at sqlite, where the
 *    embedding column does not even exist (those migrations are
 *    pgsql-guarded). So these drive the CATALOG and PLATFORM paths, which
 *    reach the LLM without vector search — the same constraint
 *    ChatEndpointTest already works within. Catalog questions are phrased as
 *    bare inventory ("list repository available") to stay on
 *    CatalogService's structured arm and off its semantic one.
 *
 *  - Magnitude cannot be asserted. Http::fake() returns whatever usage
 *    numbers we write, so real prompt_tokens exist only against the live
 *    API. These assert STRUCTURE; the measured saving comes from
 *    `php artisan research:eval-chat --baseline`.
 */
class PromptAssemblyTest extends TestCase
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
                'choices' => [['message' => ['content' => 'An answer.']]],
                'usage' => ['prompt_tokens' => 900, 'completion_tokens' => 20],
            ]),
        ]);

        $this->user = User::factory()->student()->create();
    }

    private function conversation(): ChatConversation
    {
        return ChatConversation::create(['user_id' => $this->user->id]);
    }

    private function ask(ChatConversation $conversation, string $message)
    {
        return $this->actingAs($this->user)
            ->postJson("/chat/conversations/{$conversation->id}/messages", ['message' => $message]);
    }

    private function seedResearch(string $title = 'Seaweed Diversity in Gonzaga'): UserFile
    {
        return UserFile::factory()->approved()->publicVisibility()->create([
            'title' => $title,
            'search_index_status' => 'ready',
        ]);
    }

    /** Message contents of the most recent outgoing chat-completions payload. */
    private function lastPromptContents(): array
    {
        $contents = [];

        foreach (Http::recorded() as [$request]) {
            if (str_contains($request->url(), 'chat/completions')) {
                $contents = array_column($request->data()['messages'], 'content');
            }
        }

        return $contents;
    }

    /**
     * The headline Phase 1 bug. ChatController persists the user's message
     * before building the prompt (so a provider timeout cannot lose it —
     * that ordering is deliberate), and trimmedHistory() then picked that
     * very row up as the newest history entry, while build() also appended
     * it as "Question: ...".
     */
    public function test_the_current_question_is_sent_exactly_once(): void
    {
        $this->seedResearch();

        $question = 'list repository available';
        $this->ask($this->conversation(), $question)->assertOk();

        $occurrences = 0;

        foreach ($this->lastPromptContents() as $content) {
            $occurrences += substr_count($content, $question);
        }

        $this->assertSame(
            1,
            $occurrences,
            'The question appeared in the payload more than once — trimmedHistory() is picking up the row being answered.'
        );
    }

    /**
     * The same bug's second cost: the duplicate also consumed one of the
     * configured history slots, so real conversational reach was a turn
     * shorter than config/chat.php advertises.
     */
    public function test_the_current_turn_does_not_consume_a_history_slot(): void
    {
        config(['chat.context.history_messages' => 2]);
        $this->seedResearch();

        $conversation = $this->conversation();

        $this->ask($conversation, 'what research is available')->assertOk();
        $this->ask($conversation, 'list repository available')->assertOk();

        $contents = $this->lastPromptContents();

        // system + 2 history + the current user turn. Before the fix the
        // current question displaced the oldest history entry.
        $this->assertCount(4, $contents);
        $this->assertStringContainsString('what research is available', $contents[1]);
    }

    public function test_greeting_turns_never_reach_a_later_prompt(): void
    {
        $this->seedResearch();
        $conversation = $this->conversation();

        $this->ask($conversation, 'hi')->assertOk();
        $this->ask($conversation, 'thanks')->assertOk();
        $this->ask($conversation, 'list repository available')->assertOk();

        $contents = $this->lastPromptContents();

        // Both the canned replies and the bare "hi"/"thanks" user turns are
        // excluded — everything after the system message should be the real
        // question alone.
        $afterSystem = implode("\n", array_slice($contents, 1));

        $this->assertStringNotContainsString('Ask me anything', $afterSystem);
        $this->assertStringNotContainsString("You're welcome", $afterSystem);
        $this->assertStringNotContainsString('thanks', $afterSystem);
        $this->assertCount(2, $contents, 'A greeting exchange still occupied history slots.');
    }

    /**
     * The "nothing matched" reply is a worked example of declining. Left in
     * the history window it makes the model likelier to decline again, so
     * one failed lookup degrades the turns after it.
     */
    public function test_the_empty_catalog_reply_never_reaches_a_later_prompt(): void
    {
        $conversation = $this->conversation();

        // Empty corpus: this turn takes the catalog_empty path and spends
        // nothing at all.
        $this->ask($conversation, 'list repository available')->assertOk();
        Http::assertNothingSent();

        $this->seedResearch();
        $this->ask($conversation, 'what research is available')->assertOk();

        foreach ($this->lastPromptContents() as $content) {
            $this->assertStringNotContainsString('no research', mb_strtolower($content));
        }
    }

    /** Exclusion is prompt-only: the user still sees their own transcript. */
    public function test_excluded_messages_are_still_returned_to_the_widget(): void
    {
        $conversation = $this->conversation();

        $this->ask($conversation, 'hi')->assertOk();

        $response = $this->actingAs($this->user)
            ->getJson("/chat/conversations/{$conversation->id}");

        $response->assertOk()->assertJsonCount(2, 'messages');
        $this->assertSame('hi', $response->json('messages.0.content'));
    }

    /**
     * The system message must stay byte-identical across every prompt
     * variant. NOT for prompt caching — the shared prefix measures ~1,000
     * tokens, right at OpenAI's 1,024-token minimum, so the short turns this
     * plan optimises toward often qualify for no discount at all. The reason
     * is auditability: one system prompt is one place to verify the trust
     * model ("answer ONLY from REPOSITORY EVIDENCE", and rule 7's
     * data-not-instructions boundary), and forking it per intent is exactly
     * how a security rule goes silently missing from one path.
     */
    public function test_the_system_message_is_identical_across_every_builder(): void
    {
        $builder = app(PromptBuilder::class);
        $conversation = $this->conversation();

        $research = $builder->build(
            $conversation,
            new RetrievalResult(chunks: [], files: collect(), filters: [], isComparison: false, relaxedFilters: false),
            'a question',
        );
        $platform = $builder->buildPlatformOnly($conversation, 'a question');
        $catalog = $builder->buildCatalog(
            $conversation,
            new CatalogResult(documents: collect(), total: 0, filters: []),
            'a question',
        );

        $this->assertSame('system', $research['messages'][0]['role']);
        $this->assertSame($research['messages'][0]['content'], $platform['messages'][0]['content']);
        $this->assertSame($research['messages'][0]['content'], $catalog['messages'][0]['content']);
    }

    /**
     * Someone will eventually want the user's name or today's date in the
     * system prompt. That would break the audit story above and make the
     * prefix different on every single call.
     */
    public function test_the_system_message_contains_nothing_variable(): void
    {
        $builder = app(PromptBuilder::class);

        $first = $builder->buildPlatformOnly($this->conversation(), 'question one');

        $otherUser = User::factory()->student()->create();
        $otherConversation = ChatConversation::create(['user_id' => $otherUser->id]);

        $this->travel(3)->days();
        $second = $builder->buildPlatformOnly($otherConversation, 'a completely different question');

        $this->assertSame(
            $first['messages'][0]['content'],
            $second['messages'][0]['content'],
            'The system message varied by user, conversation or date — it must be a constant.'
        );
    }

    /**
     * Driven through PromptBuilder directly rather than the HTTP endpoint:
     * routing a question long enough to test truncation lands on a retrieval
     * path that needs pgvector, and the assertion here is purely about how
     * history is rendered.
     */
    public function test_long_prior_questions_are_truncated_in_history(): void
    {
        config(['chat.context.max_history_user_chars' => 50]);

        $conversation = $this->conversation();
        $longQuestion = str_repeat('about seaweed ponds and fish ', 20);

        $conversation->messages()->create(['role' => 'user', 'content' => $longQuestion]);
        $conversation->messages()->create(['role' => 'assistant', 'content' => 'A prior answer.']);

        $current = $conversation->messages()->create(['role' => 'user', 'content' => 'and the salinity']);

        $messages = app(PromptBuilder::class)
            ->buildPlatformOnly($conversation, 'and the salinity', $current->id)['messages'];

        $history = $messages[1]['content'];

        // Capped at 50 plus the ellipsis...
        $this->assertLessThan(60, mb_strlen($history));
        $this->assertStringEndsWith('…', $history);

        // ...while the CURRENT question is never truncated.
        $this->assertStringContainsString('and the salinity', end($messages)['content']);
    }

    public function test_the_active_context_document_list_is_capped(): void
    {
        config(['chat.context.max_context_documents' => 2]);
        $this->seedResearch();

        $conversation = $this->conversation();
        $conversation->active_context = [
            'topic' => 'tilapia',
            'documents' => [
                1 => 'First Study About Tilapia Growth Rates',
                2 => 'Second Study About Pond Salinity Levels',
                3 => 'Third Study That Should Not Appear',
                4 => 'Fourth Study That Should Not Appear Either',
            ],
        ];
        $conversation->save();

        $this->ask($conversation, 'list repository available')->assertOk();

        $contents = $this->lastPromptContents();
        $userTurn = end($contents);

        $this->assertStringContainsString('First Study', $userTurn);
        $this->assertStringNotContainsString('Third Study', $userTurn);
        $this->assertStringNotContainsString('Fourth Study', $userTurn);
    }

    public function test_very_long_context_titles_are_truncated(): void
    {
        config(['chat.context.max_context_title_chars' => 20]);
        $this->seedResearch();

        $conversation = $this->conversation();
        $conversation->active_context = [
            'topic' => 'tilapia',
            'documents' => [1 => 'An Extremely Long Research Title That Would Otherwise Cost Tokens Every Turn'],
        ];
        $conversation->save();

        $this->ask($conversation, 'list repository available')->assertOk();

        $contents = $this->lastPromptContents();
        $userTurn = end($contents);

        $this->assertStringContainsString('An Extremely Long', $userTurn);
        $this->assertStringNotContainsString('Cost Tokens Every Turn', $userTurn);
    }
}
