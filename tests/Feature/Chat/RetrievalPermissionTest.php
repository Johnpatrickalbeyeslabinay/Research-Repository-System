<?php

namespace Tests\Feature\Chat;

use App\Models\User;
use App\Models\UserFile;
use App\Support\ResearchAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * App\Support\ResearchAccess is the single security boundary the AI
 * chatbot's retrieval query depends on: a chunk from research a user isn't
 * allowed to read must never be retrievable, in either form the rule is
 * expressed in — allows() (a single loaded row, used by
 * StudentController::authorizeFileAccess()) and sqlPredicate()/scope() (a
 * live query, used by the future VectorSearchService). This test asserts
 * both forms agree for every role this app has, run against a real
 * Postgres database (see phpunit.xml — sqlite can't run this schema).
 */
class RetrievalPermissionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $owner;

    private User $adviser;

    private User $coAuthor;

    private User $otherStudent;

    private UserFile $publicApproved;

    private UserFile $privatePending;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->owner = User::factory()->student()->create();
        $this->adviser = User::factory()->instructor()->create();
        $this->coAuthor = User::factory()->student()->create();
        $this->otherStudent = User::factory()->student()->create();

        $this->publicApproved = UserFile::factory()
            ->approved()
            ->publicVisibility()
            ->create(['user_id' => User::factory()->student()->create()->id]);

        $this->privatePending = UserFile::factory()
            ->pending()
            ->create([
                'user_id' => $this->owner->id,
                'adviser_id' => $this->adviser->id,
            ]);

        $this->privatePending->authors()->create([
            'user_id' => $this->coAuthor->id,
            'author_name' => $this->coAuthor->name,
            'position' => 0,
        ]);

        $this->privatePending->load('authors');
    }

    public function test_public_approved_research_is_visible_to_everyone(): void
    {
        foreach ([null, $this->admin, $this->owner, $this->adviser, $this->coAuthor, $this->otherStudent] as $user) {
            $this->assertTrue(
                ResearchAccess::allows($user, $this->publicApproved),
                'Expected public+approved research to be visible to '.($user?->name ?? 'anonymous')
            );
        }
    }

    public function test_private_pending_research_is_restricted_to_the_people_connected_to_it(): void
    {
        foreach ([$this->admin, $this->owner, $this->adviser, $this->coAuthor] as $user) {
            $this->assertTrue(
                ResearchAccess::allows($user, $this->privatePending),
                "Expected {$user->name} ({$user->role}) to have access to the private/pending research"
            );
        }

        foreach ([null, $this->otherStudent] as $user) {
            $this->assertFalse(
                ResearchAccess::allows($user, $this->privatePending),
                'Expected '.($user?->name ?? 'anonymous').' to be denied access to the private/pending research'
            );
        }
    }

    public function test_pending_research_remains_visible_to_its_own_author_and_adviser(): void
    {
        // A pending submission isn't approved yet, but the student who
        // submitted it and the adviser reviewing it must still be able to
        // ask the chatbot about it — intended, not a leak (see
        // ResearchAccess's class docblock and this app's approval
        // lifecycle: pending -> admin_pending -> approved).
        $this->assertTrue(ResearchAccess::allows($this->owner, $this->privatePending));
        $this->assertTrue(ResearchAccess::allows($this->adviser, $this->privatePending));
    }

    public function test_sql_predicate_agrees_with_the_row_level_check_for_every_role(): void
    {
        $users = [null, $this->admin, $this->owner, $this->adviser, $this->coAuthor, $this->otherStudent];
        $documents = [$this->publicApproved, $this->privatePending];

        foreach ($documents as $document) {
            foreach ($users as $user) {
                $expected = ResearchAccess::allows($user, $document);

                $visibleIds = ResearchAccess::scope(UserFile::query(), $user)->pluck('id')->all();

                $this->assertSame(
                    $expected,
                    in_array($document->id, $visibleIds, true),
                    sprintf(
                        'allows() and scope() disagreed for user=%s document=%d (status=%s, visibility=%s)',
                        $user?->name ?? 'anonymous',
                        $document->id,
                        $document->status,
                        $document->visibility
                    )
                );
            }
        }
    }

    public function test_another_students_private_research_never_appears_in_an_unrelated_students_accessible_list(): void
    {
        // The scenario the whole chatbot security model exists to
        // prevent: student B must never be able to retrieve student A's
        // private paper, by any path.
        $visibleToOtherStudent = ResearchAccess::scope(UserFile::query(), $this->otherStudent)->pluck('id')->all();

        $this->assertNotContains($this->privatePending->id, $visibleToOtherStudent);
    }

    public function test_anonymous_users_never_see_private_research(): void
    {
        $visibleToAnonymous = ResearchAccess::scope(UserFile::query(), null)->pluck('id')->all();

        $this->assertNotContains($this->privatePending->id, $visibleToAnonymous);
        $this->assertContains($this->publicApproved->id, $visibleToAnonymous);
    }
}
