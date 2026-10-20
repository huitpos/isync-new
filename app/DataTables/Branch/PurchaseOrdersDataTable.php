<?php

namespace App\DataTables\Branch;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\PurchaseOrder as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class PurchaseOrdersDataTable extends DataTable
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
            ->editColumn('po_number', function (Model $data) use ($companySlug, $branchSlug) {
                return view('branch.datatables._link', [
                    'url' => route('branch.purchase-orders.show', [
                        'companySlug' => $companySlug,
                        'branchSlug' => $branchSlug,
                        'purchase_order' => $data->id,
                    ]),
                    'text' => $data->po_number,
                ]);
            })
            ->editColumn('created_at', function (Model $data) {
                return $data->created_at;
            })
            ->addColumn('view_url', function (Model $data) use($companySlug, $branchSlug) {
                return route('branch.purchase-orders.show', [
                    'companySlug' => $companySlug,
                    'branchSlug' => $branchSlug,
                    'purchase_order' => $data->id
                ]);
            })
            ->addColumn('delivery_url', function (Model $data) use($companySlug, $branchSlug) {
                if ($data->is_closed) {
                    return '';
                }

                return '<a href="' . route('branch.purchase-delivery.create', ['companySlug' => $companySlug, 'branchSlug' => $branchSlug, 'purchaseOrderId' => $data->id]) . '"><i class="fa-solid fa-truck fs-3" title="deliver"></i></a>';
            })
            ->rawColumns(['delivery_url']));
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Model $model): QueryBuilder
    {
        $branchId = $this->branch_id;
        $query =  $model->newQuery()
            ->with([
                'createdBy',
                'purchaseRequest',
                'branch'
            ])
            ->whereHas('branch', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });

        $search = $this->search;
        if (is_string($search) && $search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('po_number', 'like', '%' . $search . '%')
                    ->orWhereHas('purchaseRequest', function ($query) use ($search) {
                        $query->where('pr_number', 'like', '%' . $search . '%');
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
            Column::make('po_number')->title('PO Number'),
            Column::make('purchase_request.pr_number', 'purchaseRequest.pr_number')->title('PR Number'),
            Column::make('branch.name'),
            Column::make('created_by.name', 'createdBy.name')->title('created by'),
            Column::make('created_at'),
            Column::make('delivery_url')->title('')->orderable(false)->searchable(false),
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