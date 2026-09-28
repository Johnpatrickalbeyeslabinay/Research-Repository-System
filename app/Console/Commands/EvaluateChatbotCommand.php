<?php

namespace App\Console\Commands;

use App\Models\ChatConversation;
use App\Models\User;
use App\Services\Chat\ChatAnswerService;
use App\Services\Chat\CitationBuilder;
use App\Services\Chat\ConversationContext;
use App\Services\Chat\IntentDetector;
use App\Services\Chat\PromptBuilder;
use App\Services\Chat\Routing\Intent;
use App\Services\Chat\Routing\IntentRouter;
use App\Services\Chat\Routing\RoutedIntent;
use App\Services\Retrieval\CatalogService;
use App\Services\Retrieval\QueryPlanner;
use App\Services\Retrieval\RetrievalService;
use App\Support\AiCost;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Runs a fixture set of questions through the real retrieval + chat
 * pipeline and reports retrieval coverage, citation validity, and —
 * critically — permission safety. Each question runs through a scratch
 * ChatConversation, deleted immediately after (never left in chat history).
 *
 * This is a coverage/regression signal for a developer to read, not a pass/
 * fail CI gate on everything: with a small corpus, "no evidence found" is
 * the CORRECT answer for most out-of-corpus questions, not a failure. The
 * one line that IS a hard pass/fail is permission violations — that must
 * always be zero.
 */
class EvaluateChatbotCommand extends Command
{
    /**
     * Documents an unrelated user must never be able to retrieve: everything
     * that is not both approved AND public.
     *
     * THESE IDS MUST BE KEPT CURRENT. The permission probes are the harness's
     * one hard pass/fail, and they work by asserting that none of these ids
     * appear in a probe user's results — so an id that no longer exists makes
     * the assertion vacuously true. This list previously read [26, 29, 30],
     * none of which were still in the corpus, which meant "Permission
     * violations: 0" was reporting on nothing at all for that probe.
     *
     * Verify with:
     *   select id, status, visibility from stud_file
     *    where not (status = 'approved' and visibility = 'public');
     */
    private const PRIVATE_DOCUMENT_IDS = [33, 35, 36, 37];

    protected $signature = 'research:eval-chat
        {--user= : User id to run the (non-probe) questions as — defaults to the first admin}
        {--probe-user= : User id with NO relation to the permission-probe document — a throwaway account is created and cleaned up if omitted}
        {--show : Print each answer in full, plus its token counts — the summary table says whether a turn was cited, not whether it read well}
        {--filter= : Only run fixtures whose type or question contains this string}
        {--tokens : Print the per-fixture token and cost report}
        {--save-baseline= : Write this run to a JSON file to diff future runs against}
        {--baseline= : Diff this run against a saved baseline}';

    protected $description = 'Run a fixture set of questions through the chatbot pipeline and report retrieval/citation/groundedness/permission results';

    public function __construct(private IntentRouter $router)
    {
        parent::__construct();
    }

    public function handle(
        RetrievalService $retrieval,
        PromptBuilder $prompts,
        ChatAnswerService $answerer,
        CitationBuilder $citations
    ): int {
        $user = $this->resolveUser();

        if (! $user) {
            $this->error('No user found to run the evaluation as. Pass --user=<id> or seed an admin.');

            return self::FAILURE;
        }

        [$probeUser, $probeUserIsThrowaway] = $this->resolveProbeUser();

        $rows = [];
        $permissionViolations = 0;
        $noEvidenceCount = 0;
        $answeredCount = 0;

        /** @var array<int, array{type: string, question: string, expected: string, actual: string}> */
        $routingMismatches = [];

        foreach ($this->selectedFixtures() as $fixture) {
            // Every probe type must run as the unrelated throwaway user —
            // running one as the admin (who legitimately sees everything)
            // would report a leak that isn't one.
            $isProbe = str_ends_with($fixture['type'], 'permission-probe');
            $runningAs = $isProbe ? $probeUser : $user;

            if ($runningAs === null) {
                $rows[] = [$fixture['type'], Str::limit($fixture['question'], 45), '—', '—', 'skipped: no probe user'];

                continue;
            }

            if ($fixture['type'] === 'conversation') {
                foreach ($this->evaluateConversationFixture(
                    $fixture,
                    $runningAs,
                    $retrieval,
                    $prompts,
                    $answerer,
                    $citations,
                    $answeredCount,
                    $noEvidenceCount
                ) as $row) {
                    $rows[] = $row;
                }

                continue;
            }

            $conversation = ChatConversation::create(['user_id' => $runningAs->id]);

            // Dispatch on what the ROUTER decides, not on the fixture's type
            // label. Those are different things, and conflating them is how a
            // harness stops measuring the product: a fixture typed 'catalog'
            // used to be forced down the catalog path regardless of how the
            // real chatbot would route that same sentence, so a mis-route was
            // structurally undetectable here and the token numbers described a
            // path the user might never take.
            //
            // The label is now an EXPECTATION, checked against the routed
            // intent and reported when the two disagree.
            $routed = $this->router->route($fixture['question']);
            $this->recordRouting($fixture, $routed, $routingMismatches);

            // Zero-token intents are the whole point of the routing work, so
            // the harness has to prove they cost nothing rather than assume it.
            if ($routed->isZeroToken()) {
                $this->recordTokens($fixture, $routed, outcome: 'free (no API call)');
                $rows[] = [
                    $fixture['type'],
                    Str::limit($fixture['question'], 45),
                    $routed->intent->value,
                    '0 / 0',
                    'free (no API call)',
                ];

                $conversation->messages()->delete();
                $conversation->delete();

                continue;
            }

            // Catalog fixtures exercise the document-level path
            // (App\Services\Retrieval\CatalogService), not chunk retrieval.
            if ($routed->intent === Intent::Catalog) {
                $rows[] = $this->evaluateCatalogFixture(
                    $fixture,
                    $runningAs,
                    $conversation,
                    $retrieval,
                    $prompts,
                    $answerer,
                    $citations,
                    $permissionViolations,
                    $answeredCount,
                    $noEvidenceCount
                );

                $conversation->messages()->delete();
                $conversation->delete();

                continue;
            }

            $result = $retrieval->retrieve($fixture['question'], $runningAs);
            $hasEvidence = ! $result->isEmpty();
            $citationCount = 0;
            $outcome = 'no evidence found';

            if ($fixture['type'] === 'permission-probe') {
                $leaked = array_intersect($result->documentIds(), $fixture['disallowed_stud_file_ids'] ?? []);

                if (! empty($leaked)) {
                    $permissionViolations++;
                    $outcome = 'LEAK: doc '.implode(',', $leaked).' retrievable';
                } else {
                    $outcome = 'correctly excluded';
                }
            } elseif ($hasEvidence) {
                $answeredCount++;

                try {
                    $prompt = $prompts->build($conversation, $result, $fixture['question']);
                    $answer = $answerer->answer($prompt['messages']);
                    $built = $citations->build($answer['content'], $prompt['evidence_order'], $result->files);
                    $citationCount = count($built['citations']);
                    $outcome = $citationCount > 0 ? 'grounded, cited' : 'answered, NO citations';
                    $this->showAnswer($fixture['question'], $built['text'], $answer['prompt_tokens'], $answer['completion_tokens']);
                } catch (\Throwable $e) {
                    $outcome = 'error: '.Str::limit($e->getMessage(), 40);
                }
            } else {
                $noEvidenceCount++;
            }

            $this->recordTokens(
                $fixture,
                $routed,
                tier: $result->tier?->tier->value,
                escalated: (bool) $result->tier?->escalated,
                answer: $answer ?? null,
                evidenceChunks: count($result->chunks),
                evidenceDocuments: count($result->documentIds()),
                outcome: $outcome,
            );

            $rows[] = [
                $fixture['type'],
                Str::limit($fixture['question'], 45),
                // Tier, not just "did we find anything". Which tier answered —
                // and whether the gate escalated — is the number that says
                // whether abstract-first actually paid off; a token total alone
                // cannot tell "the cheap tier worked" from "everything
                // escalated to full text anyway".
                $hasEvidence
                    ? ($result->tier?->tier->value ?? 'yes').($result->tier?->escalated ? ' ↑' : '')
                    : 'no',
                $citationCount ?: '—',
                $outcome,
            ];

            $conversation->messages()->delete();
            $conversation->delete();
        }

        $this->table(['Type', 'Question', 'Evidence / scope', 'Citations', 'Outcome'], $rows);
        $this->newLine();
        $this->line("Answered with evidence: {$answeredCount}");
        $this->line("No-evidence (zero-token) responses: {$noEvidenceCount}");

        if (! empty($routingMismatches)) {
            $this->newLine();
            $this->warn('Routing disagreed with the fixture label on '.count($routingMismatches).' question(s):');
            $this->table(
                ['Question', 'Fixture expects', 'Router chose', 'Via'],
                array_map(array_values(...), $routingMismatches),
            );
            $this->line('Either the fixture label is stale, or a classifier is claiming a turn it should not.');
        }

        if ($permissionViolations > 0) {
            $this->error("Permission violations: {$permissionViolations} — CRITICAL, must be zero.");
        } else {
            $this->info('Permission violations: 0');
        }

        if ($this->option('tokens') || $this->option('save-baseline') || $this->option('baseline')) {
            $this->reportTokens();
        }

        if ($path = $this->option('baseline')) {
            $this->diffBaseline((string) $path);
        }

        if ($path = $this->option('save-baseline')) {
            $this->saveBaseline((string) $path);
        }

        if ($probeUserIsThrowaway && $probeUser) {
            $probeUser->delete();
        }

        return $permissionViolations === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Per-fixture token ledger, keyed by question.
     *
     * Every turn records here, INCLUDING the zero-token ones — a report that
     * only lists turns that spent something cannot show that a path became
     * free, which is precisely the change most of this work made.
     *
     * @var array<string, array<string, mixed>>
     */
    private array $ledger = [];

    private function recordTokens(
        array $fixture,
        RoutedIntent $routed,
        ?string $tier = null,
        bool $escalated = false,
        ?array $answer = null,
        int $evidenceChunks = 0,
        int $evidenceDocuments = 0,
        string $outcome = '',
    ): void {
        $prompt = (int) ($answer['prompt_tokens'] ?? 0);
        $completion = (int) ($answer['completion_tokens'] ?? 0);
        $cached = (int) ($answer['cached_prompt_tokens'] ?? 0);

        $this->ledger[$fixture['question']] = [
            'type' => $fixture['type'],
            'question' => $fixture['question'],
            'intent' => $routed->intent->value,
            'classifier' => $routed->classifier,
            'tier' => $tier,
            'escalated' => $escalated,
            'llm_calls' => $answer === null ? 0 : 1,
            'prompt_tokens' => $prompt,
            'cached_prompt_tokens' => $cached,
            'completion_tokens' => $completion,
            'estimated_prompt_tokens' => (int) ($answer['estimated_prompt_tokens'] ?? 0),
            'evidence_chunks' => $evidenceChunks,
            'evidence_documents' => $evidenceDocuments,
            'outcome' => $outcome,
            'usd' => AiCost::usd($answer['model'] ?? null, $prompt, $completion, $cached ?: null),
        ];
    }

    /**
     * Compares the fixture's type label against what the router actually did.
     *
     * A fixture type is a claim about how a sentence SHOULD be handled. The
     * routed intent is how it IS handled. When those disagree, one of the two
     * is wrong and a developer needs to see which — silently trusting the label
     * (as this harness used to) means a routing regression shows up as a
     * perfectly normal-looking results table.
     *
     * @param  array{type: string, question: string}  $fixture
     * @param  array<int, array<string, string>>  $mismatches
     */
    private function recordRouting(array $fixture, RoutedIntent $routed, array &$mismatches): void
    {
        // A SET, not a single intent: one label can legitimately cover a family.
        // 'smalltalk' has to accept Acknowledgement too, because "salamat" and
        // "thanks" are small talk to a reader but a different intent to the
        // router — and a harness that flags that as a regression is a harness
        // developers learn to ignore.
        $expected = match ($fixture['type']) {
            'catalog', 'catalog-permission-probe' => [Intent::Catalog, Intent::CatalogCount],
            'smalltalk' => [Intent::SmallTalk, Intent::Acknowledgement, Intent::Unintelligible],
            'identity' => [Intent::Identity],
            'out-of-scope' => [Intent::OutOfScope],
            'platform' => [Intent::Platform],
            // Author, year and SDG questions are DOCUMENT-level: they ask which
            // studies exist matching a filter, not what any one study says. The
            // router sends them to the catalog path, which is correct — these
            // labels used to expect research_qa and reported three mismatches
            // every run, which is how a harness trains people to ignore it.
            'author-filter', 'year-filter', 'sdg-filter' => [Intent::Catalog, Intent::CatalogCount, Intent::ResearchQa],
            // Everything else is expected to reach research retrieval. That
            // includes the permission probes, whose whole purpose is to prove
            // retrieval excludes documents the user may not read.
            default => [Intent::ResearchQa],
        };

        if (in_array($routed->intent, $expected, true)) {
            return;
        }

        $mismatches[] = [
            'question' => Str::limit($fixture['question'], 50),
            'expected' => implode(' | ', array_map(fn (Intent $i) => $i->value, $expected)),
            'actual' => $routed->intent->value,
            'classifier' => $routed->classifier,
        ];
    }

    /**
     * One catalog fixture through the real document-level path. Counters
     * are by-reference so the summary totals stay accurate across both
     * evaluation styles.
     *
     * @param  array{type: string, question: string, disallowed_stud_file_ids?: int[]}  $fixture
     * @return array<int, string|int>  one table row
     */
    private function evaluateCatalogFixture(
        array $fixture,
        User $runningAs,
        ChatConversation $conversation,
        RetrievalService $retrieval,
        PromptBuilder $prompts,
        ChatAnswerService $answerer,
        CitationBuilder $citations,
        int &$permissionViolations,
        int &$answeredCount,
        int &$noEvidenceCount
    ): array {
        $intents = app(IntentDetector::class);
        $planner = app(QueryPlanner::class);
        $catalogService = app(CatalogService::class);

        $question = $fixture['question'];

        // A catalog fixture that the detector doesn't even classify as one
        // is a failure worth seeing in the table — the rest of the path
        // would never run for it in production either.
        if (! $intents->isCatalogQuestion($question)) {
            return [$fixture['type'], Str::limit($question, 45), 'n/a', '—', 'NOT detected as catalog'];
        }

        $plan = $planner->plan($question, null, null);
        $vector = $intents->isBareInventory($question) ? null : $retrieval->topicVector($plan['topic']);
        $catalog = $catalogService->browse($runningAs, $plan, $vector);

        if ($fixture['type'] === 'catalog-permission-probe') {
            $leaked = array_intersect($catalog->documentIds(), $fixture['disallowed_stud_file_ids'] ?? []);

            if (! empty($leaked)) {
                $permissionViolations++;

                return [$fixture['type'], Str::limit($question, 45), 'yes', '—', 'LEAK: doc '.implode(',', $leaked).' listed'];
            }

            return [$fixture['type'], Str::limit($question, 45), 'no', '—', 'correctly excluded'];
        }

        if ($catalog->isEmpty()) {
            $noEvidenceCount++;

            return [$fixture['type'], Str::limit($question, 45), 'no', '—', 'empty catalog (zero-token)'];
        }

        $answeredCount++;

        try {
            $prompt = $prompts->buildCatalog($conversation, $catalog, $question);
            $answer = $answerer->answer($prompt['messages'], (int) config('chat.catalog.max_output_tokens', 450));
            $built = $citations->build($answer['content'], $prompt['evidence_order'], $catalog->files());
            $citationCount = count($built['citations']);

            $this->showAnswer($question, $built['text'], $answer['prompt_tokens'], $answer['completion_tokens']);

            return [
                $fixture['type'],
                Str::limit($question, 45),
                $catalog->documents->count().' docs',
                $citationCount ?: '—',
                $citationCount > 0 ? 'listed, cited' : 'listed, NO citations',
            ];
        } catch (\Throwable $e) {
            return [$fixture['type'], Str::limit($question, 45), 'yes', '—', 'error: '.Str::limit($e->getMessage(), 40)];
        }
    }

    /**
     * A multi-turn fixture: several questions down ONE conversation, with
     * active_context carried between turns exactly as
     * ChatController::message() does.
     *
     * Single-question fixtures structurally cannot catch the statelessness
     * failure this exists to measure — a follow-up only misbehaves in the
     * presence of a previous turn. What matters per turn is whether
     * retrieval found the RIGHT documents, so the row reports the scope
     * each turn resolved to.
     *
     * @param  array{type: string, turns: string[]}  $fixture
     * @return array<int, array<int, string|int>>  one table row per turn
     */
    private function evaluateConversationFixture(
        array $fixture,
        User $runningAs,
        RetrievalService $retrieval,
        PromptBuilder $prompts,
        ChatAnswerService $answerer,
        CitationBuilder $citations,
        int &$answeredCount,
        int &$noEvidenceCount
    ): array {
        $context = app(ConversationContext::class);
        $conversation = ChatConversation::create(['user_id' => $runningAs->id]);
        $rows = [];

        foreach ($fixture['turns'] as $i => $question) {
            $conversation->refresh();

            $conversation->messages()->create(['role' => 'user', 'content' => $question]);

            $result = $retrieval->retrieve(
                question: $question,
                user: $runningAs,
                activeContext: $conversation->active_context,
            );

            $isFollowUp = $result->filters['is_follow_up'] ?? false;
            $scoped = ! empty($result->filters['stud_file_ids']);
            $citationCount = 0;
            $answerText = '';

            if ($result->isEmpty()) {
                $noEvidenceCount++;
                $outcome = 'no evidence';
            } else {
                $answeredCount++;

                try {
                    $prompt = $prompts->build($conversation, $result, $question);
                    $answer = $answerer->answer($prompt['messages']);
                    $built = $citations->build($answer['content'], $prompt['evidence_order'], $result->files);
                    $citationCount = count($built['citations']);
                    $answerText = $built['text'];
                    $outcome = $citationCount > 0 ? 'grounded, cited' : 'answered, NO citations';

                    $conversation->messages()->create([
                        'role' => 'assistant',
                        'content' => $answerText,
                    ]);

                    $this->showAnswer($question, $answerText, $answer['prompt_tokens'], $answer['completion_tokens']);
                } catch (\Throwable $e) {
                    $outcome = 'error: '.Str::limit($e->getMessage(), 30);
                }
            }

            $context->update($conversation, $result, $isFollowUp);

            // The signal that continuity actually worked: a follow-up should
            // be detected as one AND resolve to a document scope, rather
            // than silently re-searching the whole corpus.
            $marks = [];
            $marks[] = $isFollowUp ? 'follow-up' : 'fresh';
            if ($scoped) {
                $marks[] = 'scoped('.count($result->filters['stud_file_ids']).')';
            }
            if (($result->filters['condense_method'] ?? 'none') !== 'none') {
                $marks[] = $result->filters['condense_method'];
            }

            $rows[] = [
                $i === 0 ? $fixture['type'] : '  ↳ turn '.($i + 1),
                Str::limit($question, 45),
                implode(' ', $marks),
                $citationCount ?: '—',
                $outcome,
            ];
        }

        $conversation->messages()->delete();
        $conversation->delete();

        return $rows;
    }

    /**
     * fixtureQuestions() narrowed by --filter, so a single category can be
     * iterated on without paying for (or waiting on) the whole set.
     *
     * @return array<int, array{type: string, question: string, disallowed_stud_file_ids?: int[]}>
     */
    private function selectedFixtures(): array
    {
        $filter = $this->option('filter');

        if (! $filter) {
            return $this->fixtureQuestions();
        }

        return array_values(array_filter(
            $this->fixtureQuestions(),
            // A 'conversation' fixture carries 'turns', not 'question'.
            fn ($f) => Str::contains($f['type'], $filter, ignoreCase: true)
                || Str::contains($f['question'] ?? implode(' ', $f['turns'] ?? []), $filter, ignoreCase: true)
        ));
    }

    /** Prints a full answer under --show; a no-op otherwise. */
    /** The per-fixture token and cost table. */
    private function reportTokens(): void
    {
        if (empty($this->ledger)) {
            return;
        }

        $rows = [];
        $totals = ['calls' => 0, 'in' => 0, 'out' => 0, 'usd' => 0.0, 'free' => 0];

        foreach ($this->ledger as $entry) {
            $rows[] = [
                $entry['type'],
                Str::limit($entry['question'], 34),
                $entry['intent'],
                $entry['tier'] ? $entry['tier'].($entry['escalated'] ? ' ↑' : '') : '—',
                $entry['llm_calls'],
                $entry['prompt_tokens'] ?: '—',
                $entry['completion_tokens'] ?: '—',
                $entry['usd'] > 0 ? '$'.number_format($entry['usd'], 5) : 'free',
            ];

            $totals['calls'] += $entry['llm_calls'];
            $totals['in'] += $entry['prompt_tokens'];
            $totals['out'] += $entry['completion_tokens'];
            $totals['usd'] += $entry['usd'];
            $totals['free'] += $entry['llm_calls'] === 0 ? 1 : 0;
        }

        $this->newLine();
        $this->line('Token report');
        $this->newLine();
        $this->table(['Type', 'Question', 'Intent', 'Tier', 'Calls', 'In', 'Out', 'Cost'], $rows);

        $this->line(sprintf(
            'TOTAL  %d turns, %d LLM calls (%d free)  ·  %s in / %s out  ·  $%s',
            count($this->ledger),
            $totals['calls'],
            $totals['free'],
            number_format($totals['in']),
            number_format($totals['out']),
            number_format($totals['usd'], 5),
        ));

        $this->reportEstimatorAccuracy();
    }

    /**
     * How the estimator did on this run. Surfaced here as well as in
     * research:calibrate-token-estimator because a fixture run is the one place
     * both numbers are in hand at once.
     */
    private function reportEstimatorAccuracy(): void
    {
        $ratios = [];

        foreach ($this->ledger as $entry) {
            if ($entry['prompt_tokens'] > 0 && $entry['estimated_prompt_tokens'] > 0) {
                $ratios[] = $entry['prompt_tokens'] / $entry['estimated_prompt_tokens'];
            }
        }

        if (empty($ratios)) {
            return;
        }

        $under = count(array_filter($ratios, fn (float $r) => $r > 1.0));

        $this->line(sprintf(
            '       estimator: mean actual/estimated %.2f, %d of %d turns under-estimated',
            array_sum($ratios) / count($ratios),
            $under,
            count($ratios),
        ));
    }

    private function saveBaseline(string $path): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($path, json_encode([
            'saved_at' => now()->toIso8601String(),
            'prompt_version' => \App\Services\Chat\PromptBuilder::promptVersion(),
            'model' => config('chat.models.answer'),
            'fixtures' => $this->ledger,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $this->newLine();
        $this->info("Baseline written to {$path}");
        $this->line('Diff a later run against it with --baseline='.$path);
    }

    /**
     * The before/after table this whole measurement effort exists to produce.
     *
     * The OUTCOME column is the point. A token saving that also turned
     * "grounded, cited" into "answered, NO citations" is not a saving, it is a
     * regression wearing one — and a table of Δtokens alone would report it as
     * a success.
     */
    private function diffBaseline(string $path): void
    {
        if (! is_file($path)) {
            $this->error("No baseline at {$path}");

            return;
        }

        $baseline = json_decode((string) file_get_contents($path), true);

        if (! is_array($baseline) || ! isset($baseline['fixtures'])) {
            $this->error("{$path} is not a baseline file this command wrote.");

            return;
        }

        $rows = [];
        $deltaIn = 0;
        $deltaOut = 0;
        $deltaUsd = 0.0;
        $changed = 0;

        foreach ($this->ledger as $question => $now) {
            $then = $baseline['fixtures'][$question] ?? null;

            if ($then === null) {
                $rows[] = [Str::limit($question, 34), 'NEW', '—', '—', $now['outcome']];

                continue;
            }

            $in = $now['prompt_tokens'] - (int) ($then['prompt_tokens'] ?? 0);
            $out = $now['completion_tokens'] - (int) ($then['completion_tokens'] ?? 0);
            $usd = $now['usd'] - (float) ($then['usd'] ?? 0);

            $deltaIn += $in;
            $deltaOut += $out;
            $deltaUsd += $usd;

            $outcomeChanged = ($then['outcome'] ?? '') !== $now['outcome'];

            if ($outcomeChanged) {
                $changed++;
            }

            $rows[] = [
                Str::limit($question, 34),
                $this->signed($in),
                $this->signed($out),
                ($usd >= 0 ? '+' : '').number_format($usd, 5),
                $outcomeChanged
                    ? '⚠ '.($then['outcome'] ?? '—').' → '.$now['outcome']
                    : $now['outcome'],
            ];
        }

        $this->newLine();
        $this->line('Diff against '.($baseline['saved_at'] ?? 'baseline')
            .' (prompt '.($baseline['prompt_version'] ?? '?').' → '.\App\Services\Chat\PromptBuilder::promptVersion().')');
        $this->newLine();
        $this->table(['Question', 'Δ in', 'Δ out', 'Δ USD', 'Outcome'], $rows);

        $this->line(sprintf(
            'TOTAL  %s input tokens, %s output tokens, %s USD',
            $this->signed($deltaIn),
            $this->signed($deltaOut),
            ($deltaUsd >= 0 ? '+' : '').number_format($deltaUsd, 5),
        ));

        if ($changed > 0) {
            $this->newLine();
            $this->error("{$changed} fixture(s) changed outcome — read those rows before believing any saving above.");
        } else {
            $this->info('No fixture changed outcome.');
        }
    }

    private function signed(int $value): string
    {
        return ($value > 0 ? '+' : '').number_format($value);
    }

    private function showAnswer(string $question, string $text, ?int $promptTokens, ?int $completionTokens): void
    {
        if (! $this->option('show')) {
            return;
        }

        $this->newLine();
        $this->line("<fg=cyan>Q:</> {$question}");
        $this->line("<fg=gray>tokens in/out: {$promptTokens}/{$completionTokens}</>");
        $this->line($text);
        $this->line(str_repeat('-', 70));
    }

    private function resolveUser(): ?User
    {
        if ($id = $this->option('user')) {
            return User::find($id);
        }

        return User::where('role', 'admin')->first();
    }

    /**
     * @return array{0: ?User, 1: bool} [user, isThrowaway]
     */
    private function resolveProbeUser(): array
    {
        if ($id = $this->option('probe-user')) {
            return [User::find($id), false];
        }

        $user = User::create([
            'user_id' => 'EVAL-PROBE-'.Str::random(6),
            'role' => 'student',
            'email' => 'eval-probe-'.Str::random(8).'@example.test',
            'name' => 'Evaluation Probe User',
            'account_status' => 'active',
            'password' => bcrypt(Str::random(32)),
        ]);

        return [$user, true];
    }

    /**
     * A deliberately small, hand-picked fixture set spanning the question
     * categories from the project's RAG plan (Deliverable 30): direct
     * fact, topic discovery, methodology, findings, SDG/year/author
     * filters, comparison, unanswerable, and a permission probe. Sized for
     * a quick developer-run sanity check, not exhaustive coverage — expand
     * this as the corpus grows past the current 3 (duplicate) documents.
     *
     * @return array<int, array{type: string, question: string, disallowed_stud_file_ids?: int[]}>
     */
    private function fixtureQuestions(): array
    {
        return [
            // Catalog / discovery — document-level questions that chunk
            // retrieval structurally cannot answer (no chunk contains a
            // list of the repository's contents). Before
            // App\Services\Retrieval\CatalogService existed, every one of
            // these returned the no-evidence reply.
            // Zero-token intents. These are in the fixture set specifically so
            // the harness PROVES they cost nothing rather than assuming it —
            // every one of them used to reach the API. The Tagalog and Ilocano
            // greetings are here because the users are at CSU Aparri in
            // Cagayan and the old anchored regex missed all of them.
            ['type' => 'smalltalk', 'question' => 'hi'],
            ['type' => 'smalltalk', 'question' => 'hi there'],
            ['type' => 'smalltalk', 'question' => 'good day sir'],
            ['type' => 'smalltalk', 'question' => 'kumusta po'],
            ['type' => 'smalltalk', 'question' => 'magandang umaga'],
            ['type' => 'smalltalk', 'question' => 'naimbag nga bigat'],
            ['type' => 'smalltalk', 'question' => 'salamat'],
            ['type' => 'identity', 'question' => 'who are you?'],
            ['type' => 'identity', 'question' => 'what can you do?'],
            ['type' => 'identity', 'question' => 'are you chatgpt'],
            ['type' => 'out-of-scope', 'question' => 'recommend a good restaurant'],
            ['type' => 'out-of-scope', 'question' => 'what is the weather today'],

            // The counterpart guard: a greeting PREFIX on a real question must
            // still be answered as the question. If routing ever regresses to
            // answering this with "Hello!", it shows up here as an
            // out-of-scope/smalltalk mismatch rather than passing silently.
            ['type' => 'catalog', 'question' => 'hi, list repository available'],

            ['type' => 'catalog', 'question' => 'list repository available'],
            ['type' => 'catalog', 'question' => 'What research is available?'],
            ['type' => 'catalog', 'question' => 'How many studies are in the repository?'],
            ['type' => 'catalog', 'question' => 'What topics are covered?'],
            ['type' => 'catalog', 'question' => 'recommend papers about document management'],
            ['type' => 'catalog', 'question' => 'suggest studies about artificial intelligence in education'],
            ['type' => 'catalog', 'question' => 'Which studies are related to SDG 9?'],
            ['type' => 'catalog', 'question' => 'What studies were published from 2024 to 2026?'],
            // Catalog must honour ResearchAccess exactly as chunk retrieval
            // does — a second, independent way into the same corpus is
            // exactly where a permission model tends to spring a leak.
            ['type' => 'catalog-permission-probe', 'question' => 'list repository available', 'disallowed_stud_file_ids' => self::PRIVATE_DOCUMENT_IDS],

            // Multi-turn continuity — the "the chatbot is stateless"
            // complaint. Each ↳ turn is a follow-up that carries no topic of
            // its own and must resolve against the previous turn's
            // documents (see App\Services\Chat\QueryCondenser and
            // ConversationContext).
            ['type' => 'conversation', 'turns' => [
                'What research is available about GPS tracking for passenger boats?',
                'what methodology did it use?',
                'summarize the findings of this research',
                'why was real-time tracking needed?',
            ]],

            // --- Abstract tier is expected to answer these on its own -------
            //
            // Overview, authorship and provenance questions. The answer lives
            // in a study's title, abstract and catalogued metadata, so these
            // should NOT escalate to full text — that is the whole point of
            // App\Services\Retrieval\Tier::Abstract. An escalation here (shown
            // with ↑ in the results) is a tuning signal, not a pass.
            ['type' => 'abstract-tier', 'question' => 'What is the GPS boat tracking study about?'],
            ['type' => 'abstract-tier', 'question' => 'What is the document management system research about?'],
            ['type' => 'abstract-tier', 'question' => 'Is there anything on information literacy and student performance?'],
            ['type' => 'author-filter', 'question' => 'What research did Marinette Peralta work on?'],
            ['type' => 'year-filter', 'question' => 'What studies were published in 2024?'],
            ['type' => 'sdg-filter', 'question' => 'Which studies support SDG 4?'],

            // --- Full text is expected: these need the body ----------------
            ['type' => 'methodology', 'question' => 'What methodology was used in the AI learning efficiency study?'],
            ['type' => 'findings', 'question' => 'What were the key findings about AI tool usage and academic performance?'],
            ['type' => 'direct-fact', 'question' => 'How many respondents took part in the AI tool usage study?'],
            ['type' => 'comparison', 'question' => 'Compare the document management study with the archives management study.'],
            ['type' => 'drill-down', 'question' => 'Give me the full details of the SeawatchWeb safety protocols.'],

            // The one genuinely marine paper in the corpus — filed under the
            // title "Research Repository System" but its actual content is a
            // seaweed diversity survey in Gonzaga, Cagayan. Kept because a
            // title/content mismatch is a real retrieval challenge: the answer
            // must come from the body text, not the title.
            ['type' => 'findings', 'question' => 'What were the findings about seaweed species in Gonzaga, Cagayan?'],

            // Deliberately out-of-corpus. The correct behavior is to
            // decline — ideally via the zero-token no-evidence path, but
            // an explicit "the evidence doesn't cover this" is equally
            // correct and simply costs more. What must NEVER happen is a
            // confident answer, or a citation to an unrelated study.
            ['type' => 'unanswerable', 'question' => 'What studies discuss blockchain applications in supply chains?'],
            ['type' => 'unanswerable', 'question' => 'Summarize the findings on vaccine hesitancy in rural clinics.'],
            ['type' => 'catalog', 'question' => 'find research about rice disease detection'],

            // The probe user has NO relation to any of these documents and must
            // never retrieve a chunk from one, by any path (vector or lexical).
            //
            // The question deliberately targets private content: asking about
            // AI tool usage aims squarely at document 35, which is approved but
            // PRIVATE. A probe that asks about public content proves nothing.
            ['type' => 'permission-probe', 'question' => 'What did the study find about AI tool usage and learning efficiency?', 'disallowed_stud_file_ids' => self::PRIVATE_DOCUMENT_IDS],
            ['type' => 'permission-probe', 'question' => 'What were the findings about seaweed species in Gonzaga?', 'disallowed_stud_file_ids' => self::PRIVATE_DOCUMENT_IDS],
        ];
    }
}
