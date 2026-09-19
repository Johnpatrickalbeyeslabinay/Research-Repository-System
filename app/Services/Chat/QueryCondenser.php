<?php

namespace App\Services\Chat;

use App\Models\AiUsageLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Rewrites a conversational follow-up into a standalone search query.
 *
 * Why: retrieval embeds the question text. "which one used CNN?" embeds to
 * a vector about the words "which one" and "CNN" with no notion of what
 * "one" refers to, so it retrieves against the wrong region of the corpus
 * (or nothing at all). This is the classic RAG statelessness failure — the
 * chatbot appears to forget the conversation not because history is
 * missing from the prompt, but because RETRIEVAL never saw it. Fixing the
 * prompt alone cannot fix it: the right chunks were never fetched.
 *
 * Deterministic first, LLM only as a bounded fallback. Prefixing the
 * previous topic resolves the overwhelming majority of real follow-ups for
 * zero tokens ("rice disease detection which one used CNN"), which is both
 * cheaper and more predictable than asking a model to do it. The LLM path
 * exists for the genuinely ambiguous remainder and is capped at 40 output
 * tokens.
 *
 * CRITICAL: the rewritten text is used ONLY for embedding and lexical
 * search. The user's literal question is still what the answer prompt
 * sees — this must never silently answer a question the user didn't ask.
 */
class QueryCondenser
{
    /**
     * A question this short is almost certainly leaning on prior context
     * rather than standing on its own.
     */
    private const SHORT_QUESTION_WORDS = 8;

    /**
     * Pronouns and elliptical openers that only mean something relative to
     * an earlier turn. Broader than QueryPlanner::BACK_REFERENCE_PATTERN
     * was, which missed the most common real phrasings — "this research",
     * "summarize it", "why?" — and so left those follow-ups searching the
     * whole corpus with a meaningless vector.
     */
    public const BACK_REFERENCE_PATTERN = '/\b(which ones?|that study|that paper|that research|these studies|those studies|this one|this study|this paper|this research|the study|the paper|the (?:first|second|third|last|other) one|(?:of|among|from) (?:these|those|them)|it|its|them|they|their|summari[sz]e (?:it|this|that)|tell me more|more details|more about|elaborate|expand on|what about|how about|why|and the|what else)\b/i';

    /**
     * @param  ?array  $activeContext  ChatConversation::active_context
     * @return array{topic: string, is_follow_up: bool, method: string}
     *         'topic' is what retrieval should embed; 'method' is one of
     *         none|deterministic|llm, recorded on retrieval_meta so a bad
     *         rewrite is diagnosable after the fact.
     */
    public function condense(string $question, ?array $activeContext): array
    {
        $unchanged = ['topic' => $question, 'is_follow_up' => false, 'method' => 'none'];

        $priorTopic = trim((string) ($activeContext['topic'] ?? ''));

        if ($priorTopic === '') {
            return $unchanged;
        }

        if (! $this->looksLikeFollowUp($question)) {
            return $unchanged;
        }

        // Nothing to add — the question already contains the prior topic
        // (e.g. "tell me more about rice disease detection"), so prefixing
        // it again would only dilute the vector with a duplicate.
        if (mb_stripos($question, $priorTopic) !== false) {
            return ['topic' => $question, 'is_follow_up' => true, 'method' => 'none'];
        }

        if ($this->shouldUseLlm($question, $activeContext)) {
            $rewritten = $this->llmCondense($question, $activeContext);

            if ($rewritten !== null) {
                return ['topic' => $rewritten, 'is_follow_up' => true, 'method' => 'llm'];
            }
        }

        return [
            'topic' => $priorTopic.' '.$question,
            'is_follow_up' => true,
            'method' => 'deterministic',
        ];
    }

    /**
     * Short, or explicitly referring backwards. Both are needed: "why?" is
     * short but carries no back-reference token, while "how does that study
     * compare to the other one" is long but entirely dependent on context.
     */
    public function looksLikeFollowUp(string $question): bool
    {
        $question = trim($question);

        if ($question === '') {
            return false;
        }

        return str_word_count($question) <= self::SHORT_QUESTION_WORDS
            || (bool) preg_match(self::BACK_REFERENCE_PATTERN, $question);
    }

    /**
     * The deterministic prefix is good enough whenever there's one obvious
     * subject to inherit. It's the ORDINAL and contrastive references —
     * "the second one", "the other study", "compare them" — that actually
     * need a model, because which document they mean depends on the order
     * things were listed in, not on the topic string.
     */
    private function shouldUseLlm(string $question, ?array $activeContext): bool
    {
        if (! config('chat.condenser.llm_fallback', true)) {
            return false;
        }

        if (count($activeContext['documents'] ?? []) < 2) {
            return false;
        }

        return (bool) preg_match(
            '/\b(the (?:first|second|third|last|other) one|the other (?:study|paper|research)|compare|both|between them|versus|vs\.?)\b/i',
            $question
        );
    }

    /**
     * One small call, hard-capped. Returns null on any failure — a
     * condenser that can't reach the API must degrade to the deterministic
     * rewrite, never break the user's turn.
     */
    private function llmCondense(string $question, ?array $activeContext): ?string
    {
        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            return null;
        }

        $model = config('chat.models.answer');
        $titles = [];

        foreach ($activeContext['documents'] ?? [] as $title) {
            $titles[] = '- '.$title;
        }

        $context = "Topic under discussion: {$activeContext['topic']}\nStudies discussed, in order:\n".implode("\n", $titles);
        $start = microtime(true);

        try {
            $response = Http::withToken($apiKey)
                ->timeout(10)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'max_tokens' => 40,
                    'temperature' => 0,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Rewrite the user\'s follow-up as a standalone search query for a research database. '
                                .'Resolve pronouns and ordinal references ("the second one") using the context given. '
                                .'Output only the query text — no quotes, no explanation, no preamble.',
                        ],
                        ['role' => 'user', 'content' => $context."\n\nFollow-up: {$question}"],
                    ],
                ]);
        } catch (\Throwable $e) {
            Log::warning('Query condense call failed', ['error' => $e->getMessage()]);

            return null;
        }

        $latencyMs = (int) round((microtime(true) - $start) * 1000);

        if ($response->failed()) {
            return null;
        }

        $rewritten = $response->json('choices.0.message.content');

        if (! is_string($rewritten) || trim($rewritten) === '') {
            return null;
        }

        AiUsageLog::record([
            'user_id' => auth()->id(),
            'request_id' => (string) \Illuminate\Support\Str::uuid(),
            'operation' => 'query_condense',
            'model' => $model,
            'prompt_tokens' => $response->json('usage.prompt_tokens'),
            'completion_tokens' => $response->json('usage.completion_tokens'),
            'total_tokens' => $response->json('usage.total_tokens'),
            'latency_ms' => $latencyMs,
            'success' => true,
        ]);

        return trim($rewritten, " \t\n\r\0\x0B\"'");
    }
}
