<?php

namespace Tests\Feature;

use App\Models\Sdg;
use App\Models\User;
use App\Models\UserFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The dashboards were rewritten to make fewer round trips to the remote
 * database: lists and counts that used to be separate queries are now
 * sliced from one fetched collection, and each admin tab only loads what
 * it renders. These tests pin that the numbers on screen didn't change in
 * the process — a count that silently drifted would be worse than a slow
 * page.
 */
class DashboardPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_counts_come_from_the_students_own_files_only(): void
    {
        $student = User::factory()->student()->create();
        $adviser = User::factory()->instructor()->create();

        foreach (['pending', 'pending', 'approved', 'rejected'] as $status) {
            UserFile::factory()->create(['user_id' => $student->id, 'status' => $status]);
        }
        // Someone else's submission must not be counted.
        UserFile::factory()->create(['user_id' => User::factory()->student()->create()->id, 'status' => 'rejected']);

        $response = $this->actingAs($student)->get('/student')->assertOk();

        $response->assertViewHas('totalUploads', 4);
        $response->assertViewHas('pendingUploads', 2);
        $response->assertViewHas('approvedUploads', 1);
        $response->assertViewHas('rejectedUploads', 1);
        $response->assertViewHas('rejectedFiles', fn ($files) => $files->count() === 1
            && $files->first()->user_id === $student->id);
        $response->assertViewHas('notifications', fn ($files) => $files->count() === 4);
        $response->assertViewHas('instructors', fn ($people) => $people->pluck('id')->all() === [$adviser->id]);
        $response->assertViewHas('otherStudents', fn ($people) => ! $people->contains('id', $student->id));
    }

    public function test_instructor_dashboard_pending_list_is_the_pending_subset_of_their_queue(): void
    {
        $adviser = User::factory()->instructor()->create();

        UserFile::factory()->pending()->create(['adviser_id' => $adviser->id]);
        UserFile::factory()->approved()->create(['adviser_id' => $adviser->id]);
        UserFile::factory()->pending()->create(['adviser_id' => User::factory()->instructor()->create()->id]);

        $response = $this->actingAs($adviser)->get('/instructor')->assertOk();

        $response->assertViewHas('studFiles', fn ($files) => $files->count() === 2);
        $response->assertViewHas('pendingFiles', fn ($files) => $files->count() === 1
            && $files->first()->status === 'pending');
    }

    public function test_admin_user_tab_is_paginated_and_its_totals_ignore_the_role_filter(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->student()->count(30)->create();
        User::factory()->instructor()->count(2)->create();

        $response = $this->actingAs($admin)
            ->get('/admin?view=user&role=student')
            ->assertOk();

        $response->assertViewHas('users', fn ($users) => $users->total() === 30 && $users->count() === 25);
        $response->assertViewHas('userCounts', fn ($counts) => $counts->get('student') === 30
            && $counts->get('instructor') === 2
            && $counts->get('admin') === 1);
        // The research queue belongs to the dashboard tab, not this one.
        $response->assertViewHas('studFiles', null);
    }

    public function test_pages_no_longer_pull_unversioned_or_oversized_assets(): void
    {
        $html = $this->get('/public')->assertOk()->getContent();

        $this->assertStringNotContainsString('unpkg.com/lucide', $html);
        $this->assertStringNotContainsString('images/icon.png', $html);
        $this->assertStringNotContainsString('bootstrap.bundle.min.js', $html);
        $this->assertStringContainsString('images/favicon-32.png', $html);
        $this->assertStringContainsString('data-turbo-track="reload"', $html);
    }

    public function test_the_sdg_list_is_cached_but_an_empty_one_is_not(): void
    {
        $this->assertTrue(Sdg::cachedList()->isEmpty());
        $this->assertFalse(Cache::has('sdgs:list'));

        Sdg::create(['number' => 1]);

        $this->assertSame([1], Sdg::cachedList()->pluck('number')->all());
        $this->assertTrue(Cache::has('sdgs:list'));
    }
}
