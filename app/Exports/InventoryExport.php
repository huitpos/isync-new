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

class InventoryExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, ShouldAutoSize
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

        $branchId = $branch->id;
        $companyId = $branch->company_id;

        $products = Product::where('company_id', $companyId)
            ->with([
                'branches' => function ($query) use ($branchId) {
                    $query->where('branches.id', $branchId);
                }
            ])
            ->where('company_id', $companyId)
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
            'Stock',
            'Unit Cost',
            'Branch SRP',
        ];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->description,
            $product->code,
            $product['branches'][0]['pivot']['stock'] ?? 0,
            $product->cost,
            $product['branches'][0]['pivot']['price'] ?? $product->srp
        ];
    }

    /**
     * Define the start cell for the export.
     *
     * @return string
     */
    public function startCell(): string
    {
        return 'A1'; // Data will start from cell A2
    }
}
