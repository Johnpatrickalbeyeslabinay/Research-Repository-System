<?php

namespace App\Console\Commands;

use App\Models\ChatConversation;
use App\Models\User;
use App\Services\Chat\ChatAnswerService;
use App\Services\Chat\CitationBuilder;
use App\Services\Chat\ConversationContext;
use App\Services\Chat\IntentDetector;
use App\Services\Chat\PromptBuilder;
use App\Services\Retrieval\CatalogService;
use App\Services\Retrieval\QueryPlanner;
use App\Services\Retrieval\RetrievalService;
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
    protected $signature = 'research:eval-chat
        {--user= : User id to run the (non-probe) questions as — defaults to the first admin}
        {--probe-user= : User id with NO relation to the permission-probe document — a throwaway account is created and cleaned up if omitted}
        {--show : Print each answer in full, plus its token counts — the summary table says whether a turn was cited, not whether it read well}
        {--filter= : Only run fixtures whose type or question contains this string}';

    protected $description = 'Run a fixture set of questions through the chatbot pipeline and report retrieval/citation/groundedness/permission results';

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

            // Catalog fixtures exercise the document-level path
            // (App\Services\Retrieval\CatalogService), not chunk retrieval —
            // mirroring ChatController::message()'s branching, so this
            // harness measures what the chatbot actually does rather than
            // what it would do if every question were a research question.
            if (in_array($fixture['type'], ['catalog', 'catalog-permission-probe'], true)) {
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

            $rows[] = [
                $fixture['type'],
                Str::limit($fixture['question'], 45),
                $hasEvidence ? 'yes' : 'no',
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

        if ($permissionViolations > 0) {
            $this->error("Permission violations: {$permissionViolations} — CRITICAL, must be zero.");
        } else {
            $this->info('Permission violations: 0');
        }

        if ($probeUserIsThrowaway && $probeUser) {
            $probeUser->delete();
        }

        return $permissionViolations === 0 ? self::SUCCESS : self::FAILURE;
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
            ['type' => 'catalog', 'question' => 'list repository available'],
            ['type' => 'catalog', 'question' => 'What research is available?'],
            ['type' => 'catalog', 'question' => 'How many studies are in the repository?'],
            ['type' => 'catalog', 'question' => 'What topics are covered?'],
            ['type' => 'catalog', 'question' => 'recommend papers related to fish'],
            ['type' => 'catalog', 'question' => 'suggest studies about aquaculture'],
            ['type' => 'catalog', 'question' => 'Which studies are related to SDG 14?'],
            ['type' => 'catalog', 'question' => 'What studies were published from 2024 to 2026?'],
            // Catalog must honour ResearchAccess exactly as chunk retrieval
            // does — a second, independent way into the same corpus is
            // exactly where a permission model tends to spring a leak.
            ['type' => 'catalog-permission-probe', 'question' => 'list repository available', 'disallowed_stud_file_ids' => [26, 29, 30, 33]],

            // Multi-turn continuity — the "the chatbot is stateless"
            // complaint. Each ↳ turn is a follow-up that carries no topic of
            // its own and must resolve against the previous turn's
            // documents (see App\Services\Chat\QueryCondenser and
            // ConversationContext).
            ['type' => 'conversation', 'turns' => [
                'What research is available about seaweed diversity?',
                'what methodology did it use?',
                'summarize the findings of this research',
                'why did the results differ between seasons?',
            ]],

            ['type' => 'topic-discovery', 'question' => 'What research is available about seaweed diversity?'],
            ['type' => 'methodology', 'question' => 'What methodology was used to survey the seaweed sites?'],
            ['type' => 'findings', 'question' => 'What were the key findings about seaweed species dominance?'],
            ['type' => 'direct-fact', 'question' => 'How many seaweed species were identified in the study?'],
            ['type' => 'comparison', 'question' => 'Compare the diversity findings between the dry and wet seasons.'],
            ['type' => 'sdg-filter', 'question' => 'Which studies support SDG 4?'],
            ['type' => 'year-filter', 'question' => 'What studies were published in 2026?'],
            ['type' => 'author-filter', 'question' => 'What research did John Patrick Labinay work on?'],
            // Deliberately out-of-corpus. The correct behavior is to
            // decline — ideally via the zero-token no-evidence path, but
            // an explicit "the evidence doesn't cover this" is equally
            // correct and simply costs more. What must NEVER happen is a
            // confident answer, or a citation to an unrelated study.
            //
            // These are pinned to the research path on purpose: the catalog
            // path has its own out-of-corpus fixtures above, and this is
            // where chunk-level grounding honesty is measured.
            ['type' => 'unanswerable', 'question' => 'What studies discuss blockchain applications in supply chains?'],
            ['type' => 'unanswerable', 'question' => 'Summarize the findings on renewable energy adoption.'],
            ['type' => 'catalog', 'question' => 'find research about rice disease detection'],
            // stud_file_id 26 is private+pending, owned by user id 2 — the
            // probe user has no relation to it at all and must never
            // retrieve chunks from it, by any path (vector or lexical).
            ['type' => 'permission-probe', 'question' => 'What were the seaweed survey findings?', 'disallowed_stud_file_ids' => [26, 29, 30]],
        ];
    }
}
