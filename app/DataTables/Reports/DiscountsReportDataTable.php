<?php

namespace App\DataTables\Reports;

use App\DataTables\Concerns\UniformAjaxTable;
use App\DataTables\Reports\Concerns\ReadsReportFilters;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DiscountsReportDataTable extends DataTable
{
    use ReadsReportFilters;
    use UniformAjaxTable;

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('invoice_number', function ($row) {
                $label = trim($row->transaction_id . ' ' . $row->receipt_number);
                if (!$this->company_slug) {
                    return e($label);
                }

                return '<a href="' . e($this->transactionUrl((int) $row->transaction_id)) . '" target="_blank">' . e($label) . '</a>';
            })
            ->addColumn('customer_info', function ($row) {
                return $row->otherInfo->map(fn ($info) => e($info->name) . ': ' . e($info->value))->implode('<br>');
            })
            ->editColumn('gross_sales', fn ($row) => $this->money($row->gross_sales))
            ->editColumn('net_sales', fn ($row) => $this->money($row->net_sales))
            ->filterColumn('date', function ($query, $keyword) {
                $query->where('transactions.completed_at', 'like', '%' . $keyword . '%');
            })
            ->filterColumn('machine_number', function ($query, $keyword) {
                $query->where('pos_machines.machine_number', 'like', '%' . $keyword . '%');
            })
            ->filterColumn('invoice_number', function ($query, $keyword) {
                $query->where('transactions.receipt_number', 'like', '%' . $keyword . '%');
            })
            ->filterColumn('gross_sales', fn ($query, $keyword) => $query->where('transactions.gross_sales', 'like', '%' . $keyword . '%'))
            ->filterColumn('net_sales', fn ($query, $keyword) => $query->where('transactions.net_sales', 'like', '%' . $keyword . '%'))
            ->filterColumn('discount_name', fn ($query, $keyword) => $query->where('discounts.discount_name', 'like', '%' . $keyword . '%'))
            ->filterColumn('discount_amount', fn ($query, $keyword) => $query->where('discounts.discount_amount', 'like', '%' . $keyword . '%'))
            ->filterColumn('cashier_name', fn ($query, $keyword) => $query->where('transactions.cashier_name', 'like', '%' . $keyword . '%'))
            ->rawColumns(['invoice_number', 'customer_info']);
    }

    public function query(Discount $model): QueryBuilder
    {
        [$start, $end] = $this->reportDates();
        $types = request()->input('discount_types', $this->discount_type_ids ?? []);
        if (!is_array($types)) {
            $types = [$types];
        }

        return $model->newQuery()
            ->select([
                'discounts.id',
                'discounts.discount_id',
                'transactions.completed_at as date',
                'transactions.receipt_number',
                'transactions.gross_sales',
                'transactions.net_sales',
                'discounts.discount_name',
                'discounts.discount_amount',
                'discounts.discount_id',
                'discounts.pos_machine_id',
                'discounts.branch_id',
                'transactions.cashier_name',
                'pos_machines.machine_number',
                'transactions.id as transaction_id',
            ])
            ->join('transactions', function ($join) {
                $join->on('transactions.transaction_id', '=', 'discounts.transaction_id');
                $join->on('transactions.branch_id', '=', 'discounts.branch_id');
                $join->on('transactions.pos_machine_id', '=', 'discounts.pos_machine_id');
            })
            ->join('isync.pos_machines', 'transactions.pos_machine_id', '=', 'pos_machines.id')
            ->whereBetween('discounts.treg', [$start, $end])
            ->where('discounts.is_void', false)
            ->where('transactions.is_void', false)
            ->where('transactions.is_complete', true)
            ->where('discounts.branch_id', $this->reportBranchId())
            ->when($types, function ($query) use ($types) {
                $query->whereIn('discounts.discount_type_id', $types);
            });
    }

    public function getColumns(): array
    {
        return [
            Column::make('date')->title('Date')->name('date'),
            Column::make('machine_number')->title('Machine Number')->name('machine_number'),
            Column::make('invoice_number')->title('Sales Invoice No.')->orderable(false),
            Column::make('discount_name')->title('Name'),
            Column::make('customer_info')->title('OSCA/SC/PWD ID')->orderable(false)->searchable(false),
            Column::make('gross_sales')->title('Gross Sales'),
            Column::make('discount_amount')->title('Sales Discount Granted'),
            Column::make('cashier_name')->title('Cashier Name'),
            Column::make('net_sales')->title('Net Sales'),
        ];
    }
}
