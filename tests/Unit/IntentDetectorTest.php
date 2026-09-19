<?php

namespace Tests\Unit;

use App\Services\Chat\IntentDetector;
use App\Services\Chat\PlatformQuestionDetector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Pure regex behaviour — no database, no HTTP, no API calls. Extends
 * PHPUnit's TestCase directly rather than Tests\TestCase so it doesn't pay
 * for booting the Laravel application.
 */
class IntentDetectorTest extends TestCase
{
    private IntentDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->detector = new IntentDetector;
    }

    /** @return array<string, array{string}> */
    public static function catalogQuestions(): array
    {
        return [
            // The exact phrasing from the live bug report.
            'terse inventory' => ['list repository available'],
            'what is available' => ['What research is available?'],
            'what do you have' => ['what studies do you have'],
            'whats in the repository' => ["what's in the repository"],
            'show me studies' => ['show me the studies'],
            'list papers' => ['list all papers'],
            'how many' => ['How many studies are in the repository?'],
            'what topics' => ['What topics are covered?'],
            'browse' => ['let me browse the repository'],
            'recommend' => ['recommend papers related to fish'],
            'suggest' => ['suggest studies about aquaculture'],
            'find research' => ['find research about rice disease detection'],
            'any research about' => ['are there any studies about seaweed?'],
            'looking for' => ['looking for research on tilapia'],
            'which studies about' => ['Which studies are related to SDG 4?'],
            'what research on' => ['What research is there about GIS?'],
            'published in a year range' => ['What studies were published from 2024 to 2026?'],
            'papers submitted' => ['Which papers were submitted last year?'],
        ];
    }

    #[DataProvider('catalogQuestions')]
    public function test_catalog_questions_are_detected(string $question): void
    {
        $this->assertTrue(
            $this->detector->isCatalogQuestion($question),
            "Expected a catalog question: \"{$question}\""
        );
    }

    /** @return array<string, array{string}> */
    public static function nonCatalogQuestions(): array
    {
        return [
            // Content questions — these need the chunk text, not metadata,
            // even though several are phrased like a catalog request.
            'methodology' => ['What methodologies were used in chatbot-related studies?'],
            'findings' => ['What were the key findings about seaweed diversity?'],
            'summarize' => ['Summarize the findings of this research.'],
            'compare' => ['Compare the methodology of these two studies.'],
            'dataset' => ['What dataset did the study use?'],
            'how was it done' => ['How was the sampling done?'],
            'why' => ['Why did the results differ between seasons?'],
            'direct fact' => ['How many seaweed species were identified?'],

            // Platform questions belong to PlatformQuestionDetector.
            'platform how it works' => ['how does this system work'],
            'platform submit' => ['how do I submit my research?'],

            // Greetings and plain chatter.
            'greeting' => ['hi'],
            'thanks' => ['thanks'],
            'empty' => [''],
        ];
    }

    #[DataProvider('nonCatalogQuestions')]
    public function test_non_catalog_questions_are_not_detected(string $question): void
    {
        $this->assertFalse(
            $this->detector->isCatalogQuestion($question),
            "Did not expect a catalog question: \"{$question}\""
        );
    }

    /**
     * The two detectors must not both claim the same message — catalog is
     * checked after platform in ChatController, so an overlap would
     * silently route a catalog question to the platform-only prompt.
     */
    public function test_catalog_and_platform_detection_do_not_overlap(): void
    {
        $platform = new PlatformQuestionDetector;

        foreach (self::catalogQuestions() as $label => [$question]) {
            $this->assertFalse(
                $platform->isPlatformQuestion($question),
                "PlatformQuestionDetector also claimed the catalog question \"{$question}\" ({$label})"
            );
        }
    }

    public function test_bare_inventory_is_distinguished_from_topical_recommendation(): void
    {
        // No topic to embed — CatalogService should skip the semantic arm.
        $this->assertTrue($this->detector->isBareInventory('list repository available'));
        $this->assertTrue($this->detector->isBareInventory('How many studies are there?'));

        // Has a real topic — the semantic arm is what makes these useful.
        $this->assertFalse($this->detector->isBareInventory('recommend papers related to fish'));
        $this->assertFalse($this->detector->isBareInventory('find research about rice disease detection'));
    }
}
