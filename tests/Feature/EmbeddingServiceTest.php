<?php

namespace Tests\Feature;

use App\Exceptions\EmbeddingFailedException;
use App\Services\Research\EmbeddingService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * The embedding client's failure contract. Its exception message reaches
 * stud_file.search_index_error and, before ChatTurnService translated it, the
 * chat widget — so what it may and may not contain is the thing worth pinning.
 */
class EmbeddingServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.openai.api_key' => 'test-key']);
        Http::preventStrayRequests();
    }

    public function test_a_rejected_key_reports_the_status_and_code_but_never_the_provider_body(): void
    {
        Http::fake([
            'api.openai.com/v1/embeddings' => Http::response([
                'error' => [
                    'message' => 'Incorrect API key provided: sk-proj-****SYcA.',
                    'type' => 'invalid_request_error',
                    'code' => 'invalid_api_key',
                ],
            ], 401),
        ]);
        Log::spy();

        try {
            app(EmbeddingService::class)->embedBatch(['fish']);
            $this->fail('A 401 should have thrown.');
        } catch (EmbeddingFailedException $e) {
            $this->assertSame('Embedding request failed (HTTP 401: invalid_api_key).', $e->getMessage());
            $this->assertStringNotContainsString('sk-proj', $e->getMessage());
        }

        // The body is not lost — it moved to the server log.
        Log::shouldHaveReceived('warning')->withArgs(
            fn ($message, $context) => str_contains($context['body'] ?? '', 'Incorrect API key')
        );
    }

    public function test_an_unreachable_provider_is_a_typed_failure(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 28: timed out'));

        $this->expectException(EmbeddingFailedException::class);
        $this->expectExceptionMessage('could not be reached');

        app(EmbeddingService::class)->embedBatch(['fish']);
    }

    public function test_a_missing_key_is_a_typed_failure_without_any_request(): void
    {
        config(['services.openai.api_key' => '']);

        $this->expectException(EmbeddingFailedException::class);

        try {
            app(EmbeddingService::class)->embedBatch(['fish']);
        } finally {
            Http::assertNothingSent();
        }
    }
}
