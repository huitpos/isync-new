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

        $products = DB::select("
            SELECT 
                p.id,
                p.name,
                p.description,
                p.code,
                p.cost,
                p.srp,
                c.name as category_name,
                sc.name as subcategory_name,
                d.name as department_name,
                COALESCE(bp.stock, 0) as stock,
                COALESCE(bp.price, p.srp) as branch_price
            FROM products p
            LEFT JOIN branch_product bp ON p.id = bp.product_id AND bp.branch_id = ?
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN subcategories sc ON p.subcategory_id = sc.id
            LEFT JOIN departments d ON p.department_id = d.id
            WHERE p.company_id = ?
        ", [$branchId, $companyId]);

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
            'Category',
            'Subcategory',
            'Department',
        ];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->description,
            $product->code,
            $product->stock,
            $product->cost,
            $product->branch_price,
            $product->category_name ?? 'N/A',
            $product->subcategory_name ?? 'N/A',
            $product->department_name ?? 'N/A',
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
