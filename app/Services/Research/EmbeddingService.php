<?php

namespace App\Services\Research;

use Illuminate\Support\Facades\Http;

/**
 * Generates embeddings for pre-chunked document text via OpenAI's
 * embeddings endpoint, batched. Purely an embedding call — never routed
 * through the generative metadata model, and never triggers a second
 * generative call itself.
 */
class EmbeddingService
{
    private const BATCH_SIZE = 100;

    /**
     * Returns the vectors AND what they cost.
     *
     * The token count is reported, not logged, because this class stays a
     * pure API client — the same split App\Services\Chat\ChatAnswerService
     * already uses, where the caller owns the AiUsageLog write. That matters
     * here: the two callers need different rows (ProcessResearchDocument
     * logs 'doc_embedding' against the submitting student,
     * RetrievalService logs 'query_embedding' against the asking user), and
     * neither piece of context belongs in an embedding client.
     *
     * Until this returned usage, document embedding was completely absent
     * from ai_usage_logs despite the table's own comment listing
     * 'doc_embedding' as an expected operation — so indexing spend was
     * invisible, and a token optimisation could not be validated against
     * the largest single consumer of the API key.
     *
     * 'embeddings' holds one vector per input, in the same order as $texts.
     *
     * @param  string[]  $texts
     * @return array{embeddings: array<int, float[]>, prompt_tokens: int}
     *
     * @throws \RuntimeException
     */
    public function embedBatch(array $texts): array
    {
        if (empty($texts)) {
            return ['embeddings' => [], 'prompt_tokens' => 0];
        }

        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            throw new \RuntimeException('OpenAI API key is not configured.');
        }

        $model = config('services.openai.embedding_model');
        $embeddings = [];
        $promptTokens = 0;

        foreach (array_chunk($texts, self::BATCH_SIZE) as $batch) {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.openai.com/v1/embeddings', [
                    'model' => $model,
                    'input' => array_values($batch),
                ]);

            if ($response->failed()) {
                throw new \RuntimeException('Embedding request failed: '.$response->body());
            }

            $data = $response->json('data');

            if (! is_array($data) || count($data) !== count($batch)) {
                throw new \RuntimeException('Unexpected embedding response shape.');
            }

            // OpenAI returns items tagged with their input index — sort to
            // guarantee output order matches input order regardless of it.
            usort($data, fn ($a, $b) => ($a['index'] ?? 0) <=> ($b['index'] ?? 0));

            foreach ($data as $item) {
                $embeddings[] = $item['embedding'];
            }

            // Accumulated across batches: a 400-chunk document is 4 HTTP
            // calls, and the caller wants one figure for the document.
            $promptTokens += (int) $response->json('usage.prompt_tokens', 0);
        }

        return ['embeddings' => $embeddings, 'prompt_tokens' => $promptTokens];
    }
}
