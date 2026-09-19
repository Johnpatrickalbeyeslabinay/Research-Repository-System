<?php

namespace Tests\Feature\Chat;

use App\Models\User;
use App\Models\UserFile;
use App\Services\Retrieval\CatalogService;
use App\Services\Retrieval\QueryPlanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * CatalogService is a SECOND, independent way into the research corpus.
 * Chunk retrieval enforces ResearchAccess inside its raw SQL; this path
 * enforces it through ResearchAccess::scope() instead, and a second door
 * is exactly where a permission model tends to spring a leak. Those
 * assertions are the point of this file; the listing behaviour is
 * secondary.
 *
 * The semantic arm needs pgvector and so cannot run against the sqlite
 * test database — passing a null query vector exercises the structured
 * arm, which is the one that enforces permissions and produces the totals.
 * The semantic arm is covered live by `php artisan research:eval-chat`.
 */
class CatalogRetrievalTest extends TestCase
{
    use RefreshDatabase;

    private CatalogService $catalog;

    private QueryPlanner $planner;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->catalog = app(CatalogService::class);
        $this->planner = app(QueryPlanner::class);
    }

    private function browse(string $question, ?User $user)
    {
        return $this->catalog->browse($user, $this->planner->plan($question, null, null), null);
    }

    private function ready(array $attributes = []): UserFile
    {
        return UserFile::factory()->approved()->publicVisibility()->create(
            array_merge(['search_index_status' => 'ready'], $attributes)
        );
    }

    public function test_it_lists_public_approved_research(): void
    {
        $this->ready(['title' => 'Seaweed Diversity']);

        $result = $this->browse('list repository available', User::factory()->student()->create());

        $this->assertSame(1, $result->total);
        $this->assertSame('Seaweed Diversity', $result->documents->first()->title);
    }

    public function test_it_never_lists_another_students_private_research(): void
    {
        $owner = User::factory()->student()->create();
        UserFile::factory()->pending()->create([
            'user_id' => $owner->id,
            'title' => 'Unpublished Draft',
            'search_index_status' => 'ready',
        ]);

        $stranger = User::factory()->student()->create();

        $result = $this->browse('list repository available', $stranger);

        $this->assertTrue($result->isEmpty());
        $this->assertSame(0, $result->total);
    }

    public function test_the_owner_adviser_and_admin_do_see_their_own_pending_research(): void
    {
        $owner = User::factory()->student()->create();
        $adviser = User::factory()->instructor()->create();

        UserFile::factory()->pending()->create([
            'user_id' => $owner->id,
            'adviser_id' => $adviser->id,
            'search_index_status' => 'ready',
        ]);

        foreach ([$owner, $adviser, User::factory()->admin()->create()] as $user) {
            $this->assertSame(
                1,
                $this->browse('list repository available', $user)->total,
                "Expected {$user->role} to see the pending research they are connected to"
            );
        }
    }

    public function test_anonymous_visitors_see_only_public_approved_research(): void
    {
        $this->ready(['title' => 'Public Study']);
        UserFile::factory()->pending()->create(['search_index_status' => 'ready']);

        $result = $this->browse('list repository available', null);

        $this->assertSame(1, $result->total);
        $this->assertSame('Public Study', $result->documents->first()->title);
    }

    public function test_unindexed_research_is_not_listed(): void
    {
        // Listing a document the other retrieval arms can't answer from
        // would advertise something the chatbot then fails to discuss.
        $this->ready(['title' => 'Indexed']);
        UserFile::factory()->approved()->publicVisibility()->create([
            'title' => 'Still Indexing',
            'search_index_status' => 'processing',
        ]);

        $result = $this->browse('list repository available', null);

        $this->assertSame(1, $result->total);
        $this->assertSame('Indexed', $result->documents->first()->title);
    }

    public function test_an_empty_repository_produces_the_zero_token_message(): void
    {
        $result = $this->browse('list repository available', User::factory()->student()->create());

        $this->assertTrue($result->isEmpty());
        $this->assertStringContainsString(
            "doesn't have any research you can access",
            $this->catalog->emptyMessage($result->filters)
        );
    }

    public function test_an_empty_filtered_result_explains_which_filters_were_applied(): void
    {
        $this->ready(['publication_year' => 2020]);

        $plan = $this->planner->plan('What studies were published from 2024 to 2026?', null, null);
        $result = $this->catalog->browse(User::factory()->student()->create(), $plan, null);

        $this->assertTrue($result->isEmpty());

        // "Nothing found" must be explainable, or the user has no next move.
        $message = $this->catalog->emptyMessage($result->filters);
        $this->assertStringContainsString('2024-2026', $message);
    }

    public function test_the_year_filter_narrows_the_listing(): void
    {
        $this->ready(['title' => 'Recent', 'publication_year' => 2025]);
        $this->ready(['title' => 'Old', 'publication_year' => 2015]);

        $plan = $this->planner->plan('What studies were published from 2024 to 2026?', null, null);
        $result = $this->catalog->browse(null, $plan, null);

        $this->assertSame(1, $result->total);
        $this->assertSame('Recent', $result->documents->first()->title);
    }

    public function test_duplicate_pdfs_are_collapsed_but_still_counted(): void
    {
        // The same file submitted three times is one piece of content, not
        // three recommendations.
        foreach (['A', 'B', 'C'] as $title) {
            $this->ready(['title' => $title, 'pdf_hash' => str_repeat('a', 64)]);
        }

        $result = $this->browse('list repository available', null);

        $this->assertCount(1, $result->documents);
    }

    public function test_the_display_cap_does_not_distort_the_reported_total(): void
    {
        // "How many studies are there?" must answer from the real count,
        // not the page size.
        config(['chat.catalog.max_documents' => 3]);

        for ($i = 0; $i < 7; $i++) {
            $this->ready(['title' => "Study {$i}", 'pdf_hash' => str_repeat((string) $i, 64)]);
        }

        $result = $this->browse('list repository available', null);

        $this->assertCount(3, $result->documents);
        $this->assertSame(7, $result->total);
        $this->assertTrue($result->isTruncated());
    }
}
