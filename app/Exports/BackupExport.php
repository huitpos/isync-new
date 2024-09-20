<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use App\Models\AuditTrail;
use App\Models\Branch;
use App\Models\PosMachine;
use App\Models\Product;

class BackupExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents, ShouldAutoSize
{
    protected $branchId;

    public function __construct($branchId)
    {
        $this->branchId = $branchId;
    }

    /**
     * Return a collection of data for the export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $branch = Branch::find($this->branchId);

        $products = Product::where('company_id', $branch->company->id)
            ->get();

        return new Collection($products);
    }

    /**
     * Define the headings for the export.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Product Name',
            'Description',
            'Barcode',
            'SRP',
        ];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->description,
            $product->barcode,
            $product->srp,
        ];
    }

    /**
     * Define the start cell for the export.
     *
     * @return string
     */
    public function startCell(): string
    {
        return 'A6'; // Data will start from cell A2
    }

    /**
     * Register events to modify the sheet.
     *
     * @return array
     */
    public function registerEvents(): array
    {
        $branch = Branch::find($this->branchId);

        return [
            AfterSheet::class => function(AfterSheet $event) use($branch) {
                $event->sheet->setCellValue('A1', 'Company Name');
                $event->sheet->setCellValue('B1', $branch->company->company_name);

                $event->sheet->setCellValue('A2', 'Branch Name');
                $event->sheet->setCellValue('B2', $branch->name);

                $event->sheet->setCellValue('A3', 'Address');
                $event->sheet->setCellValue('B3', $branch->unit_floor_number . ', ' . $branch->street . ', ' . $branch->city->name . ', ' . $branch->province->name . ', ' . $branch->region->name);

                $event->sheet->setCellValue('A5', 'Products');
            },
        ];
    }
}
