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
        $user = auth()->user();
        $canRevert = $user !== null;

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
            ->addColumn('actions', function (Model $log) use ($canRevert) {
                if ($log->reverted_at) {
                    return '<span class="badge badge-light-warning">Reverted</span><br>'
                        . '<small class="text-muted">' . $log->reverted_at->format('M d, Y H:i') . '</small>';
                }

                if (!$canRevert || str_ends_with($log->movement_type, '_revert')) {
                    return '';
                }

                $url = route('inventory-tracking.revert.show', array_filter([
                    'movement_type' => $log->movement_type,
                    'object_id' => $log->object_id,
                    'branch_id' => $log->branch_id,
                    'return_movement_type' => request('movement_type'),
                    'return_branch_id' => request('branch_id'),
                    'return_product_id' => request('product_id'),
                ]));

                return '<a href="' . e($url) . '" class="btn btn-sm btn-light-danger">Revert</a>';
            })
            ->rawColumns(['product_display', 'previous_qty', 'new_qty', 'processed_at', 'actions']);
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

        if ($this->description) {
            $this->applyDescriptionFilter($query, (string) $this->description);
        }

        $query->orderBy('processed_at', 'desc');

        return $query;
    }

    private function applyDescriptionFilter(QueryBuilder $query, string $description): void
    {
        $keyword = trim($description);

        if ($keyword === '') {
            return;
        }

        $normalized = preg_replace('/^(SI|PD|STD|STO|CONTROL)\s*#?\s*/i', '', $keyword) ?: $keyword;
        $transactionalDb = config('database.connections.transactional_db.database');
        $mysqlDb = config('database.connections.mysql.database');

        $query->where(function (QueryBuilder $outer) use ($keyword, $normalized, $transactionalDb, $mysqlDb) {
            $outer->whereExists(function ($exists) use ($keyword, $mysqlDb) {
                $exists->select(DB::raw(1))
                    ->from("{$mysqlDb}.products as p")
                    ->whereColumn('p.id', 'inventory_movement_logs.product_id')
                    ->where('p.name', 'like', '%' . $keyword . '%');
            })
            ->orWhere(function (QueryBuilder $sub) use ($normalized, $transactionalDb) {
                $sub->whereIn('movement_type', ['transactions', 'transactions_revert'])
                    ->whereExists(function ($exists) use ($normalized, $transactionalDb) {
                        $exists->select(DB::raw(1))
                            ->from("{$transactionalDb}.transactions as t")
                            ->whereColumn('t.transaction_id', 'inventory_movement_logs.object_id')
                            ->whereColumn('t.branch_id', 'inventory_movement_logs.branch_id')
                            ->where(function ($tq) use ($normalized) {
                                $tq->where('t.receipt_number', 'like', '%' . $normalized . '%')
                                    ->orWhere('t.control_number', 'like', '%' . $normalized . '%');
                            });
                    });
            })
            ->orWhere(function (QueryBuilder $sub) use ($normalized, $mysqlDb) {
                $sub->whereIn('movement_type', ['purchase_deliveries', 'purchase_deliveries_revert'])
                    ->whereExists(function ($exists) use ($normalized, $mysqlDb) {
                        $exists->select(DB::raw(1))
                            ->from("{$mysqlDb}.purchase_deliveries as pd")
                            ->whereColumn('pd.id', 'inventory_movement_logs.object_id')
                            ->whereColumn('pd.branch_id', 'inventory_movement_logs.branch_id')
                            ->where('pd.pd_number', 'like', '%' . $normalized . '%');
                    });
            })
            ->orWhere(function (QueryBuilder $sub) use ($normalized, $mysqlDb) {
                $sub->whereIn('movement_type', ['stock_transfer_deliveries', 'stock_transfer_deliveries_revert'])
                    ->whereExists(function ($exists) use ($normalized, $mysqlDb) {
                        $exists->select(DB::raw(1))
                            ->from("{$mysqlDb}.stock_transfer_deliveries as std")
                            ->whereColumn('std.id', 'inventory_movement_logs.object_id')
                            ->where('std.std_number', 'like', '%' . $normalized . '%');
                    });
            })
            ->orWhere(function (QueryBuilder $sub) use ($normalized, $mysqlDb) {
                $sub->whereIn('movement_type', ['stock_transfer_orders', 'stock_transfer_orders_revert'])
                    ->whereExists(function ($exists) use ($normalized, $mysqlDb) {
                        $exists->select(DB::raw(1))
                            ->from("{$mysqlDb}.stock_transfer_orders as sto")
                            ->whereColumn('sto.id', 'inventory_movement_logs.object_id')
                            ->where('sto.sto_number', 'like', '%' . $normalized . '%');
                    });
            });
        });
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
            Column::computed('actions')
                ->title('Actions')
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(120),
        ];
    }

    protected function filename(): string
    {
        return 'InventoryProcessingHistory_' . date('YmdHis');
    }

    private function getMovementDescription(Model $log, string $companySlug): string
    {
        $movementType = str_ends_with($log->movement_type, '_revert')
            ? substr($log->movement_type, 0, -strlen('_revert'))
            : $log->movement_type;

        if ($movementType === 'transactions') {
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

        if ($movementType === 'purchase_deliveries') {
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

        if ($movementType === 'stock_transfer_deliveries') {
            $delivery = DB::select(
                'SELECT id, std_number FROM stock_transfer_deliveries WHERE id = ?',
                [$log->object_id]
            );

            if (isset($delivery[0])) {
                return 'STD #' . e($delivery[0]->std_number);
            }
        }

        if ($movementType === 'stock_transfer_orders') {
            $order = DB::select(
                'SELECT id, sto_number FROM stock_transfer_orders WHERE id = ?',
                [$log->object_id]
            );

            if (isset($order[0])) {
                return 'STO #' . e($order[0]->sto_number);
            }
        }

        return '';
    }
}
