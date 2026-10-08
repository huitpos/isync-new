<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BranchProductMasterListDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('product_display', function (Product $product) {
                $html = '<strong>' . e($product->name) . '</strong>';

                if ($product->code) {
                    $html .= '<br><small class="text-muted">Code: ' . e($product->code) . '</small>';
                }

                if ($product->sku) {
                    $html .= '<br><small class="text-muted">SKU: ' . e($product->sku) . '</small>';
                }

                return $html;
            })
            ->editColumn('branch_name', function (Product $product) {
                return e($product->branch_name);
            })
            ->editColumn('stock', function (Product $product) {
                $stock = (float) $product->stock;
                $class = $stock <= 0 ? 'text-danger' : 'text-success';

                return '<span class="fw-bold ' . $class . '">' . number_format($stock, 2) . '</span>';
            })
            ->editColumn('cost', fn (Product $product) => number_format((float) $product->cost, 2))
            ->editColumn('price', fn (Product $product) => number_format((float) $product->price, 2))
            ->filterColumn('product_display', function ($query, $keyword) {
                $query->where(function ($productQuery) use ($keyword) {
                    $productQuery->where('products.name', 'like', '%' . $keyword . '%')
                        ->orWhere('products.sku', 'like', '%' . $keyword . '%')
                        ->orWhere('products.code', 'like', '%' . $keyword . '%');
                });
            })
            ->filterColumn('branch_name', function ($query, $keyword) {
                $query->where('branches.name', 'like', '%' . $keyword . '%');
            })
            ->rawColumns(['product_display', 'stock']);
    }

    public function query(Product $model): QueryBuilder
    {
        return static::buildMasterListQuery(
            $model->newQuery(),
            (int) $this->company_id,
            $this->branch_id ? [(int) $this->branch_id] : array_map('intval', (array) $this->branch_ids),
            $this->product_name ?? null,
        );
    }

    public static function buildMasterListQuery(
        QueryBuilder $query,
        int $companyId,
        array $branchIds,
        ?string $productName = null,
    ): QueryBuilder {
        $inventoryDb = config('database.connections.inventory.database');
        $isyncDb = config('database.connections.mysql.database');

        $query
            ->select([
                'products.id',
                'products.name',
                'products.code',
                'products.sku',
                'branches.id as branch_id',
                'branches.name as branch_name',
                DB::raw('COALESCE(inv_bp.stock, sync_bp.stock, 0) as stock'),
                DB::raw('COALESCE(sync_bp.cost, products.cost, 0) as cost'),
                DB::raw('COALESCE(sync_bp.price, products.srp, 0) as price'),
            ])
            ->join('branches', function ($join) use ($branchIds) {
                $join->whereIn('branches.id', $branchIds);
            })
            ->leftJoin("{$inventoryDb}.branch_product as inv_bp", function ($join) {
                $join->on('inv_bp.product_id', '=', 'products.id')
                    ->on('inv_bp.branch_id', '=', 'branches.id');
            })
            ->leftJoin("{$isyncDb}.branch_product as sync_bp", function ($join) {
                $join->on('sync_bp.product_id', '=', 'products.id')
                    ->on('sync_bp.branch_id', '=', 'branches.id');
            })
            ->where('products.company_id', $companyId);

        if ($productName) {
            $query->where(function ($productQuery) use ($productName) {
                $productQuery->where('products.name', 'like', '%' . $productName . '%')
                    ->orWhere('products.sku', 'like', '%' . $productName . '%')
                    ->orWhere('products.code', 'like', '%' . $productName . '%');
            });
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('branch-product-master-list-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('frt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>")
            ->addTableClass('table table-striped table-hover align-middle')
            ->setTableHeadClass('table-dark')
            ->orderBy(3, 'desc');
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::make('product_display')->title('Product')->orderable(false),
            Column::make('branch_name')->title('Branch')->orderable(false),
            Column::make('stock')->title('Stock on Hand'),
            Column::make('cost')->title('Cost'),
            Column::make('price')->title('Price'),
        ];
    }

    protected function filename(): string
    {
        return 'BranchProductMasterList_' . date('YmdHis');
    }
}
