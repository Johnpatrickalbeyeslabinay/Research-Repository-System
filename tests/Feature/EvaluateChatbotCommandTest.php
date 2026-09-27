<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The evaluation harness itself.
 *
 * `research:eval-chat` is the instrument every token claim in this project
 * rests on, so it needs its own coverage — an instrument that silently
 * measures the wrong thing is worse than no instrument. It used to dispatch on
 * each fixture's hand-written type label rather than on what the router
 * actually decides, which made a routing regression structurally invisible
 * here: the results table looked normal either way.
 *
 * Only the zero-token fixtures are exercised. Everything else needs pgvector
 * (absent on sqlite) and a live API key, which is exactly why the harness is
 * a developer tool run against the real database rather than a CI gate.
 */
class EvaluateChatbotCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Any API call at all during a small-talk run is a failure, not a
        // detail — these fixtures exist to prove those turns are free.
        Http::preventStrayRequests();

        User::factory()->admin()->create();
    }

    public function test_small_talk_fixtures_report_as_free_and_make_no_api_call(): void
    {
        $this->artisan('research:eval-chat --filter=smalltalk')
            ->expectsOutputToContain('free (no API call)')
            ->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_identity_fixtures_are_free(): void
    {
        $this->artisan('research:eval-chat --filter=identity')
            ->expectsOutputToContain('free (no API call)')
            ->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_out_of_scope_fixtures_are_free(): void
    {
        $this->artisan('research:eval-chat --filter=out-of-scope')
            ->expectsOutputToContain('free (no API call)')
            ->assertSuccessful();

        Http::assertNothingSent();
    }

    /**
     * The harness must not report a routing mismatch on its own fixtures. If
     * this fails, either a classifier changed behaviour or a fixture label is
     * stale — and in both cases a developer needs to know which.
     */
    public function test_the_free_fixtures_route_as_their_labels_claim(): void
    {
        foreach (['smalltalk', 'identity', 'out-of-scope'] as $type) {
            $this->artisan("research:eval-chat --filter={$type}")
                ->doesntExpectOutputToContain('Routing disagreed with the fixture label')
                ->assertSuccessful();
        }
    }

    /**
     * Permission violations are the harness's one hard pass/fail, and a run
     * that finds none must exit 0 — otherwise it cannot be trusted as a gate
     * for the thing it does gate.
     */
    public function test_a_clean_run_exits_successfully(): void
    {
        $this->artisan('research:eval-chat --filter=smalltalk')
            ->expectsOutputToContain('Permission violations: 0')
            ->assertSuccessful();
    }
    // --- Token report and baseline diffing ------------------------------
    //
    // Options are passed as an ARRAY, not inside the command string: on Windows
    // storage_path() returns a mixed-separator path whose backslashes the
    // string form treats as escapes, so --save-baseline silently received a
    // mangled value and wrote nothing.

    public function test_the_token_report_lists_free_turns(): void
    {
        $this->artisan('research:eval-chat --filter=identity --tokens')
            ->expectsOutputToContain('Token report')
            ->assertSuccessful();
    }

    public function test_a_baseline_round_trip_reports_no_change(): void
    {
        $path = storage_path('app/testing/baseline.json');

        $this->artisan('research:eval-chat', ['--filter' => 'identity', '--save-baseline' => $path])
            ->assertSuccessful();

        $this->assertFileExists($path);

        $saved = json_decode((string) file_get_contents($path), true);
        $this->assertArrayHasKey('fixtures', $saved);
        $this->assertArrayHasKey('who are you?', $saved['fixtures']);

        $this->artisan('research:eval-chat', ['--filter' => 'identity', '--baseline' => $path])
            ->expectsOutputToContain('No fixture changed outcome.')
            ->assertSuccessful();

        @unlink($path);
    }

    /**
     * The assertion the whole measurement effort rests on. A token saving that
     * also broke grounding is a regression wearing a saving's clothes, and a
     * table of Δtokens alone would report it as a win.
     */
    public function test_an_outcome_change_is_flagged_even_when_tokens_dropped(): void
    {
        $path = storage_path('app/testing/baseline-regression.json');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        // A baseline claiming this question used to be answered with citations
        // and cost 2,500 tokens. It is now free — a huge apparent saving that
        // is actually a behaviour change.
        file_put_contents($path, json_encode([
            'saved_at' => now()->toIso8601String(),
            'prompt_version' => 'v5',
            'fixtures' => [
                'who are you?' => [
                    'outcome' => 'grounded, cited',
                    'prompt_tokens' => 2500,
                    'completion_tokens' => 300,
                    'usd' => 0.0005,
                ],
            ],
        ]));

        $this->artisan('research:eval-chat', ['--filter' => 'identity', '--baseline' => $path])
            ->expectsOutputToContain('changed outcome')
            ->assertSuccessful();

        @unlink($path);
    }

    public function test_a_missing_baseline_is_reported_without_crashing(): void
    {
        $this->artisan('research:eval-chat', ['--filter' => 'identity', '--baseline' => storage_path('app/nope.json')])
            ->expectsOutputToContain('No baseline at')
            ->assertSuccessful();
    }

    public function test_a_file_that_is_not_a_baseline_is_rejected(): void
    {
        $path = storage_path('app/testing/not-a-baseline.json');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        file_put_contents($path, json_encode(['something' => 'else']));

        $this->artisan('research:eval-chat', ['--filter' => 'identity', '--baseline' => $path])
            ->expectsOutputToContain('is not a baseline file')
            ->assertSuccessful();

        @unlink($path);
    }
}
