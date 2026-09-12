<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * One reusable export instead of a SalesExport/PurchasesExport/... class
 * per report — pass it whatever rows + column headings you want and it
 * produces the .xlsx. Keeps ReportExportController the single place that
 * decides what each report actually contains.
 */
class ReportExport implements FromCollection, WithHeadings
{
    public function __construct(
        private Collection $rows,
        private array $headings,
    ) {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}