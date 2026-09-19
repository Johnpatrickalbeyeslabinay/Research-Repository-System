<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * GET /research/{id} is where every chatbot citation and every "you might
 * mean this" suggestion card lands. It previously hardcoded an
 * approved+public filter that was STRICTER than App\Support\ResearchAccess,
 * so a student clicking a citation to their own pending submission got a
 * 404 for research they are explicitly allowed to read.
 *
 * The two halves matter equally: people with access must get the page, and
 * nobody else may — a 404 that became a 200 for the wrong person would be
 * a far worse bug than the one being fixed.
 */
class ResearchShowPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_open_approved_public_research(): void
    {
        $file = UserFile::factory()->approved()->publicVisibility()->create();

        $this->get("/research/{$file->id}")->assertOk();
        $this->actingAs(User::factory()->student()->create())
            ->get("/research/{$file->id}")->assertOk();
    }

    public function test_the_submitter_can_open_their_own_pending_research(): void
    {
        // The exact failure a chatbot citation produced.
        $owner = User::factory()->student()->create();
        $file = UserFile::factory()->pending()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)->get("/research/{$file->id}")->assertOk();
    }

    public function test_the_adviser_and_an_admin_can_open_it_too(): void
    {
        $adviser = User::factory()->instructor()->create();
        $file = UserFile::factory()->pending()->create([
            'user_id' => User::factory()->student()->create()->id,
            'adviser_id' => $adviser->id,
        ]);

        $this->actingAs($adviser)->get("/research/{$file->id}")->assertOk();
        $this->actingAs(User::factory()->admin()->create())->get("/research/{$file->id}")->assertOk();
    }

    public function test_a_listed_co_author_can_open_it(): void
    {
        $coAuthor = User::factory()->student()->create();
        $file = UserFile::factory()->pending()->create([
            'user_id' => User::factory()->student()->create()->id,
        ]);
        $file->authors()->create([
            'user_id' => $coAuthor->id,
            'author_name' => $coAuthor->name,
            'position' => 0,
        ]);

        $this->actingAs($coAuthor)->get("/research/{$file->id}")->assertOk();
    }

    public function test_an_unrelated_student_still_gets_a_404(): void
    {
        $file = UserFile::factory()->pending()->create([
            'user_id' => User::factory()->student()->create()->id,
        ]);

        $this->actingAs(User::factory()->student()->create())
            ->get("/research/{$file->id}")->assertNotFound();
    }

    public function test_anonymous_visitors_still_cannot_reach_private_research(): void
    {
        // The half that must NOT have changed: guessing an id from the
        // public page must still lead nowhere.
        $file = UserFile::factory()->pending()->create();

        $this->get("/research/{$file->id}")->assertNotFound();
    }

    public function test_the_action_buttons_are_not_hidden_behind_a_fixed_footer(): void
    {
        // The footer was .fixed-bottom, which parks it out of flow over the
        // last ~80px of the page — covering View PDF / Download / Ask about
        // this research with no way to scroll past. Asserting the class is
        // gone is a crude proxy for a visual bug, but it is the part that
        // actually regresses: the buttons themselves were always in the
        // markup, just permanently underneath something.
        $owner = User::factory()->student()->create();
        $file = UserFile::factory()->approved()->publicVisibility()->create(['user_id' => $owner->id]);

        $html = $this->actingAs($owner)->get("/research/{$file->id}")->assertOk()->getContent();

        // Matched on the <footer> tag itself, not anywhere in the document
        // — the layout's CSS comment explains the old class by name.
        $this->assertDoesNotMatchRegularExpression('/<footer[^>]*fixed-bottom/', $html);
        $this->assertStringContainsString('View PDF', $html);
        $this->assertStringContainsString('Ask about this research', $html);
    }

    public function test_approved_but_private_research_is_not_public(): void
    {
        // Approval alone doesn't publish — the student must also have
        // requested public visibility.
        $file = UserFile::factory()->approved()->create(['visibility' => 'private']);

        $this->get("/research/{$file->id}")->assertNotFound();
    }
}
