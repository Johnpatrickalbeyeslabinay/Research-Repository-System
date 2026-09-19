<?php

namespace App\Services\Chat;

use App\Exceptions\ChatUnavailableException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The single OpenAI chat completions call that produces a grounded answer
 * from App\Services\Chat\PromptBuilder's messages. Plain text output, not
 * a JSON schema — [n] citation markers parse reliably with a plain regex
 * (see CitationBuilder), and plain text keeps the door open for streaming
 * later without a response_format migration. Shaped like
 * App\Services\Ai\MetadataAnalysisService's OpenAI call (timeout,
 * max_tokens, typed exception on failure) — the house pattern for a
 * generative call in this app.
 */
class ChatAnswerService
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages  from PromptBuilder::build()['messages']
     * @param  ?int  $maxOutputTokens  overrides
     *         config('chat.models.max_output_tokens') for this call — a
     *         catalog turn answers with a short list and doesn't need the
     *         full research-answer ceiling (see config('chat.catalog')).
     * @return array{content: string, model: string, prompt_tokens: ?int, completion_tokens: ?int, latency_ms: int}
     *
     * @throws ChatUnavailableException
     */
    public function answer(array $messages, ?int $maxOutputTokens = null): array
    {
        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            throw ChatUnavailableException::notConfigured();
        }

        $model = config('chat.models.answer');
        $start = microtime(true);

        try {
            $response = Http::withToken($apiKey)
                ->timeout((int) config('chat.models.timeout', 45))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'max_tokens' => $maxOutputTokens ?? (int) config('chat.models.max_output_tokens', 700),
                    'temperature' => 0.2,
                    'messages' => $messages,
                ]);
        } catch (\Throwable $e) {
            Log::warning('Chat answer call failed', ['error' => $e->getMessage()]);

            throw ChatUnavailableException::providerError();
        }

        $latencyMs = (int) round((microtime(true) - $start) * 1000);

        if ($response->failed()) {
            Log::warning('Chat answer call returned an error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw ChatUnavailableException::providerError();
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw ChatUnavailableException::malformedResponse();
        }

        return [
            'content' => trim($content),
            'model' => $model,
            'prompt_tokens' => $response->json('usage.prompt_tokens'),
            'completion_tokens' => $response->json('usage.completion_tokens'),
            'latency_ms' => $latencyMs,
        ];
    }
}
