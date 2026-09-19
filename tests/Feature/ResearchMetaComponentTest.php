<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * The AI-index status row on x-research.meta. Only documents at
 * search_index_status='ready' are retrievable (see VectorSearchService's
 * WHERE clause), so an unindexed paper is invisible to every chatbot answer
 * — this row is the only place that is ever explained. It is an operational
 * detail, though, so it must not leak to readers who can't act on it.
 */
class ResearchMetaComponentTest extends TestCase
{
    use RefreshDatabase;

    private function render(UserFile $file): string
    {
        $file->load(['authors', 'keywords', 'sdgs']);

        return Blade::render('<x-research.meta :file="$file" />', ['file' => $file]);
    }

    public function test_the_owner_is_told_their_paper_is_not_yet_searchable(): void
    {
        $owner = User::factory()->student()->create();
        $file = UserFile::factory()->pending()->create([
            'user_id' => $owner->id,
            'search_index_status' => 'not_started',
        ]);

        $this->actingAs($owner);

        $this->assertStringContainsString('Queued for AI search indexing', $this->render($file));
    }

    public function test_a_failed_index_is_reported_with_a_recovery_hint(): void
    {
        $owner = User::factory()->student()->create();
        $file = UserFile::factory()->pending()->create([
            'user_id' => $owner->id,
            'search_index_status' => 'failed',
        ]);

        $this->actingAs($owner);

        $output = $this->render($file);

        $this->assertStringContainsString('Not searchable', $output);
        $this->assertStringContainsString('retry', $output);
    }

    public function test_the_adviser_also_sees_it(): void
    {
        $adviser = User::factory()->instructor()->create();
        $file = UserFile::factory()->pending()->create([
            'user_id' => User::factory()->student()->create()->id,
            'adviser_id' => $adviser->id,
            'search_index_status' => 'processing',
        ]);

        $this->actingAs($adviser);

        $this->assertStringContainsString('Indexing for AI search', $this->render($file));
    }

    public function test_an_unrelated_reader_is_not_shown_indexing_internals(): void
    {
        $file = UserFile::factory()->approved()->publicVisibility()->create([
            'user_id' => User::factory()->student()->create()->id,
            'search_index_status' => 'failed',
        ]);

        $this->actingAs(User::factory()->student()->create());

        $output = $this->render($file);

        $this->assertStringNotContainsString('Not searchable', $output);
        $this->assertStringNotContainsString('indexing', strtolower($output));
    }

    public function test_a_ready_document_shows_no_notice_at_all(): void
    {
        $owner = User::factory()->student()->create();
        $file = UserFile::factory()->approved()->publicVisibility()->create([
            'user_id' => $owner->id,
            'search_index_status' => 'ready',
        ]);

        $this->actingAs($owner);

        $output = $this->render($file);

        $this->assertStringNotContainsString('AI search', $output);
        // The rest of the component still renders.
        $this->assertStringContainsString('Researcher', $output);
    }

    public function test_it_renders_for_a_guest_without_erroring(): void
    {
        // auth()->user() is null on the public research page.
        $file = UserFile::factory()->approved()->publicVisibility()->create([
            'search_index_status' => 'not_started',
        ]);

        $output = $this->render($file);

        $this->assertStringNotContainsString('Queued for AI', $output);
    }
}
