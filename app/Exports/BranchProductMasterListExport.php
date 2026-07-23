<?php

namespace App\Exports;

use App\DataTables\BranchProductMasterListDataTable;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BranchProductMasterListExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    public function __construct(
        protected int $companyId,
        protected array $branchIds,
        protected ?int $branchId = null,
        protected ?string $productName = null,
    ) {}

    public function query(): QueryBuilder
    {
        $branchIds = $this->branchId
            ? [$this->branchId]
            : $this->branchIds;

        return BranchProductMasterListDataTable::buildMasterListQuery(
            Product::query(),
            $this->companyId,
            $branchIds,
            $this->productName,
        )->orderBy('products.name')->orderBy('branches.name');
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Code',
            'SKU',
            'Branch',
            'Stock on Hand',
            'Cost',
            'Price',
        ];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->code ?? '',
            $product->sku ?? '',
            $product->branch_name,
            (float) $product->stock,
            (float) $product->cost,
            (float) $product->price,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
        ];
    }
}
