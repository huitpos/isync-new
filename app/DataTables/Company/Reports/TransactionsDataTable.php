<?php

namespace App\DataTables\Company\Reports;

use App\Models\Transaction as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class TransactionsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('payment_types', function (Model $data) {
                return $data->payments->pluck('payment_type_name')->unique()->implode(', ');
            })
            ->addColumn('approver', function (Model $data) {
                return '';
            });
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(Model $model): QueryBuilder
    {
        return $model->newQuery()
            ->withCount('items')
            ->with('payments');
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
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>",)
            ->addTableClass('table align-middle table-striped table-row-bordered fs-6 gy-5 gs-7 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(0, 'asc');
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('created_at')->title('Date'),
            Column::make('pos_machine_id')->title('Machine No.'),
            Column::make('receipt_number')->title('OR No.'),
            Column::make('cashier_name')->title('Cashier Details'),
            Column::make('shift_number')->title('Shift'),
            Column::make('gross_sales')->title('Gross Sales'),
            Column::make('net_sales')->title('Net Sales'),
            Column::make('vatable_sales')->title('VAT Sales'),
            Column::make('vat_amount')->title('VAT Amount'),
            Column::make('vat_exempt_sales')->title('VAT Exempt'),
            Column::make('discount_amount')->title('Discount'),
            Column::make('payment_types')->title('Type Of Payment'),
            Column::make('is_void')->title('Void'),
            Column::make('items_count')->title('Quantity'),
            Column::make('tender_amount')->title('Amount Paid'),
            Column::make('service_charge')->title('Service Charge'),
            Column::make('type')->title('Transaction Type'),
            Column::make('change')->title('Change'),
            Column::make('')->title('Approver'),
        ];
    }
}
