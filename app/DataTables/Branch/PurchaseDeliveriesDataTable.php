<?php

namespace App\DataTables\Branch;

use App\Models\PurchaseDelivery as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class PurchaseDeliveriesDataTable extends DataTable
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
            ->editColumn('pd_number', function (Model $data) use($companySlug, $branchSlug){
                return '<a href="' . route('branch.purchase-deliveries.show', [
                    'companySlug' => $companySlug,
                    'branchSlug' => $branchSlug,
                    'purchase_delivery' => $data->id
                ]) . '">' . $data->pd_number . '</a>';
            })
            ->rawColumns(['pd_number']);
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(Model $model): QueryBuilder
    {
        $branchId = $this->branch_id;
        $query = $model->newQuery()
            ->with([
                'createdBy',
                'purchaseOrder',
                'branch'
            ])
            ->whereHas('branch', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });

        if ($this->status) {
            $query->where('status', $this->status);
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
            Column::make('pd_number'),
            Column::make('purchase_order.po_number', 'purchaseOrder.po_number')->title('po_number'),
            Column::make('sales_invoice_number')->title('Sales Invoice'),
            Column::make('delivery_number')->title('Delivery Number'),
            Column::make('branch.name', 'branch.name'),
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