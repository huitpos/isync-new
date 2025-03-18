<?php

namespace App\DataTables\Branch;

use App\Models\Product;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ProductsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $companySlug = $this->company_slug;
        $branchSlug = $this->branch_slug;

        return (new EloquentDataTable($query))
            ->addColumn('name', function (Product $data) use ($companySlug, $branchSlug) {
                return view('branch.datatables._link', [
                    'url' => route('branch.products.show', [
                        'companySlug' => $companySlug,
                        'product' => $data->id,
                        'branchSlug' => $branchSlug
                    ]),
                    'text' => $data->name,
                ]);
            })
            ->editColumn('cost', function (Product $data) {
                return number_format($data->cost, 2);
            })
            ->editColumn('srp', function (Product $data) {
                return number_format($data['branches'][0]['pivot']['price'] ?? $data->srp, 2);
            })
            ->addColumn('actions', function (Product $data) use ($companySlug, $branchSlug) {
                return view('branch.datatables._actions', [
                    'param' => [
                        'product' => $data->id,
                        'companySlug' => $companySlug,
                        'branchSlug' => $branchSlug
                    ],
                    'route' => 'branch.products',
                ]);
            });
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(Product $model): QueryBuilder
    {
        $branchId = $this->branch_id;

        return $model->newQuery()
            ->where('company_id', $this->company_id)
            ->with([
                'itemType',
                'uom',
                'createdBy',
                'branches' => function ($query) use ($branchId) {
                    $query->where('branches.id', $branchId);
                }
            ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('clusters-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>i>",)
            ->addTableClass('table align-middle table-striped table-row-bordered fs-6 gy-5 gs-7 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(0, 'asc')
            ->drawCallback("function() {" . file_get_contents(resource_path('views/datatables/_common-scripts.js')) . "}");
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::make('name')->title('Product Name'),
            Column::make('description'),
            Column::make('item_type.name', 'itemType.name')->title('Item Type'),
            Column::make('uom.name')->title('UOM'),
            Column::make('code')->title('Item Code'),
            Column::make('cost'),
            Column::make('srp'),
            Column::make('created_by.name', 'createdBy.name')->title('created by'),
            Column::make('status')
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}