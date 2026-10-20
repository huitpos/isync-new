<?php

namespace App\DataTables\Reports;

use App\DataTables\Concerns\UniformAjaxTable;
use App\DataTables\Reports\Concerns\ReadsReportFilters;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class VatSalesReportDataTable extends DataTable
{
    use ReadsReportFilters;
    use UniformAjaxTable;

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return $this->withRelationSearch((new EloquentDataTable($query))
            ->addColumn('machine_number', fn (Transaction $row) => $row->machine->machine_number ?? '')
            ->addColumn('invoice_number', fn (Transaction $row) => $this->transactionLink($row))
            ->addColumn('amount_due', fn (Transaction $row) => $this->money($row->vatable_sales + $row->vat_amount))
            ->addColumn('total_due', fn (Transaction $row) => $this->money($row->vatable_sales + $row->vat_amount))
            ->editColumn('vat_amount', fn (Transaction $row) => $this->money($row->vat_amount))
            ->editColumn('vatable_sales', fn (Transaction $row) => $this->money($row->vatable_sales))
            ->editColumn('vat_exempt_sales', fn (Transaction $row) => $this->money($row->vat_exempt_sales))
            ->filterColumn('machine_number', function ($query, $keyword) {
                $query->whereHas('machine', function ($machine) use ($keyword) {
                    $machine->where('machine_number', 'like', '%' . $keyword . '%');
                });
            })
            ->filterColumn('invoice_number', function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('transaction_number', 'like', '%' . $keyword . '%')
                        ->orWhere('receipt_number', 'like', '%' . $keyword . '%');
                });
            })
            ->filterColumn('amount_due', function ($query, $keyword) {
                $query->whereRaw('(vatable_sales + vat_amount) like ?', ['%' . $keyword . '%']);
            })
            ->rawColumns(['invoice_number']));
    }

    public function query(Transaction $model): QueryBuilder
    {
        [$start, $end] = $this->reportDates();

        return $model->newQuery()
            ->with('machine')
            ->where('branch_id', $this->reportBranchId())
            ->where('is_complete', true)
            ->where('is_void', false)
            ->whereBetween('treg', [$start, $end]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('treg')->title('Date'),
            Column::make('machine_number')->title('Machine Number')->orderable(false),
            Column::make('invoice_number')->title('Sales Invoice Number')->orderable(false),
            Column::make('amount_due')->title('Sub Total')->orderable(false),
            Column::make('total_due')->title('Total Amount Due')->orderable(false)->searchable(false),
            Column::make('vat_amount')->title('Vat Amount'),
            Column::make('vatable_sales')->title('Vatable Sales'),
            Column::make('vat_exempt_sales')->title('Vat Exempt Sales'),
        ];
    }
}
