<?php

namespace App\DataTables\Reports;

use App\DataTables\Concerns\UniformAjaxTable;
use App\DataTables\Reports\Concerns\ReadsReportFilters;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SalesInvoicesReportDataTable extends DataTable
{
    use ReadsReportFilters;
    use UniformAjaxTable;

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return $this->withRelationSearch((new EloquentDataTable($query))
            ->editColumn('completed_at', fn (Transaction $row) => $row->completed_at)
            ->addColumn('machine_number', fn (Transaction $row) => $row->machine->machine_number ?? '')
            ->addColumn('invoice_number', fn (Transaction $row) => $this->transactionLink($row))
            ->addColumn('table_no', fn () => '')
            ->addColumn('customer_count', fn () => 1)
            ->editColumn('net_sales', fn (Transaction $row) => $this->money($row->net_sales - $row->vat_amount))
            ->editColumn('gross_sales', fn (Transaction $row) => $this->money($row->gross_sales))
            ->editColumn('vatable_sales', fn (Transaction $row) => $this->money($row->vatable_sales))
            ->editColumn('vat_amount', fn (Transaction $row) => $this->money($row->vat_amount))
            ->editColumn('vat_exempt_sales', fn (Transaction $row) => $this->money($row->vat_exempt_sales))
            ->editColumn('vat_expense', fn (Transaction $row) => $this->money($row->vat_expense))
            ->editColumn('discount_amount', fn (Transaction $row) => $this->money($row->discount_amount))
            ->addColumn('payment_types', fn (Transaction $row) => $row->payments->pluck('payment_type_name')->unique()->implode(', '))
            ->editColumn('is_void', fn (Transaction $row) => $row->is_void ? 'Yes' : 'No')
            ->editColumn('tender_amount', fn (Transaction $row) => $this->money($row->tender_amount))
            ->editColumn('service_charge', fn (Transaction $row) => $this->money($row->service_charge))
            ->editColumn('change', fn (Transaction $row) => $this->money($row->change))
            ->addColumn('approver', fn () => '')
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

        return $model->newQuery()
            ->with(['machine', 'payments'])
            ->where('branch_id', $this->reportBranchId())
            ->where('is_complete', true)
            ->whereBetween('completed_at', [$start, $end]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('completed_at')->title('Date'),
            Column::make('machine_number')->title('Machine No')->orderable(false),
            Column::make('invoice_number')->title('SI No.')->orderable(false),
            Column::make('table_no')->title('Table No.')->orderable(false)->searchable(false),
            Column::make('customer_count')->title('Customer Count')->orderable(false)->searchable(false),
            Column::make('cashier_name')->title('Cashier Details'),
            Column::make('shift_number')->title('Shift'),
            Column::make('gross_sales')->title('Gross Sales'),
            Column::make('net_sales')->title('Net Sales'),
            Column::make('vatable_sales')->title('VAT Sales'),
            Column::make('vat_amount')->title('VAT Amount'),
            Column::make('vat_exempt_sales')->title('VAT Exempt'),
            Column::make('vat_expense')->title('VAT Expense'),
            Column::make('discount_amount')->title('Discount'),
            Column::make('payment_types')->title('Type Of Payment')->orderable(false),
            Column::make('is_void')->title('Void'),
            Column::make('total_quantity')->title('Quantity'),
            Column::make('tender_amount')->title('Amount Paid'),
            Column::make('service_charge')->title('Service Charge'),
            Column::make('type')->title('Transaction Type'),
            Column::make('change')->title('Change'),
            Column::make('approver')->title('Approver')->orderable(false)->searchable(false),
            Column::make('remarks')->title('Remarks'),
        ];
    }
}
