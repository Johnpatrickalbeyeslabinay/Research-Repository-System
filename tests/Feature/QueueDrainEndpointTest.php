<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * POST /internal/queue/drain is the one route in this app that is not
 * session-authenticated — a cron has no session — so its shared secret is
 * the ONLY thing standing between the public internet and a queue runner.
 * That makes these assertions load-bearing rather than routine.
 */
class QueueDrainEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_404s_when_no_secret_is_configured(): void
    {
        // An unconfigured deployment must fail closed. 404 rather than 403
        // so the endpoint's existence isn't advertised.
        config(['research.cron_secret' => '']);

        $this->postJson('/internal/queue/drain')->assertNotFound();
    }

    public function test_it_rejects_a_missing_secret(): void
    {
        config(['research.cron_secret' => 'correct-horse']);

        $this->postJson('/internal/queue/drain')->assertForbidden();
    }

    public function test_it_rejects_a_wrong_secret(): void
    {
        config(['research.cron_secret' => 'correct-horse']);

        $this->postJson('/internal/queue/drain', [], ['X-Cron-Secret' => 'battery-staple'])
            ->assertForbidden();
    }

    public function test_it_drains_the_queue_with_the_correct_secret(): void
    {
        config(['research.cron_secret' => 'correct-horse']);

        $this->postJson('/internal/queue/drain', [], ['X-Cron-Secret' => 'correct-horse'])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_it_accepts_a_get_with_a_bearer_token(): void
    {
        // The form Vercel Cron actually uses: GET + Authorization: Bearer.
        config(['research.cron_secret' => 'correct-horse']);

        $this->getJson('/internal/queue/drain', ['Authorization' => 'Bearer correct-horse'])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_it_rejects_a_wrong_bearer_token(): void
    {
        config(['research.cron_secret' => 'correct-horse']);

        $this->getJson('/internal/queue/drain', ['Authorization' => 'Bearer battery-staple'])
            ->assertForbidden();
    }

    public function test_a_prefix_of_the_secret_is_not_accepted(): void
    {
        // Guards against a truthy/loose comparison creeping in where
        // hash_equals() belongs.
        config(['research.cron_secret' => 'correct-horse']);

        $this->postJson('/internal/queue/drain', [], ['X-Cron-Secret' => 'correct'])
            ->assertForbidden();
    }
}
