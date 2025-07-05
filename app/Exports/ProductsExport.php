<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, ShouldAutoSize, WithColumnFormatting
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Return a collection of data for the export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Get a smaller result set by selecting only needed fields
        // Process in chunks to avoid memory issues
        $products = collect();
        
        DB::table('products')
            ->select([
                'products.name',
                'products.description',
                'item_types.name as item_type_name',
                'unit_of_measurements.name as uom_name',
                'products.code',
                'products.cost',
                'products.srp',
                'users.name as created_by_name',
                'products.status'
            ])
            ->leftJoin('item_types', 'products.item_type_id', '=', 'item_types.id')
            ->leftJoin('unit_of_measurements', 'products.uom_id', '=', 'unit_of_measurements.id')
            ->leftJoin('users', 'products.created_by', '=', 'users.id')
            ->where('products.company_id', $this->companyId)
            ->orderBy('products.name')
            ->chunk(500, function($results) use (&$products) {
                foreach ($results as $item) {
                    $products->push($item);
                }
            });
        
        return $products;
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
            'Item Type',
            'UOM',
            'Item Code',
            'Cost',
            'SRP',
            'Created By',
            'Status',
        ];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->description,
            $product->item_type_name ?? '',
            $product->uom_name ?? '',
            $product->code ?? '',
            (float)$product->cost, // Pass as float for proper formatting
            (float)$product->srp,  // Pass as float for proper formatting
            $product->created_by_name ?? '',
            $product->status,
        ];
    }

    /**
     * Define the start cell for the export.
     *
     * @return string
     */
    public function startCell(): string
    {
        return 'A1';
    }
    
    /**
     * Column formatting
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
        ];
    }
}
