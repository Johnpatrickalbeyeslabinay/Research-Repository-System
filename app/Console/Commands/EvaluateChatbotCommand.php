<?php

namespace App\Console\Commands;

use App\Models\ChatConversation;
use App\Models\User;
use App\Services\Chat\ChatAnswerService;
use App\Services\Chat\CitationBuilder;
use App\Services\Chat\PromptBuilder;
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
        {--probe-user= : User id with NO relation to the permission-probe document — a throwaway account is created and cleaned up if omitted}';

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

        foreach ($this->fixtureQuestions() as $fixture) {
            $runningAs = $fixture['type'] === 'permission-probe' ? $probeUser : $user;

            if ($runningAs === null) {
                $rows[] = [$fixture['type'], Str::limit($fixture['question'], 45), '—', '—', 'skipped: no probe user'];

                continue;
            }

            $conversation = ChatConversation::create(['user_id' => $runningAs->id]);

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

        $this->table(['Type', 'Question', 'Evidence?', 'Citations', 'Outcome'], $rows);
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
            ['type' => 'topic-discovery', 'question' => 'What research is available about seaweed diversity?'],
            ['type' => 'methodology', 'question' => 'What methodology was used to survey the seaweed sites?'],
            ['type' => 'findings', 'question' => 'What were the key findings about seaweed species dominance?'],
            ['type' => 'direct-fact', 'question' => 'How many seaweed species were identified in the study?'],
            ['type' => 'comparison', 'question' => 'Compare the diversity findings between the dry and wet seasons.'],
            ['type' => 'sdg-filter', 'question' => 'Which studies support SDG 4?'],
            ['type' => 'year-filter', 'question' => 'What studies were published in 2026?'],
            ['type' => 'author-filter', 'question' => 'What research did John Patrick Labinay work on?'],
            // Deliberately out-of-corpus — the correct behavior is
            // "no evidence found", not a hallucinated answer.
            ['type' => 'unanswerable', 'question' => 'What research is available about rice disease detection using AI?'],
            ['type' => 'unanswerable', 'question' => 'What studies discuss blockchain applications in supply chains?'],
            ['type' => 'unanswerable', 'question' => 'Summarize the findings on renewable energy adoption.'],
            // stud_file_id 26 is private+pending, owned by user id 2 — the
            // probe user has no relation to it at all and must never
            // retrieve chunks from it, by any path (vector or lexical).
            ['type' => 'permission-probe', 'question' => 'What were the seaweed survey findings?', 'disallowed_stud_file_ids' => [26, 29, 30]],
        ];
    }
}
