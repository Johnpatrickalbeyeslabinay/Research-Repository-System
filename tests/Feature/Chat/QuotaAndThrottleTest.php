<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\User;
use App\Support\ChatQuota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * The two spend limits, each guarding against the specific way it was
 * already broken:
 *
 *  - ChatQuota::limit() fell back to 120 while config/chat.php said 50, so a
 *    stale config:cache silently granted 2.4x the intended daily budget.
 *  - The message route hardcoded throttle:6,1 while config/chat.php's
 *    comment claimed 'per_minute' backed it — so raising
 *    CHAT_RATE_PER_MINUTE changed nothing at all.
 *
 * The throttle test is only passable once the limiter actually reads the
 * config, which is what makes it a proof rather than a restatement.
 */
class QuotaAndThrottleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.openai.api_key' => 'test-key']);

        Http::preventStrayRequests();

        $this->user = User::factory()->student()->create();
    }

    public function test_the_quota_limit_matches_the_configured_value(): void
    {
        $this->assertSame((int) config('chat.limits.per_day'), ChatQuota::limit());
    }

    /**
     * The fallback path specifically — the only situation where the old
     * 120-vs-50 mismatch could actually bite.
     */
    public function test_the_quota_falls_back_to_the_documented_default_when_the_key_is_missing(): void
    {
        config(['chat.limits.per_day' => null]);

        $this->assertSame(
            50,
            ChatQuota::limit(),
            'The in-code fallback must match config/chat.php, or a stale config:cache changes the spend cap.'
        );
    }

    /**
     * Not zero: failing closed on a missing quota key would be a denial of
     * service against our own users. Fail-closed is correct for permissions
     * (App\Support\ResearchAccess), wrong for a spend cap.
     */
    public function test_a_missing_quota_key_does_not_lock_everyone_out(): void
    {
        config(['chat.limits.per_day' => null]);

        $this->assertGreaterThan(0, ChatQuota::remaining($this->user->id));
    }

    public function test_the_burst_limiter_reads_the_configured_per_minute_value(): void
    {
        // Registered in AppServiceProvider::boot() and resolved per request,
        // so setting the config here is enough — no need to re-register.
        config(['chat.limits.per_minute' => 1]);
        RateLimiter::clear('chat');

        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'An answer.']]],
                'usage' => ['prompt_tokens' => 100, 'completion_tokens' => 10],
            ]),
        ]);

        $conversation = ChatConversation::create(['user_id' => $this->user->id]);

        // "hi" is a zero-token path, so this consumes a throttle slot
        // without needing retrieval or a real corpus.
        $first = $this->actingAs($this->user)
            ->postJson("/chat/conversations/{$conversation->id}/messages", ['message' => 'hi']);
        $first->assertOk();

        $second = $this->actingAs($this->user)
            ->postJson("/chat/conversations/{$conversation->id}/messages", ['message' => 'hello']);

        $second->assertStatus(
            429,
            'With per_minute=1 the second request must be throttled. A 200 here means the route is '
                .'still using a hardcoded limit instead of the named "chat" limiter.'
        );
    }

    public function test_a_higher_configured_limit_allows_a_second_request(): void
    {
        config(['chat.limits.per_minute' => 10]);
        RateLimiter::clear('chat');

        $conversation = ChatConversation::create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->postJson("/chat/conversations/{$conversation->id}/messages", ['message' => 'hi'])
            ->assertOk();

        $this->actingAs($this->user)
            ->postJson("/chat/conversations/{$conversation->id}/messages", ['message' => 'thanks'])
            ->assertOk();

        // Neither turn reached the LLM, so neither should have cost quota.
        Http::assertNothingSent();
        $this->assertSame(0, ChatQuota::used($this->user->id));
    }
}
