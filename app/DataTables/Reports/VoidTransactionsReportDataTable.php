<?php

namespace App\DataTables\Reports;

use App\DataTables\Concerns\UniformAjaxTable;
use App\DataTables\Reports\Concerns\ReadsReportFilters;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class VoidTransactionsReportDataTable extends DataTable
{
    use ReadsReportFilters;
    use UniformAjaxTable;

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return $this->withRelationSearch((new EloquentDataTable($query))
            ->addColumn('machine_number', fn (Transaction $row) => $row->machine->machine_number ?? '')
            ->addColumn('invoice_number', fn (Transaction $row) => $this->transactionLink($row))
            ->editColumn('discount_amount', fn (Transaction $row) => $this->money($row->discount_amount))
            ->editColumn('gross_sales', fn (Transaction $row) => $this->money($row->gross_sales))
            ->editColumn('net_sales', fn (Transaction $row) => $this->money($row->net_sales - $row->vat_amount))
            ->addColumn('approver', fn () => '')
            ->addColumn('payment_types', fn (Transaction $row) => $row->payments->pluck('payment_type_name')->unique()->implode(', '))
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
            ->filterColumn('payment_types', function ($query, $keyword) {
                $query->whereHas('payments', function ($payment) use ($keyword) {
                    $payment->where('payment_type_name', 'like', '%' . $keyword . '%');
                });
            })
            ->rawColumns(['invoice_number']));
    }

    public function query(Transaction $model): QueryBuilder
    {
        [$start, $end] = $this->reportDates();
        $paymentTypeId = request()->input('payment_type', request()->input('payment_type_id'));

        $query = $model->newQuery()
            ->with(['machine', 'payments'])
            ->where('transactions.branch_id', $this->reportBranchId())
            ->where('transactions.is_void', true)
            ->whereBetween('transactions.treg', [$start, $end]);

        if ($paymentTypeId) {
            $query->whereHas('payments', function ($payment) use ($paymentTypeId) {
                $payment->where('payment_type_id', $paymentTypeId);
            });
        }

        return $query;
    }

    public function getColumns(): array
    {
        return [
            Column::make('void_counter')->title('Void No.'),
            Column::make('treg')->title('Date'),
            Column::make('invoice_number')->title('Sales Invoice #')->orderable(false),
            Column::make('machine_number')->title('Machine #')->orderable(false),
            Column::make('discount_amount')->title('Discount Amount'),
            Column::make('gross_sales')->title('Gross Sales'),
            Column::make('net_sales')->title('Net Sales'),
            Column::make('void_remarks')->title('Remarks'),
            Column::make('cashier_name')->title('Cashier'),
            Column::make('approver')->title('Approved By')->orderable(false)->searchable(false),
            Column::make('payment_types')->title('Payment Type')->orderable(false),
        ];
    }
}
