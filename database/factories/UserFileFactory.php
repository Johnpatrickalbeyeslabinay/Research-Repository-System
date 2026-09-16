<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserFile>
 */
class UserFileFactory extends Factory
{
    protected $model = UserFile::class;

    /**
     * Defaults to pending + private — the state every new submission
     * starts in (see StudentController::store()) and the state
     * ResearchAccess must be strictest about.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'title' => fake()->sentence(6),
            'name' => fake()->name(),
            'adviser' => null,
            'adviser_id' => null,
            'desc' => fake()->paragraph(),
            'file_path' => 'research_files/'.fake()->sha256().'.pdf',
            'auto_tag' => null,
            'views' => 0,
            'downloads' => 0,
            'status' => 'pending',
            'visibility' => 'private',
            'requested_visibility' => 'private',
            'research_type' => null,
            'publication_year' => null,
            'pdf_hash' => fake()->sha256(),
            'pdf_page_count' => null,
            'search_index_status' => 'not_started',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved']);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function publicVisibility(): static
    {
        return $this->state(fn () => ['visibility' => 'public', 'requested_visibility' => 'public']);
    }

    public function searchReady(): static
    {
        return $this->state(fn () => ['search_index_status' => 'ready']);
    }
}
