<?php

namespace App\DataTables\Company;


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
            ->editColumn('str_number', function (Model $data) use ($companySlug) {
                return view('company.datatables._link', [
                    'url' => route('company.stock-transfer-requests.show', [
                        'companySlug' => $companySlug,
                        'stock_transfer_request' => $data->id,
                    ]),
                    'text' => $data->str_number,
                ]);
            })
            ->editColumn('created_at', function (Model $data) {
                return $data->created_at;
            })
            ->addColumn('view_url', function (Model $data) use($companySlug) {
                return route('company.stock-transfer-requests.show', [
                    'companySlug' => $companySlug,
                    'stock_transfer_request' => $data->id
                ]);
            }));
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(Model $model): QueryBuilder
    {
        $companyId = $this->company_id;
        $query = $model->newQuery()
        ->with([
            'createdBy',
            'branch',
            'sourceBranch',
        ])
        ->whereHas('branch.company', function ($query) use ($companyId) {
            $query->where('companies.id', $companyId);
        });

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->branch_id) {
            $query->where('destination_branch_id', $this->branch_id);
        }

        return $query;
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
            Column::make('str_number')->title('STR Number'),
            Column::make('source_branch.name', 'sourceBranch.name')->title('Source Branch'),
            Column::make('branch.name')->title('Destination Branch'),
            Column::make('created_by.name', 'createdBy.name')->title('Created By'),
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