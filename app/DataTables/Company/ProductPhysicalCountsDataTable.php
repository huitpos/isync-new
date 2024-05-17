<?php

namespace App\DataTables\Company;

use App\Models\ProductPhysicalCount as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ProductPhysicalCountsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $companySlug = $this->company_slug;

        return (new EloquentDataTable($query))
            ->editColumn('sto_number', function (Model $data) use($companySlug) {
                return '<a href="' . route('company.product-physical-counts.show', [
                    'companySlug' => $companySlug,
                    'product_physical_count' => $data->id
                ]) . '">' . $data->id . '</a>';
            })
            ->editColumn('created_at', function (Model $data) {
                return $data->created_at;
            })
            ->rawColumns(['sto_number']);
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(Model $model): QueryBuilder
    {
        $companyId = $this->company_id;
        $query =  $model->newQuery()
            ->with([
                'createdBy',
                'branch'
            ])
            ->whereHas('branch.company', function ($query) use ($companyId) {
                $query->where('companies.id', $companyId);
            });

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->branch_id) {
            $query->where('branch_id', $this->branch_id);
        }

        return $query;
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
            Column::make('sto_number')->title('ID'),
            Column::make('branch.name')->title('Branch'),
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