<?php

namespace App\DataTables\Company;

use App\DataTables\Concerns\UniformAjaxTable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\QueryDataTable;
use Yajra\DataTables\Services\DataTable;

class ValuationDataTable extends DataTable
{
    use UniformAjaxTable;

    public function dataTable(Builder $query): QueryDataTable
    {
        return (new QueryDataTable($query))
            ->editColumn('qty', fn ($row) => number_format((float) $row->qty, 2))
            ->editColumn('value', fn ($row) => number_format((float) $row->value, 2));
    }

    public function query(): Builder
    {
        return DB::table('inventory_cost_layers')
            ->join('products', 'products.id', '=', 'inventory_cost_layers.product_id')
            ->join('branches', 'branches.id', '=', 'inventory_cost_layers.branch_id')
            ->where('branches.company_id', $this->company_id)
            ->where('inventory_cost_layers.qty_remaining', '>', 0)
            ->groupBy('branches.id', 'branches.name', 'products.id', 'products.name', 'products.sku')
            ->select([
                'branches.name as branch_name',
                'products.name as product_name',
                'products.sku',
                DB::raw('SUM(inventory_cost_layers.qty_remaining) as qty'),
                DB::raw('SUM(inventory_cost_layers.qty_remaining * inventory_cost_layers.unit_cost) as value'),
            ]);
    }

    public function total(): float
    {
        return (float) DB::table('inventory_cost_layers')
            ->join('branches', 'branches.id', '=', 'inventory_cost_layers.branch_id')
            ->where('branches.company_id', $this->company_id)
            ->where('inventory_cost_layers.qty_remaining', '>', 0)
            ->sum(DB::raw('inventory_cost_layers.qty_remaining * inventory_cost_layers.unit_cost'));
    }

    public function getColumns(): array
    {
        return [
            Column::make('branch_name')->title('Branch')->name('branches.name'),
            Column::make('product_name')->title('Product')->name('products.name'),
            Column::make('sku')->title('SKU')->name('products.sku'),
            Column::make('qty')->title('Remaining qty')->orderable(false)->searchable(false),
            Column::make('value')->title('Value')->orderable(false)->searchable(false),
        ];
    }
}
