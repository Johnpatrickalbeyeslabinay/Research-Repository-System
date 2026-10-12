<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Ai\AbstractGenerationService;
use App\Services\Pdf\SectionDetector;
use App\Support\SubmissionAiQuota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\Support\MinimalPdf;
use Tests\TestCase;

/**
 * The submission form's AI features: abstract drafts for PDFs without
 * one, paper-grounded keyword suggestions, the per-student daily quota
 * (charged only on a real API call, never on a cache hit), and scanned
 * PDF rejection.
 */
class SubmissionAiTest extends TestCase
{
    use RefreshDatabase;

    private const HASH = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.openai.api_key' => 'test-key']);
    }

    private function fakeOpenAi(array $content): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => json_encode($content)]]],
                'usage' => ['prompt_tokens' => 100, 'completion_tokens' => 50],
            ]),
        ]);
    }

    public function test_keyword_analysis_is_cached_and_only_a_real_call_uses_quota(): void
    {
        $student = User::factory()->student()->create();
        $this->fakeOpenAi(['keywords' => ['rice yield', 'organic fertilizer'], 'sdgs' => []]);

        $payload = ['title' => 'Rice study', 'abstract' => 'This study examined rice yield.'];

        $this->actingAs($student)->postJson('/research/analyze', $payload)
            ->assertOk()
            ->assertJson(['success' => true, 'cached' => false, 'quota' => ['used' => 1, 'remaining' => 9]]);

        // A reload / re-click on unchanged content: same answer, no call, no charge.
        $this->actingAs($student)->postJson('/research/analyze', $payload)
            ->assertOk()
            ->assertJson(['cached' => true, 'keywords' => ['rice yield', 'organic fertilizer'], 'quota' => ['used' => 1]]);

        Http::assertSentCount(1);
    }

    public function test_keyword_analysis_sends_the_paper_excerpt_and_drops_author_keywords(): void
    {
        $student = User::factory()->student()->create();
        $this->fakeOpenAi(['keywords' => ['Rice Yield', 'soil health'], 'sdgs' => []]);

        $this->actingAs($student)->postJson('/research/analyze', [
            'title' => 'Rice study',
            'abstract' => 'This study examined rice yield.',
            'fallback_excerpt' => 'Farmers in Isabela face declining soil fertility.',
            'existing_keywords' => ['rice yield'],
        ])->assertOk()->assertJsonPath('keywords', ['soil health']);

        Http::assertSent(function ($request) {
            $user = $request['messages'][1]['content'];

            return str_contains($user, 'declining soil fertility')
                && str_contains($user, 'Keywords the author already listed: rice yield');
        });
    }

    public function test_the_daily_quota_blocks_further_real_calls(): void
    {
        config(['research.ai.per_day' => 2]);
        $student = User::factory()->student()->create();
        $this->fakeOpenAi(['keywords' => ['a'], 'sdgs' => []]);

        foreach (['one', 'two'] as $abstract) {
            $this->actingAs($student)->postJson('/research/analyze', ['title' => 'T', 'abstract' => $abstract])->assertOk();
        }

        $this->actingAs($student)->postJson('/research/analyze', ['title' => 'T', 'abstract' => 'three'])
            ->assertStatus(429)
            ->assertJson(['success' => false, 'quota' => ['remaining' => 0]]);

        // Already-cached content is still served once the quota is spent.
        $this->actingAs($student)->postJson('/research/analyze', ['title' => 'T', 'abstract' => 'one'])
            ->assertOk()->assertJson(['cached' => true]);

        Http::assertSentCount(2);
    }

    public function test_quota_falls_back_when_misconfigured(): void
    {
        config(['research.ai.per_day' => 0]);

        $this->assertSame(10, SubmissionAiQuota::limit());
    }

    public function test_abstract_generation_uses_the_server_side_excerpt_and_is_cached(): void
    {
        $student = User::factory()->student()->create();
        app(AbstractGenerationService::class)->storeSource(self::HASH, "[Introduction]\nFarmers face low rice yield.");
        $this->fakeOpenAi(['abstract' => 'This study examined low rice yield among farmers.']);

        $this->actingAs($student)->postJson('/research/abstract/generate', ['pdf_hash' => self::HASH, 'title' => 'Rice study'])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'abstract' => 'This study examined low rice yield among farmers.',
                'cached' => false,
                'quota' => ['used' => 1],
            ]);

        $this->actingAs($student)->postJson('/research/abstract/generate', ['pdf_hash' => self::HASH, 'title' => 'Rice study'])
            ->assertOk()->assertJson(['cached' => true, 'quota' => ['used' => 1]]);

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_contains($request['messages'][1]['content'], 'Farmers face low rice yield.'));
    }

    public function test_abstract_generation_without_a_stored_excerpt_asks_to_reselect_the_pdf(): void
    {
        $student = User::factory()->student()->create();
        Http::fake();

        $this->actingAs($student)->postJson('/research/abstract/generate', ['pdf_hash' => self::HASH, 'title' => 'Rice study'])
            ->assertStatus(422)
            ->assertJsonFragment(['success' => false])
            ->assertSee('re-select your PDF');

        $this->assertSame(0, SubmissionAiQuota::used($student->id));
        Http::assertNothingSent();
    }

    public function test_abstract_generation_rejects_a_malformed_hash(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)->postJson('/research/abstract/generate', ['pdf_hash' => '../etc', 'title' => 'T'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('pdf_hash');
    }

    public function test_an_edited_ai_draft_still_counts_as_ai_generated(): void
    {
        $this->fakeOpenAi(['abstract' => 'This study examined the effect of organic fertilizer on rice yield in Isabela and found a significant increase.']);
        $service = app(AbstractGenerationService::class);
        $service->storeSource(self::HASH, 'excerpt');
        $service->generate(self::HASH, 'Rice study');

        $this->assertTrue($service->matchesGenerated(self::HASH, 'This study examined the effect of organic fertilizer on rice yield in Isabela, Philippines and found a significant increase.'));
        $this->assertFalse($service->matchesGenerated(self::HASH, 'A mobile app for tracking library book loans was developed.'));
        $this->assertFalse($service->matchesGenerated(str_repeat('b', 64), 'This study examined the effect of organic fertilizer.'));
    }

    public function test_abstract_source_excerpt_samples_the_papers_sections(): void
    {
        $text = "INTRODUCTION\nFarmers face low rice yield.\nMETHODOLOGY\nA field experiment was run.\nCONCLUSION\nFertilizer helped.\nREFERENCES\nDoe (2020).";
        $detector = app(SectionDetector::class);
        $detected = $detector->detect([$text], $text);

        $excerpt = $detector->abstractSourceExcerpt($detected['sections'], $text);

        $this->assertStringContainsString("[Introduction]\nFarmers face low rice yield.", $excerpt);
        $this->assertStringContainsString("[Methodology]\nA field experiment was run.", $excerpt);
        $this->assertStringContainsString("[Conclusion]\nFertilizer helped.", $excerpt);
        $this->assertStringNotContainsString('Doe (2020)', $excerpt);
    }

    public function test_a_scanned_pdf_is_rejected_with_a_clear_message(): void
    {
        $student = User::factory()->student()->create();
        $pdf = MinimalPdf::build([
            MinimalPdf::textPage('Title page of a research paper submitted to the college of agriculture.'),
            MinimalPdf::scanPage(),
            MinimalPdf::scanPage(),
            MinimalPdf::scanPage(),
        ]);

        $response = $this->actingAs($student)
            ->post('/research/pdf/analyze', ['file' => UploadedFile::fake()->createWithContent('scan.pdf', $pdf)], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertStringContainsString('scanned/image-only document (3 of 4 pages are images)', $response->json('message'));
    }

    public function test_a_text_pdf_without_an_abstract_offers_ai_generation(): void
    {
        $student = User::factory()->student()->create();
        $body = str_repeat('Farmers in the province face declining rice yield due to poor soil. ', 8);
        $pdf = MinimalPdf::build([
            MinimalPdf::textPage("INTRODUCTION\n".$body),
            MinimalPdf::textPage("CONCLUSION\nOrganic fertilizer improved yield."),
        ]);

        $response = $this->actingAs($student)
            ->post('/research/pdf/analyze', ['file' => UploadedFile::fake()->createWithContent('paper.pdf', $pdf)], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['success' => true, 'can_generate_abstract' => true, 'scan' => ['verdict' => 'text']]);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $response->json('pdf_hash'));
    }
}
