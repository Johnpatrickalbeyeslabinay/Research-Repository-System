<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AdminReportExport implements FromArray, ShouldAutoSize
{
    public function __construct(
        private array $data,
        private string $period,
        private int $year,
    ) {}

    public function array(): array
    {
        $rows = [
            ['Research Repository Report'],
            ['Period', ucfirst($this->period), 'Year', $this->year],
            [],
            ['RESEARCH UPLOADS'],
            ['Period', 'Total'],
        ];

        foreach ($this->data['research']['labels'] as $i => $label) {
            $rows[] = [$label, $this->data['research']['values'][$i] ?? 0];
        }

        $rows[] = [];
        $rows[] = ['REGISTERED USERS'];
        $rows[] = ['Period', 'Students', 'Instructors'];

        foreach ($this->data['users']['labels'] as $i => $label) {
            $rows[] = [
                $label,
                $this->data['users']['students'][$i] ?? 0,
                $this->data['users']['instructors'][$i] ?? 0,
            ];
        }

        $rows[] = [];
        $rows[] = ['TOP SEARCH KEYWORDS'];
        $rows[] = ['Period', 'Keyword', 'Searches'];

        foreach ($this->data['searchRace'] as $frame) {
            foreach ($frame['data'] as $item) {
                $rows[] = [$frame['label'], $item['name'], $item['value']];
            }
        }

        return $rows;
    }
}
