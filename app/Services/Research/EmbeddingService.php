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
     * @param  string[]  $texts
     * @return array<int, float[]> one embedding vector per input, same order
     *
     * @throws \RuntimeException
     */
    public function embedBatch(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            throw new \RuntimeException('OpenAI API key is not configured.');
        }

        $model = config('services.openai.embedding_model');
        $embeddings = [];

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
        }

        return $embeddings;
    }
}
