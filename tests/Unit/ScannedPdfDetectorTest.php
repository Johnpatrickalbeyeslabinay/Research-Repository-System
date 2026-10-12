<?php

namespace Tests\Unit;

use App\Services\Pdf\ScannedPdfDetector;
use Tests\TestCase;

/**
 * Extends Tests\TestCase because the thresholds come from
 * config('research.scan_detection.*').
 */
class ScannedPdfDetectorTest extends TestCase
{
    private function realPage(): string
    {
        return str_repeat('This study examined the effect of organic fertilizer on rice yield. ', 10);
    }

    public function test_a_text_pdf_with_a_few_figure_pages_is_text(): void
    {
        $pages = array_fill(0, 9, $this->realPage());
        $pages[] = 'Figure 1. Map';
        $hasImage = array_fill(0, 9, false);
        $hasImage[] = true;

        $result = (new ScannedPdfDetector)->detect($pages, $hasImage);

        $this->assertSame(ScannedPdfDetector::VERDICT_TEXT, $result['verdict']);
        $this->assertSame(1, $result['image_pages']);
    }

    public function test_scanner_watermarks_and_page_numbers_do_not_count_as_text(): void
    {
        $pages = [
            "Scanned with CamScanner\n12",
            "CamScanner\n- 13 -",
            "Scanned by Adobe Scan\n14",
            $this->realPage(),
        ];

        $result = (new ScannedPdfDetector)->detect($pages, [true, true, true, false]);

        $this->assertSame(ScannedPdfDetector::VERDICT_SCANNED, $result['verdict']);
        $this->assertSame(3, $result['image_pages']);
        $this->assertSame(0.75, $result['ratio']);
    }

    public function test_some_scanned_pages_is_partial(): void
    {
        $pages = array_fill(0, 7, $this->realPage());
        $hasImage = array_fill(0, 7, false);

        for ($i = 0; $i < 3; $i++) {
            $pages[] = '';
            $hasImage[] = true;
        }

        $result = (new ScannedPdfDetector)->detect($pages, $hasImage);

        $this->assertSame(ScannedPdfDetector::VERDICT_PARTIAL, $result['verdict']);
    }

    public function test_a_near_empty_page_without_an_image_is_not_a_scan(): void
    {
        // A blank separator page has no picture of text on it.
        $result = (new ScannedPdfDetector)->detect(['', '', $this->realPage()], [false, false, false]);

        $this->assertSame(ScannedPdfDetector::VERDICT_TEXT, $result['verdict']);
    }

    public function test_thresholds_are_configurable(): void
    {
        config(['research.scan_detection.scanned_ratio' => 0.9]);

        $result = (new ScannedPdfDetector)->detect(['', '', $this->realPage()], [true, true, false]);

        $this->assertSame(ScannedPdfDetector::VERDICT_PARTIAL, $result['verdict']);
    }
}
