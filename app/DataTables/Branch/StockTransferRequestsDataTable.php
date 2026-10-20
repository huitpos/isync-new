<?php

namespace App\DataTables\Branch;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\StockTransferRequest as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class StockTransferRequestsDataTable extends DataTable
{
    use UniformAjaxTable;

    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $branchSlug = $this->branch_slug;
        $companySlug = $this->company_slug;

        return $this->withRelationSearch((new EloquentDataTable($query))
            ->editColumn('str_number', function (Model $data) use($companySlug, $branchSlug) {
                return '<a href="' . route('branch.stock-transfer-requests.show', [
                    'companySlug' => $companySlug,
                    'branchSlug' => $branchSlug,
                    'stock_transfer_request' => $data->id
                ]) . '">' . $data->str_number . '</a>';
            })
            ->editColumn('created_at', function (Model $data) {
                return $data->created_at;
            })
            ->addColumn('view_url', function (Model $data) use ($companySlug, $branchSlug) {
                return route('branch.stock-transfer-requests.show', [
                    'companySlug' => $companySlug,
                    'branchSlug' => $branchSlug,
                    'stock_transfer_request' => $data->id,
                ]);
            })
            ->rawColumns(['str_number']));
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Model $model): QueryBuilder
    {
        $branchId = $this->branch_id;
        return $model->newQuery()
            ->with([
                'createdBy',
                'branch',
                'sourceBranch',
                'stockTransferOrder'
            ])
            ->where('destination_branch_id', $branchId);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::make('str_number'),
            Column::make('stock_transfer_order.sto_number')->title('stock transfer order'),
            Column::make('branch.name')->title('destination branch'),
            Column::make('source_branch.name', 'sourceBranch.name')->title('source branch'),
            Column::make('created_by.name', 'createdBy.name')->title('created by'),
            Column::make('status'),
            Column::make('created_at'),
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