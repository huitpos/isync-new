<?php

namespace App\DataTables\Company;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\DebitMemo;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DebitMemosDataTable extends DataTable
{
    use UniformAjaxTable;

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return $this->withRelationSearch((new EloquentDataTable($query))
            ->editColumn('memo_number', function (DebitMemo $row) {
                $url = route('company.debit-memos.show', [
                    'companySlug' => $this->company_slug,
                    'id' => $row->id,
                ]);

                return '<a href="' . e($url) . '">' . e($row->memo_number) . '</a>';
            })
            ->editColumn('total_amount', fn (DebitMemo $row) => number_format((float) $row->total_amount, 2))
            ->editColumn('status', fn (DebitMemo $row) => ucfirst($row->status))
            ->rawColumns(['memo_number']));
    }

    public function query(DebitMemo $model): QueryBuilder
    {
        $companyId = $this->company_id;
        $status = request()->input('status');
        $branchId = request()->input('branch_id');

        return $model->newQuery()
            ->with(['supplier', 'supplierInvoice', 'branch'])
            ->whereHas('branch', fn ($query) => $query->where('company_id', $companyId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::make('memo_number')->title('Memo Number'),
            Column::make('supplier_invoice.invoice_number', 'supplierInvoice.invoice_number')->title('Invoice'),
            Column::make('supplier.name')->title('Supplier'),
            Column::make('branch.name')->title('Branch'),
            Column::make('total_amount')->title('Amount'),
            Column::make('status')->title('Status'),
        ];
    }

    protected function defaultOrder(): array
    {
        return [0, 'desc'];
    }
}
