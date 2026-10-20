<?php

namespace App\DataTables\Company;

use App\DataTables\Concerns\UniformAjaxTable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\QueryDataTable;
use Yajra\DataTables\Services\DataTable;

class LowStockDataTable extends DataTable
{
    use UniformAjaxTable;

    public function dataTable(Builder $query): QueryDataTable
    {
        return (new QueryDataTable($query))
            ->editColumn('stock', fn ($row) => number_format((float) $row->stock, 2))
            ->editColumn('minimum_stock_level', fn ($row) => number_format((float) $row->minimum_stock_level, 2))
            ->editColumn('cost', fn ($row) => number_format((float) $row->cost, 2));
    }

    public function query(): Builder
    {
        return DB::table('branch_product')
            ->join('products', 'products.id', '=', 'branch_product.product_id')
            ->join('branches', 'branches.id', '=', 'branch_product.branch_id')
            ->where('branches.company_id', $this->company_id)
            ->where('products.minimum_stock_level', '>', 0)
            ->whereColumn('branch_product.stock', '<=', 'products.minimum_stock_level')
            ->select([
                'branches.name as branch_name',
                'products.name as product_name',
                'products.sku',
                'branch_product.stock',
                'products.minimum_stock_level',
                'branch_product.cost',
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('branch_name')->title('Branch')->name('branches.name'),
            Column::make('product_name')->title('Product')->name('products.name'),
            Column::make('sku')->title('SKU')->name('products.sku'),
            Column::make('stock')->title('On hand')->name('branch_product.stock'),
            Column::make('minimum_stock_level')->title('Minimum')->name('products.minimum_stock_level'),
            Column::make('cost')->title('FIFO cost')->name('branch_product.cost'),
        ];
    }
}
