<?php

namespace App\DataTables\Company;

use App\Models\PurchaseOrder as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class PurchaseOrdersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $branchSlug = $this->branch_slug;
        $companySlug = $this->company_slug;

        return (new EloquentDataTable($query))
            ->editColumn('created_at', function (Model $data) {
                return $data->created_at;
            })
            ->addColumn('view_url', function (Model $data) use($companySlug) {
                return route('company.purchase-orders.show', [
                    'companySlug' => $companySlug,
                    'purchase_order' => $data->id
                ]);
            });
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(Model $model): QueryBuilder
    {
        $companyId = $this->company_id;
        $query = $model->newQuery()
            ->with([
                'purchaseRequest',
                'createdBy',
                'branch'
            ])
            ->whereHas('branch.company', function ($query) use ($companyId) {
                $query->where('companies.id', $companyId);
            });

        if ($this->branch_id) {
            $query->where('branch_id', $this->branch_id);
        }

        if ($this->search) {
            //wrap all in a or group
            $query->where(function ($query) {
                $query->where('po_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('purchaseRequest', function ($query) {
                        $query->where('pr_number', 'like', '%' . $this->search . '%');
                    });
            });
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
            Column::make('po_number'),
            Column::make('pr_number'),
            Column::make('branch.name'),
            Column::make('created_by.name', 'createdBy.name')->title('created by'),
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