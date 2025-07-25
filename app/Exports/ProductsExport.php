<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, WithCustomStartCell, ShouldAutoSize, WithColumnFormatting, WithChunkReading
{
    use Exportable;
    
    protected $companyId;

    public function __construct($companyId)
    {
        ini_set('memory_limit', '512M'); // Increase memory limit
        $this->companyId = $companyId;
    }

    /**
     * Return a query for the export.
     * Using FromQuery instead of FromCollection to reduce memory usage
     */
    public function query()
    {
        return DB::table('products')
            ->select([
                'products.id', // Always include ID for proper grouping
                'products.status',
                'products.name',
                'products.description',
                'products.sku',
                'products.abbreviation',
                'unit_of_measurements.name as uom_name',
                'products.barcode',
                'departments.name as department_name',
                'categories.name as category_name',
                'subcategories.name as subcategory_name',
                'products.markup_type',
                'products.markup',
                'products.cost',
                'item_types.name as item_type_name',
                'products.srp',
                DB::raw('GROUP_CONCAT(DISTINCT item_locations.name SEPARATOR ", ") as item_location_name'),
                'products.max_discount',
                'products.minimum_stock_level',
                'products.maximum_stock_level',
                'products.part_number',
                'products.code'
            ])
            ->leftJoin('item_types', 'products.item_type_id', '=', 'item_types.id')
            ->leftJoin('unit_of_measurements', 'products.uom_id', '=', 'unit_of_measurements.id')
            ->leftJoin('departments', 'products.department_id', '=', 'departments.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('subcategories', 'products.subcategory_id', '=', 'subcategories.id')
            ->leftJoin('item_location_product', 'products.id', '=', 'item_location_product.product_id')
            ->leftJoin('item_locations', 'item_location_product.item_location_id', '=', 'item_locations.id')
            ->where('products.company_id', $this->companyId)
            ->groupBy([
                'products.id', 'products.status', 'products.name', 'products.description',
                'products.sku', 'products.abbreviation', 'unit_of_measurements.name',
                'products.barcode', 'departments.name', 'categories.name',
                'subcategories.name', 'products.markup_type', 'products.markup',
                'products.cost', 'item_types.name', 'products.srp',
                'products.max_discount', 'products.minimum_stock_level',
                'products.maximum_stock_level', 'products.part_number', 'products.code'
            ])
            ->orderBy('products.name');
    }
    
    /**
     * Define chunk size for better memory management
     */
    public function chunkSize(): int
    {
        return 100; // Process 100 records at a time
    }

    /**
     * Define the headings for the export.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Status',
            'Product Name',
            'Description',
            'SKU',
            'Abbreviation',
            'UOM',
            'Barcode',
            'Department',
            'Category',
            'Subcategory',
            'Markup Type',
            'Markup',
            'Cost',
            'SRP',
            'Item Type',
            'Location',
            'Max Discount',
            'Minimum Stock Level',
            'Maximum Stock Level',
            'Part Number',
            'Item Code',
        ];
    }

    public function map($product): array
    {
        return [
            $product->status,
            $product->name,
            $product->description,
            $product->sku ?? '',
            $product->abbreviation ?? '',
            $product->uom_name ?? '',
            $product->barcode ?? '',
            $product->department_name ?? '',
            $product->category_name ?? '',
            $product->subcategory_name ?? '',
            $product->markup_type ?? '',
            (float)$product->markup ?? 0,
            (float)$product->cost ?? 0,
            (float)$product->srp ?? 0,
            $product->item_type_name ?? '',
            $product->item_location_name ?? '',
            (float)$product->max_discount ?? 0,
            (int)$product->minimum_stock_level ?? 0,
            (int)$product->maximum_stock_level ?? 0,
            $product->part_number ?? '',
            $product->code ?? '',
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
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2, // Markup
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2, // Cost
            'O' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2, // SRP
            'Q' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2, // Max Discount
            'R' => NumberFormat::FORMAT_NUMBER, // Minimum Stock Level
            'S' => NumberFormat::FORMAT_NUMBER, // Maximum Stock Level
        ];
    }
}
