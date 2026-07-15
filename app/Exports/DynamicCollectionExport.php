<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Illuminate\Support\Collection;
use Closure;

class DynamicCollectionExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithMapping
{
    private Collection $collection;
    private array $headings;
    private string $title;
    private ?Closure $mapRow;

    public function __construct(
        Collection $collection,
        array $headings,
        string $title = 'Sheet1',
        ?Closure $mapRow = null
    ) {
        $this->collection = $collection;
        $this->headings = $headings;
        $this->title = $title;
        $this->mapRow = $mapRow;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => Color::COLOR_WHITE]],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF808080'], // Abu-abu
                ],
            ],
        ];
    }

    public function map($row): array
    {
        if ($this->mapRow !== null) {
            return call_user_func($this->mapRow, $row);
        }

        if (is_object($row) && method_exists($row, 'toArray')) {
            return $row->toArray();
        }

        return (array) $row;
    }
}
