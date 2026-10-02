<?php

namespace Tests\Feature;

use App\Models\Sdg;
use App\Models\User;
use App\Models\UserFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepositoryBrowseTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_browse_link_opens_the_public_repository(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('public').'"', false)
            ->assertSee('Browse Repository');
    }

    public function test_public_listing_only_contains_approved_public_research_and_uses_public_links(): void
    {
        $published = UserFile::factory()->approved()->publicVisibility()->create([
            'title' => 'Published Mangrove Research',
        ]);
        UserFile::factory()->approved()->create([
            'title' => 'Private Approved Research',
            'visibility' => 'private',
        ]);
        UserFile::factory()->pending()->publicVisibility()->create([
            'title' => 'Pending Public Research',
        ]);

        $this->get(route('public'))
            ->assertOk()
            ->assertSee('Published Mangrove Research')
            ->assertDontSee('Private Approved Research')
            ->assertDontSee('Pending Public Research')
            ->assertSee('href="'.route('public.research.show', $published).'"', false)
            ->assertSee('<turbo-frame id="research-results"', false)
            ->assertDontSee('href="'.route('file.download', $published).'"', false);
    }

    public function test_authenticated_listing_only_contains_approved_research_the_viewer_can_access(): void
    {
        $viewer = User::factory()->student()->create();
        $public = UserFile::factory()->approved()->publicVisibility()->create([
            'title' => 'Shared Coastal Study',
            'research_type' => 'Journal Article',
            'publication_year' => 2024,
        ]);
        $owned = UserFile::factory()->approved()->create([
            'user_id' => $viewer->id,
            'title' => 'Owned Private Thesis',
            'visibility' => 'private',
            'research_type' => 'Thesis',
            'publication_year' => 2023,
        ]);
        $inaccessible = UserFile::factory()->approved()->create([
            'title' => 'Unrelated Confidential Paper',
            'visibility' => 'private',
            'research_type' => 'Restricted Type',
            'publication_year' => 1997,
        ]);
        UserFile::factory()->pending()->create([
            'user_id' => $viewer->id,
            'title' => 'Owned Pending Draft',
        ]);
        $accessibleGoal = Sdg::create(['number' => 14]);
        $restrictedGoal = Sdg::create(['number' => 15]);
        $owned->sdgs()->attach($accessibleGoal);
        $inaccessible->sdgs()->attach($restrictedGoal);

        $response = $this->actingAs($viewer)->get(route('repository'));

        $response
            ->assertOk()
            ->assertSee($public->title)
            ->assertSee($owned->title)
            ->assertDontSee('Unrelated Confidential Paper')
            ->assertDontSee('Owned Pending Draft')
            ->assertSee('href="'.route('research.show', $owned).'"', false)
            ->assertSee('href="'.route('file.download', $owned).'"', false)
            ->assertViewHas('types', fn ($types) => $types->contains('Thesis')
                && $types->contains('Journal Article')
                && ! $types->contains('Restricted Type'))
            ->assertViewHas('years', fn ($years) => $years->contains(2023)
                && $years->contains(2024)
                && ! $years->contains(1997))
            ->assertViewHas('sdgs', fn ($sdgs) => $sdgs->contains('id', $accessibleGoal->id)
                && ! $sdgs->contains('id', $restrictedGoal->id));
    }

    public function test_public_detail_actions_match_the_visitors_authentication_state(): void
    {
        $file = UserFile::factory()->approved()->publicVisibility()->create();

        $this->get(route('public.research.show', $file))
            ->assertOk()
            ->assertSee('Sign in to view PDF')
            ->assertDontSee('Open in repository')
            ->assertDontSee('href="'.route('file.view', $file).'"', false);

        $this->actingAs(User::factory()->student()->create())
            ->get(route('public.research.show', $file))
            ->assertOk()
            ->assertSee('Open in repository')
            ->assertSee('href="'.route('research.show', $file).'"', false)
            ->assertDontSee('Sign in to view PDF');
    }

    public function test_authenticated_detail_has_working_pdf_actions(): void
    {
        $viewer = User::factory()->student()->create();
        $file = UserFile::factory()->approved()->create([
            'user_id' => $viewer->id,
            'visibility' => 'private',
        ]);

        $this->actingAs($viewer)
            ->get(route('research.show', $file))
            ->assertOk()
            ->assertSee('href="'.route('file.view', $file).'"', false)
            ->assertSee('href="'.route('file.download', $file).'"', false)
            ->assertSee('Ask about this research');
    }

    public function test_detail_back_link_preserves_a_valid_filtered_listing_and_rejects_other_origins(): void
    {
        $file = UserFile::factory()->approved()->publicVisibility()->create();
        $filteredListing = route('public', ['type' => 'Thesis', 'year' => 2024]);

        $this->withHeader('referer', $filteredListing)
            ->get(route('public.research.show', $file))
            ->assertOk()
            ->assertSee('href="'.e($filteredListing).'"', false);

        $this->withHeader('referer', 'https://example.com/untrusted')
            ->get(route('public.research.show', $file))
            ->assertOk()
            ->assertSee('href="'.route('public').'"', false)
            ->assertDontSee('example.com/untrusted');
    }

    public function test_sorting_and_pagination_keep_repository_query_parameters(): void
    {
        UserFile::factory()->count(13)->approved()->publicVisibility()->create([
            'publication_year' => 2024,
            'research_type' => 'Thesis',
        ]);

        $response = $this->get(route('public', [
            'year' => 2024,
            'type' => 'Thesis',
            'sort' => 'views',
        ]));

        $response
            ->assertOk()
            ->assertSee('year=2024', false)
            ->assertSee('type=Thesis', false)
            ->assertSee('sort=views', false)
            ->assertSee('page=2', false);
    }

    public function test_pdf_routes_remain_protected_for_anonymous_visitors(): void
    {
        $file = UserFile::factory()->approved()->publicVisibility()->create();

        $this->get(route('file.view', $file))->assertRedirect(route('login'));
        $this->get(route('file.download', $file))->assertRedirect(route('login'));
    }
}
