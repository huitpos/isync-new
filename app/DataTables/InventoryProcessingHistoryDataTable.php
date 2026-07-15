<?php

namespace App\DataTables;

use App\Models\InventoryMovementLog as Model;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class InventoryProcessingHistoryDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $companySlug = $this->company_slug;
        $types = $this->movement_types;

        return (new EloquentDataTable($query))
            ->addColumn('product_display', function (Model $log) use ($companySlug) {
                $description = $this->getMovementDescription($log, $companySlug);

                if ($log->product) {
                    return '<strong>' . e($log->product->name) . '</strong><br>'
                        . '<small class="text-muted">' . $description . '</small>';
                }

                return 'Product #' . $log->product_id;
            })
            ->editColumn('movement_type', fn (Model $log) => $types[$log->movement_type] ?? $log->movement_type)
            ->editColumn('previous_qty', fn (Model $log) => '<span class="text-muted">' . number_format($log->previous_qty, 2) . '</span>')
            ->editColumn('new_qty', function (Model $log) {
                $diff = $log->new_qty - $log->previous_qty;
                $sign = $diff >= 0 ? '+' : '';
                $class = $diff >= 0 ? 'text-success' : 'text-danger';

                return '<span class="fw-bold">' . number_format($log->new_qty, 2) . '</span><br>'
                    . '<small class="' . $class . '">' . $sign . number_format($diff, 2) . '</small>';
            })
            ->addColumn('processed_by_name', fn (Model $log) => $log->processedBy ? e($log->processedBy->name) : 'System')
            ->editColumn('processed_at', function (Model $log) {
                return '<small>' . $log->processed_at->format('M d, Y') . '</small><br>'
                    . '<small class="text-muted">' . $log->processed_at->format('H:i:s') . '</small>';
            })
            ->rawColumns(['product_display', 'previous_qty', 'new_qty', 'processed_at']);
    }

    public function query(Model $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['branch', 'product', 'processedBy']);

        if ($this->branch_id) {
            $query->where('branch_id', $this->branch_id);
        } else {
            $query->whereIn('branch_id', $this->branch_ids);
        }

        if ($this->movement_type) {
            $query->where('movement_type', $this->movement_type);
        }

        if ($this->product_id) {
            $query->where('product_id', $this->product_id);
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('inventory-history-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>")
            ->addTableClass('table table-striped table-hover align-middle')
            ->setTableHeadClass('table-dark')
            ->orderBy(7, 'desc');
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::make('product_display')->title('Product')->orderable(false)->searchable(false),
            Column::make('movement_type')->title('Movement Type'),
            Column::make('branch.name')->title('Branch'),
            Column::make('previous_qty')->title('Previous Qty'),
            Column::make('new_qty')->title('New Qty'),
            Column::make('processed_by_name')->title('Processed By')->orderable(false)->searchable(false),
            Column::make('processed_at')->title('Processed At'),
        ];
    }

    protected function filename(): string
    {
        return 'InventoryProcessingHistory_' . date('YmdHis');
    }

    private function getMovementDescription(Model $log, string $companySlug): string
    {
        if ($log->movement_type === 'transactions') {
            $transaction = DB::select(
                'SELECT * FROM transactional_db.transactions WHERE transaction_id = ? and branch_id = ?',
                [$log->object_id, $log->branch_id]
            );

            if (isset($transaction[0])) {
                $url = '/' . $companySlug . '/reports/transaction/' . $transaction[0]->id;
                $receiptNumber = e($transaction[0]->receipt_number);

                return '<a href="' . $url . '" target="_blank">SI #' . $receiptNumber . '</a>';
            }
        }

        if ($log->movement_type === 'purchase_deliveries') {
            $purchaseDelivery = DB::select(
                'SELECT * FROM purchase_deliveries WHERE id = ? and branch_id = ?',
                [$log->object_id, $log->branch_id]
            );

            if (isset($purchaseDelivery[0])) {
                $url = '/' . $companySlug . '/purchase-deliveries/' . $purchaseDelivery[0]->id;
                $pdNumber = e($purchaseDelivery[0]->pd_number);

                return '<a href="' . $url . '" target="_blank">PD #' . $pdNumber . '</a>';
            }
        }

        return '';
    }
}
