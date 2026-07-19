<?php

namespace App\DataTables;

use App\Models\Branch;
use App\Models\BranchProduct as Model;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BranchProductMasterListDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('product_display', function (Model $branchProduct) {
                $product = $branchProduct->product;
                if (!$product) {
                    return 'Product #' . $branchProduct->product_id;
                }

                $html = '<strong>' . e($product->name) . '</strong>';

                if ($product->code) {
                    $html .= '<br><small class="text-muted">Code: ' . e($product->code) . '</small>';
                }

                if ($product->sku) {
                    $html .= '<br><small class="text-muted">SKU: ' . e($product->sku) . '</small>';
                }

                return $html;
            })
            ->editColumn('stock', function (Model $branchProduct) {
                $stock = (float) $branchProduct->stock;
                $class = $stock <= 0 ? 'text-danger' : 'text-success';

                return '<span class="fw-bold ' . $class . '">' . number_format($stock, 2) . '</span>';
            })
            ->filterColumn('product_display', function ($query, $keyword) {
                $productIds = Product::where('company_id', $this->company_id)
                    ->where(function ($productQuery) use ($keyword) {
                        $productQuery->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('sku', 'like', '%' . $keyword . '%')
                            ->orWhere('code', 'like', '%' . $keyword . '%');
                    })
                    ->pluck('id');

                $query->whereIn('product_id', $productIds);
            })
            ->filterColumn('branch.name', function ($query, $keyword) {
                $branchIds = Branch::whereIn('id', $this->branch_ids)
                    ->where('name', 'like', '%' . $keyword . '%')
                    ->pluck('id');

                $query->whereIn('branch_id', $branchIds);
            })
            ->rawColumns(['product_display', 'stock']);
    }

    public function query(Model $model): QueryBuilder
    {
        $productIds = Product::where('company_id', $this->company_id)->pluck('id');

        $query = $model->newQuery()
            ->with(['branch', 'product.uom'])
            ->whereIn('product_id', $productIds);

        if ($this->branch_id) {
            $query->where('branch_id', $this->branch_id);
        } else {
            $query->whereIn('branch_id', $this->branch_ids);
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
            ->orderBy(4, 'desc');
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::make('product_display')->title('Product')->orderable(false),
            Column::make('branch.name')->title('Branch'),
            Column::make('stock')->title('Stock on Hand')
        ];
    }

    protected function filename(): string
    {
        return 'BranchProductMasterList_' . date('YmdHis');
    }
}
