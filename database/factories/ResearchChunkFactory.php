<?php

namespace Database\Factories;

use App\Models\ResearchChunk;
use App\Models\UserFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResearchChunk>
 */
class ResearchChunkFactory extends Factory
{
    protected $model = ResearchChunk::class;

    /**
     * `embedding` is deliberately not set here — Eloquent has no native
     * pgvector type (see ResearchChunk's own docblock), so factory-created
     * rows have a null vector unless a caller writes it separately via raw
     * SQL, same as App\Jobs\ProcessResearchDocument does.
     */
    public function definition(): array
    {
        $content = fake()->paragraph();

        return [
            'stud_file_id' => UserFile::factory(),
            'chunk_index' => 0,
            'section' => fake()->randomElement([
                'metadata', 'abstract', 'introduction', 'methodology', 'results', 'discussion', 'conclusion',
            ]),
            'page_number' => fake()->numberBetween(1, 60),
            'content' => $content,
            'token_count' => (int) round(str_word_count($content) * 4 / 3),
            'content_hash' => hash('sha256', $content),
            'embedding_model' => 'text-embedding-3-small',
            'embedding_version' => 1,
        ];
    }
}
