<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Closure;

class DynamicExport implements FromQuery, FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithMapping
{
    private $queryOrCollection;
    private array $headings;
    private string $title;
    private ?Closure $mapRow;

    /**
     * @param Builder|Collection $queryOrCollection Data source
     * @param array $headings Header untuk tiap kolom
     * @param string $title Nama Sheet
     * @param Closure|null $mapRow Custom mapping per baris (optional)
     */
    public function __construct(
        $queryOrCollection,
        array $headings,
        string $title = 'Sheet1',
        ?Closure $mapRow = null
    ) {
        $this->queryOrCollection = $queryOrCollection;
        $this->headings = $headings;
        $this->title = $title;
        $this->mapRow = $mapRow;
    }

    /**
     * @return Builder|null
     */
    public function query()
    {
        if ($this->queryOrCollection instanceof Builder) {
            return $this->queryOrCollection;
        }
        
        return null;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        if ($this->queryOrCollection instanceof Collection) {
            return $this->queryOrCollection;
        }

        return collect([]);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * Styling untuk excel (Header bold & background abu-abu)
     */
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

    /**
     * Mapping data per baris
     */
    public function map($row): array
    {
        if ($this->mapRow !== null) {
            return call_user_func($this->mapRow, $row);
        }

        // Jika tidak ada mapRow, return row as array
        if (is_object($row) && method_exists($row, 'toArray')) {
            return $row->toArray();
        }

        return (array) $row;
    }
}
