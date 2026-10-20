<?php

namespace App\DataTables\Reports;

use App\DataTables\Concerns\UniformAjaxTable;
use App\DataTables\Reports\Concerns\ReadsReportFilters;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\QueryDataTable;
use Yajra\DataTables\Services\DataTable;

class AccountReceivablesDataTable extends DataTable
{
    use ReadsReportFilters;
    use UniformAjaxTable;

    public function dataTable(Builder $query): QueryDataTable
    {
        return (new QueryDataTable($query))
            ->editColumn('name', function ($row) {
                $url = route('branch.reports.account-receivable-details', [
                    'companySlug' => $this->company_slug,
                    'branchSlug' => $this->branch_slug,
                    'customerId' => $row->charge_account_id,
                ]);

                return '<a target="_blank" href="' . e($url) . '">' . e($row->name) . '</a>';
            })
            ->editColumn('total_sales', fn ($row) => $this->money($row->total_sales))
            ->editColumn('redeemed_sales', fn ($row) => $this->money($row->redeemed_sales))
            ->editColumn('not_redeemed_sales', fn ($row) => $this->money($row->not_redeemed_sales))
            ->addColumn('uncollected_percent', function ($row) {
                if ((float) $row->total_sales == 0.0) {
                    return '0.00';
                }

                return $this->money(((float) $row->not_redeemed_sales / (float) $row->total_sales) * 100);
            })
            ->rawColumns(['name']);
    }

    public function query(): Builder
    {
        $branchId = $this->reportBranchId();
        $sql = "SELECT
                transactions.charge_account_id,
                isync.charge_accounts.name,
                isync.charge_accounts.address,
                SUM(transactions.gross_sales) AS total_sales,
                SUM(CASE WHEN transactions.is_account_receivable_redeem = TRUE THEN transactions.gross_sales ELSE 0 END) AS redeemed_sales,
                SUM(CASE WHEN transactions.is_account_receivable_redeem = FALSE THEN transactions.gross_sales ELSE 0 END) AS not_redeemed_sales
            FROM transactional_db.transactions
            INNER JOIN isync.charge_accounts ON transactions.charge_account_id = charge_accounts.id
            WHERE transactions.is_account_receivable = TRUE
                AND transactions.is_void = FALSE
                AND transactions.is_back_out = FALSE
                AND transactions.is_complete = TRUE
                AND transactions.branch_id = {$branchId}
            GROUP BY transactions.charge_account_id, isync.charge_accounts.name, isync.charge_accounts.address";

        return DB::table(DB::raw("({$sql}) as account_receivables"));
    }

    public function getColumns(): array
    {
        return [
            Column::make('name')->title('Customer Name'),
            Column::make('address')->title('Address'),
            Column::make('total_sales')->title('Sales'),
            Column::make('redeemed_sales')->title('Collected'),
            Column::make('not_redeemed_sales')->title('Uncollected'),
            Column::make('uncollected_percent')->title('Uncollected %')->orderable(false)->searchable(false),
        ];
    }
}
