<?php

namespace App\DataTables\Reports;

use App\DataTables\Concerns\UniformAjaxTable;
use App\DataTables\Reports\Concerns\ReadsReportFilters;
use App\Services\FifoCostService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\QueryDataTable;
use Yajra\DataTables\Services\DataTable;

class ItemSalesReportDataTable extends DataTable
{
    use ReadsReportFilters;
    use UniformAjaxTable;

    public function dataTable(Builder $query): QueryDataTable
    {
        $totals = DB::table(DB::raw('(' . $query->toSql() . ') as item_sales_total'))
            ->mergeBindings($query)
            ->selectRaw('COALESCE(SUM(net), 0) as net')
            ->first();
        $totalNet = (float) ($totals->net ?? 0);

        return (new QueryDataTable($query))
            ->editColumn('qty', fn ($row) => $this->money($row->qty))
            ->editColumn('cost', fn ($row) => $this->money($row->cost))
            ->editColumn('srp', fn ($row) => $this->money($row->srp))
            ->editColumn('gross', fn ($row) => $this->money($row->gross))
            ->editColumn('discount', fn ($row) => $this->money($row->discount))
            ->editColumn('net', fn ($row) => $this->money($row->net))
            ->addColumn('percentage', function ($row) use ($totalNet) {
                if ($totalNet == 0.0) {
                    return '0.00';
                }

                return $this->money(((float) $row->net / $totalNet) * 100);
            });
    }

    public function query(): Builder
    {
        [$start, $end] = $this->reportDates();
        $branchId = $this->reportBranchId();
        $transactionalDb = config('database.connections.transactional_db.database');
        $fifoUnitCost = app(FifoCostService::class)->unitCostExpression($branchId, $start, $end);

        $sql = "SELECT
                {$transactionalDb}.orders.product_id,
                products.name AS product_name,
                products.sku,
                {$fifoUnitCost} AS cost,
                products.srp,
                departments.name AS department,
                SUM({$transactionalDb}.orders.gross) AS gross,
                SUM({$transactionalDb}.orders.qty) AS qty,
                SUM({$transactionalDb}.discount_details.discount_amount) AS discount,
                SUM({$transactionalDb}.orders.total) AS net
            FROM {$transactionalDb}.transactions
            INNER JOIN {$transactionalDb}.orders ON transactions.transaction_id = orders.transaction_id
                AND transactions.branch_id = orders.branch_id
                AND transactions.pos_machine_id = orders.pos_machine_id
                AND orders.is_void = FALSE
                AND orders.is_completed = TRUE
                AND orders.is_back_out = FALSE
                AND orders.is_return = FALSE
            LEFT JOIN {$transactionalDb}.discount_details ON orders.order_id = discount_details.order_id
                AND orders.branch_id = discount_details.branch_id
                AND orders.pos_machine_id = discount_details.pos_machine_id
            INNER JOIN isync.products ON orders.product_id = products.id
            INNER JOIN isync.departments ON products.department_id = departments.id
            WHERE transactions.is_complete = TRUE
                AND transactions.branch_id = {$branchId}
                AND transactions.is_void = FALSE
                AND transactions.is_back_out = FALSE
                AND transactions.treg BETWEEN '{$start}' AND '{$end}'
            GROUP BY orders.product_id";

        return DB::table(DB::raw("({$sql}) as item_sales"));
    }

    public function totals(): object
    {
        return DB::table(DB::raw('(' . $this->query()->toSql() . ') as item_sales_totals'))
            ->mergeBindings($this->query())
            ->selectRaw('COALESCE(SUM(qty), 0) as qty, COALESCE(SUM(cost), 0) as cost, COALESCE(SUM(srp), 0) as srp, COALESCE(SUM(gross), 0) as gross, COALESCE(SUM(discount), 0) as discount, COALESCE(SUM(net), 0) as net')
            ->first();
    }

    public function getColumns(): array
    {
        return [
            Column::make('department')->title('Department'),
            Column::make('sku')->title('SKU'),
            Column::make('product_name')->title('Product Name'),
            Column::make('qty')->title('Qty Sold'),
            Column::make('cost')->title('Item Cost'),
            Column::make('srp')->title('Selling Price'),
            Column::make('gross')->title('Gross Amount'),
            Column::make('discount')->title('Discounts'),
            Column::make('net')->title('Net Sales'),
            Column::make('percentage')->title('Percentage')->orderable(false)->searchable(false),
        ];
    }
}
