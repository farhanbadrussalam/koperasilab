<?php

namespace App\Services;

use App\Exports\DynamicExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Closure;

class ExportService
{
    /**
     * Ekspor data dinamis ke Excel.
     *
     * @param string $filename Nama file (cth: 'data.xlsx')
     * @param Builder|Collection $queryOrCollection Sumber data (Eloquent Builder atau Collection)
     * @param array $headings Header kolom Excel
     * @param string $title Nama sheet (default: 'Sheet1')
     * @param Closure|null $mapRow Custom mapper per baris
     * @param string $writerType Tipe file Excel
     * @return BinaryFileResponse
     */
    public function download(
        string $filename,
        $queryOrCollection,
        array $headings,
        string $title = 'Sheet1',
        ?Closure $mapRow = null,
        string $writerType = \Maatwebsite\Excel\Excel::XLSX
    ): BinaryFileResponse {
        
        if ($queryOrCollection instanceof Collection) {
            $export = new \App\Exports\DynamicCollectionExport(
                $queryOrCollection,
                $headings,
                $title,
                $mapRow
            );
        } else {
            $export = new DynamicExport(
                $queryOrCollection,
                $headings,
                $title,
                $mapRow
            );
        }

        return Excel::download($export, $filename, $writerType);
    }
}
