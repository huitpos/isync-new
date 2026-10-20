<?php

namespace App\DataTables\Branch;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\PurchaseReturn;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PurchaseReturnsDataTable extends DataTable
{
    use UniformAjaxTable;

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return $this->withRelationSearch((new EloquentDataTable($query))
            ->editColumn('return_number', function (PurchaseReturn $row) {
                $url = route('branch.purchase-returns.show', [
                    'companySlug' => $this->company_slug,
                    'branchSlug' => $this->branch_slug,
                    'id' => $row->id,
                ]);

                return '<a href="' . e($url) . '">' . e($row->return_number) . '</a>';
            })
            ->editColumn('return_date', fn (PurchaseReturn $row) => optional($row->return_date)->format('Y-m-d'))
            ->editColumn('status', fn (PurchaseReturn $row) => ucfirst($row->status))
            ->rawColumns(['return_number']));
    }

    public function query(PurchaseReturn $model): QueryBuilder
    {
        $status = request()->input('status');

        return $model->newQuery()
            ->with(['supplier', 'purchaseOrder'])
            ->where('branch_id', $this->branch_id)
            ->when($status, fn ($query) => $query->where('status', $status));
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::make('return_number')->title('Return Number'),
            Column::make('purchase_order.po_number', 'purchaseOrder.po_number')->title('PO Number'),
            Column::make('supplier.name')->title('Supplier'),
            Column::make('return_date')->title('Date'),
            Column::make('status')->title('Status'),
        ];
    }

    protected function defaultOrder(): array
    {
        return [0, 'desc'];
    }
}
