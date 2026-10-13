<?php

namespace Tests\Support;

/**
 * Builds tiny, valid PDFs in memory for tests — each page either a text
 * page or a "scan" (one embedded image plus optional overlay text, the way
 * phone-scanner apps stamp "Scanned with CamScanner"). Lets the scanned-PDF
 * detection be exercised through the real smalot/pdfparser path instead of
 * mocked arrays.
 */
final class MinimalPdf
{
    /**
     * @param  array<int, array{text?: string, image?: bool}>  $pages
     */
    public static function build(array $pages): string
    {
        // 1 catalog, 2 pages, 3 font, 4 image; then 2 objects per page.
        $objects = [];
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[4] = "<< /Type /XObject /Subtype /Image /Width 1 /Height 1 /ColorSpace /DeviceGray /BitsPerComponent 8 /Length 1 >>\nstream\n\x80\nendstream";

        $kids = [];
        $next = 5;

        foreach ($pages as $page) {
            $pageId = $next++;
            $contentId = $next++;
            $kids[] = "{$pageId} 0 R";

            $content = '';

            if ($page['image'] ?? false) {
                $content .= "q 500 0 0 700 50 50 cm /Im1 Do Q\n";
            }

            if (($page['text'] ?? '') !== '') {
                $y = 750;
                foreach (explode("\n", wordwrap($page['text'], 90, "\n", true)) as $line) {
                    $escaped = strtr($line, ['\\' => '\\\\', '(' => '\\(', ')' => '\\)']);
                    $content .= "BT /F1 10 Tf 40 {$y} Td ({$escaped}) Tj ET\n";
                    $y -= 14;
                }
            }

            $resources = '/Font << /F1 3 0 R >>'.(($page['image'] ?? false) ? ' /XObject << /Im1 4 0 R >>' : '');

            $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << {$resources} >> /Contents {$contentId} 0 R >>";
            $objects[$contentId] = '<< /Length '.strlen($content)." >>\nstream\n{$content}\nendstream";
        }

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.count($kids).' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= 'xref'."\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= 'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    public static function textPage(string $text): array
    {
        return ['text' => $text];
    }

    public static function scanPage(string $overlay = 'Scanned with CamScanner'): array
    {
        return ['image' => true, 'text' => $overlay];
    }
}
