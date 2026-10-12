<?php

namespace Tests\Unit;

use App\Services\Pdf\SectionDetector;
use Tests\TestCase;

/**
 * What SectionDetector sends toward the AI: the excerpt must come from the
 * paper's actual prose, not its table of contents, front matter, running
 * headers or citation noise — that text costs tokens and makes a worse
 * abstract.
 */
class SectionDetectorTest extends TestCase
{
    private function prose(string $topic, int $sentences = 12): string
    {
        return str_repeat("This chapter discusses {$topic} among rice farmers in the province in detail. ", $sentences);
    }

    /** @return string[] pages of a typical thesis with a TOC and a running header */
    private function thesisPages(): array
    {
        $header = 'Isabela State University';

        return [
            "{$header}\nAPPROVAL SHEET\nThis thesis is approved by the panel of examiners.",
            "{$header}\nACKNOWLEDGMENT\nThe researchers thank their beloved parents and friends for endless support.",
            "{$header}\nTABLE OF CONTENTS\nINTRODUCTION\n1\nBackground of the Study 1\nMETHODOLOGY\n12\nRESULTS AND DISCUSSION\n20\nCONCLUSION\n30\nREFERENCES\n35",
            "{$header}\nINTRODUCTION\n".$this->prose('declining yield')."Low yield was reported (Santos et al., 2019) and see https://example.org/report.\n1",
            "{$header}\nMETHODOLOGY\n".$this->prose('the field experiment')."\nFigure 1. Map of the study area\n2",
            "{$header}\nRESULTS AND DISCUSSION\n".$this->prose('the yield increase')."\n3",
            "{$header}\nCONCLUSION\n".$this->prose('organic fertilizer adoption')."\n4",
            "{$header}\nREFERENCES\nSantos, J. (2019). Rice yield. Journal of Agriculture.",
        ];
    }

    private function excerptFor(array $pages): string
    {
        $detector = app(SectionDetector::class);
        $fullText = implode("\n", $pages);
        $detected = $detector->detect($pages, $fullText);

        return (string) $detector->abstractSourceExcerpt($detected['sections'], $fullText, $pages);
    }

    public function test_table_of_contents_entries_are_not_treated_as_sections(): void
    {
        $pages = $this->thesisPages();
        $fullText = implode("\n", $pages);
        $detected = app(SectionDetector::class)->detect($pages, $fullText);

        $introStart = $detected['sections'][0]['start'];

        $this->assertSame('introduction', $detected['sections'][0]['key']);
        $this->assertGreaterThan(strpos($fullText, 'TABLE OF CONTENTS'), $introStart);
        $this->assertStringStartsWith("INTRODUCTION\nThis chapter discusses declining yield", substr($fullText, $introStart));
        // The real reference list, not the TOC's "REFERENCES 35" entry.
        $this->assertSame(['Santos, J. (2019). Rice yield. Journal of Agriculture.'], $detected['references']);
    }

    public function test_excerpt_is_the_papers_prose_without_noise(): void
    {
        $excerpt = $this->excerptFor($this->thesisPages());

        $this->assertStringStartsWith('[Introduction]', $excerpt);
        $this->assertStringContainsString('declining yield', $excerpt);
        $this->assertStringContainsString('[Methodology]', $excerpt);
        $this->assertStringContainsString('[Results]', $excerpt);
        $this->assertStringContainsString('[Conclusion]', $excerpt);

        // "RESULTS AND DISCUSSION" already covers the discussion.
        $this->assertStringNotContainsString('[Discussion]', $excerpt);

        foreach (['Isabela State University', 'TABLE OF CONTENTS', 'beloved parents', 'Santos et al', 'https://', 'Figure 1', 'Journal of Agriculture'] as $noise) {
            $this->assertStringNotContainsString($noise, $excerpt, "Excerpt still contains: {$noise}");
        }
    }

    public function test_excerpt_stays_within_its_word_budget_for_a_long_thesis(): void
    {
        $pages = $this->thesisPages();
        $pages[3] = "INTRODUCTION\n".$this->prose('declining yield', 400);

        $words = str_word_count($this->excerptFor($pages));

        // 200 + 150 + 200 + 200 words of prose, plus the section labels.
        $this->assertLessThanOrEqual(760, $words);
    }

    public function test_fallback_without_headings_skips_front_matter(): void
    {
        $pages = [
            "APPROVAL SHEET\nApproved by the panel.",
            "ACKNOWLEDGMENT\nThe researchers thank their beloved parents.",
            $this->prose('soil fertility', 20),
        ];

        $excerpt = $this->excerptFor($pages);

        $this->assertStringStartsWith('This chapter discusses soil fertility', $excerpt);
        $this->assertStringNotContainsString('beloved parents', $excerpt);
        $this->assertLessThanOrEqual(600, str_word_count($excerpt));
    }

    public function test_a_short_real_ending_is_not_mistaken_for_a_table_of_contents(): void
    {
        $text = "INTRODUCTION\n".$this->prose('declining yield')
            ."\nCONCLUSIONS\nYield improved.\nRECOMMENDATIONS\nAdopt it.\nREFERENCES\nDoe (2020).";

        $detected = app(SectionDetector::class)->detect([$text], $text);

        $this->assertSame(
            ['introduction', 'conclusion', 'conclusion', 'references'],
            array_column($detected['sections'], 'key')
        );
    }
}
