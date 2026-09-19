<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\User;
use App\Models\UserFile;
use App\Services\Chat\ConversationContext;
use App\Services\Chat\QueryCondenser;
use App\Services\Retrieval\QueryPlanner;
use App\Services\Retrieval\RetrievalResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The chatbot's multi-turn memory. Three collaborating pieces have to
 * agree for a follow-up to work, and each was independently broken:
 *
 *   QueryCondenser   rewrites "which one?" so RETRIEVAL sees the subject
 *   QueryPlanner     narrows the scope to the documents under discussion
 *   ConversationContext  carries the subject forward instead of letting a
 *                        pronoun overwrite it
 *
 * Covered here without any API call — the deterministic paths are the ones
 * that run for virtually every real follow-up.
 */
class ConversationContinuityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private UserFile $fileA;

    private UserFile $fileB;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->user = User::factory()->student()->create();
        $this->fileA = UserFile::factory()->approved()->publicVisibility()->create(['title' => 'Seaweed Diversity in Gonzaga']);
        $this->fileB = UserFile::factory()->approved()->publicVisibility()->create(['title' => 'Tilapia Growth Rates']);
    }

    private function conversation(): ChatConversation
    {
        return ChatConversation::create(['user_id' => $this->user->id]);
    }

    private function retrievalResult(array $fileIds, array $filterOverrides = []): RetrievalResult
    {
        $files = UserFile::whereIn('id', $fileIds)->get()->keyBy('id');

        $chunks = array_map(fn ($id) => [
            'id' => $id * 100,
            'stud_file_id' => $id,
            'section' => 'abstract',
            'page_number' => 1,
            'content' => 'chunk content',
            'similarity' => 0.5,
            'weighted_score' => 0.55,
        ], $fileIds);

        return new RetrievalResult(
            chunks: $chunks,
            files: $files,
            filters: array_merge([
                'topic' => 'seaweed diversity',
                'year_from' => null, 'year_to' => null, 'sdg_numbers' => [],
                'author' => null, 'research_type' => null, 'stud_file_ids' => [],
                'is_comparison' => false, 'is_broad' => false,
            ], $filterOverrides),
            isComparison: false,
            relaxedFilters: false,
        );
    }

    public function test_a_follow_up_keeps_the_prior_topic_instead_of_overwriting_it_with_a_pronoun(): void
    {
        $context = app(ConversationContext::class);
        $conversation = $this->conversation();

        $context->update($conversation, $this->retrievalResult([$this->fileA->id]));
        $this->assertSame('seaweed diversity', $conversation->active_context['topic']);

        // Turn two: the planner's topic for "which one used quadrats?" is
        // the pronoun itself. Writing that over the real subject is what
        // made turn three lose the thread entirely.
        $context->update(
            $conversation,
            $this->retrievalResult([$this->fileA->id], ['topic' => 'which one used quadrats']),
            isFollowUp: true
        );

        $this->assertSame('seaweed diversity', $conversation->fresh()->active_context['topic']);
    }

    public function test_a_fresh_question_does_replace_the_topic(): void
    {
        $context = app(ConversationContext::class);
        $conversation = $this->conversation();

        $context->update($conversation, $this->retrievalResult([$this->fileA->id]));
        $context->update(
            $conversation,
            $this->retrievalResult([$this->fileB->id], ['topic' => 'tilapia growth']),
            isFollowUp: false
        );

        $this->assertSame('tilapia growth', $conversation->fresh()->active_context['topic']);
    }

    public function test_documents_accumulate_across_turns_so_a_comparison_can_span_them(): void
    {
        $context = app(ConversationContext::class);
        $conversation = $this->conversation();

        $context->update($conversation, $this->retrievalResult([$this->fileA->id]));
        $context->update($conversation, $this->retrievalResult([$this->fileB->id], ['topic' => 'tilapia growth']));

        $active = $conversation->fresh()->active_context;

        // Only the latest turn's document is the tight scope...
        $this->assertSame([$this->fileB->id], $active['research_ids']);

        // ...but both remain reachable, which is what makes "compare these
        // two studies" work when they were surfaced in different turns.
        $this->assertEqualsCanonicalizing(
            [$this->fileA->id, $this->fileB->id],
            $active['recent_research_ids']
        );

        $this->assertSame('Seaweed Diversity in Gonzaga', $active['documents'][$this->fileA->id]);
        $this->assertSame('Tilapia Growth Rates', $active['documents'][$this->fileB->id]);
    }

    public function test_a_no_evidence_turn_leaves_prior_context_intact(): void
    {
        $context = app(ConversationContext::class);
        $conversation = $this->conversation();

        $context->update($conversation, $this->retrievalResult([$this->fileA->id]));
        $context->update($conversation, $this->retrievalResult([]));

        $this->assertSame('seaweed diversity', $conversation->fresh()->active_context['topic']);
    }

    public function test_planner_scopes_a_back_reference_to_the_documents_under_discussion(): void
    {
        $plan = app(QueryPlanner::class)->plan(
            'what methodology did it use?',
            ['topic' => 'seaweed diversity', 'research_ids' => [$this->fileA->id]],
            null
        );

        $this->assertSame([$this->fileA->id], $plan['stud_file_ids']);
    }

    public function test_a_back_reference_carrying_a_filter_narrows_within_the_prior_scope(): void
    {
        // The original rule abandoned the scope entirely as soon as any
        // filter appeared, so "of those, which are from 2026?" silently
        // re-searched the whole corpus by year — the opposite of the ask.
        $plan = app(QueryPlanner::class)->plan(
            'of those, which ones are from 2026?',
            ['topic' => 'seaweed diversity', 'research_ids' => [$this->fileA->id, $this->fileB->id]],
            null
        );

        $this->assertSame([$this->fileA->id, $this->fileB->id], $plan['stud_file_ids']);
        $this->assertSame(2026, $plan['year_from']);
    }

    public function test_a_fresh_question_is_not_scoped_to_prior_documents(): void
    {
        $plan = app(QueryPlanner::class)->plan(
            'What methodologies were used in tilapia aquaculture studies?',
            ['topic' => 'seaweed diversity', 'research_ids' => [$this->fileA->id]],
            null
        );

        $this->assertSame([], $plan['stud_file_ids']);
    }

    public function test_a_pinned_conversation_always_scopes_to_its_own_document(): void
    {
        $plan = app(QueryPlanner::class)->plan(
            'what methodology did it use?',
            ['topic' => 'seaweed diversity', 'research_ids' => [$this->fileB->id]],
            $this->fileA->id
        );

        $this->assertSame([$this->fileA->id], $plan['stud_file_ids']);
    }

    public function test_the_condenser_and_planner_agree_on_what_counts_as_a_follow_up(): void
    {
        // They share BACK_REFERENCE_PATTERN precisely so a question can't
        // be rewritten for search but left unscoped, or vice versa.
        $condenser = app(QueryCondenser::class);
        $planner = app(QueryPlanner::class);
        $context = ['topic' => 'seaweed diversity', 'research_ids' => [$this->fileA->id]];

        foreach (['which one used quadrats?', 'summarize this research', 'tell me more', 'why?'] as $question) {
            $this->assertTrue(
                $condenser->condense($question, $context)['is_follow_up'],
                "Condenser missed the follow-up: \"{$question}\""
            );

            $this->assertNotEmpty(
                $planner->plan($question, $context, null)['stud_file_ids'],
                "Planner did not scope the follow-up: \"{$question}\""
            );
        }
    }
}
