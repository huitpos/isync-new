<?php

namespace App\DataTables\Branch;


use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\PurchaseDelivery as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class PurchaseDeliveriesDataTable extends DataTable
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
            ->addColumn('view_url', function (Model $data) use ($companySlug, $branchSlug) {
                return route('branch.purchase-deliveries.show', [
                    'companySlug' => $companySlug,
                    'branchSlug' => $branchSlug,
                    'purchase_delivery' => $data->id,
                ]);
            })
            ->rawColumns(['pd_number']));
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

        $search = $this->search;
        if (is_string($search) && $search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('pd_number', 'like', '%' . $search . '%')
                    ->orWhere('sales_invoice_number', 'like', '%' . $search . '%')
                    ->orWhere('delivery_number', 'like', '%' . $search . '%')
                    ->orWhereHas('purchaseOrder', function ($query) use ($search) {
                        $query->where('po_number', 'like', '%' . $search . '%');
                    });
            });
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
            Column::make('pd_number'),
            Column::make('purchase_order.po_number', 'purchaseOrder.po_number')->title('po_number'),
            Column::make('sales_invoice_number')->title('Supplier Reference'),
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