<?php

namespace App\DataTables\Reports;

use App\DataTables\Concerns\UniformAjaxTable;
use App\DataTables\Reports\Concerns\ReadsReportFilters;
use App\Models\CutOff;
use App\Models\Discount;
use App\Models\EndOfDay;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BirSalesSummaryReportDataTable extends DataTable
{
    use ReadsReportFilters;
    use UniformAjaxTable;

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('ending_amount', fn (EndOfDay $row) => $this->money($row->ending_amount))
            ->editColumn('beginning_amount', fn (EndOfDay $row) => $this->money($row->beginning_amount))
            ->addColumn('manual_sales', fn () => '0')
            ->editColumn('vatable_sales', fn (EndOfDay $row) => $this->money($row->vatable_sales))
            ->editColumn('vat_amount', fn (EndOfDay $row) => $this->money($row->vat_amount))
            ->editColumn('vat_exempt_sales', fn (EndOfDay $row) => $this->money($row->vat_exempt_sales))
            ->editColumn('total_zero_rated_amount', fn (EndOfDay $row) => $this->money($row->total_zero_rated_amount))
            ->addColumn('deduction_sc', fn (EndOfDay $row) => $this->money($this->discountTotal($row, [4])))
            ->addColumn('deduction_pwd', fn (EndOfDay $row) => $this->money($this->discountTotal($row, [5])))
            ->addColumn('deduction_naac', fn (EndOfDay $row) => $this->money($this->discountTotal($row, [29])))
            ->addColumn('deduction_solo', fn (EndOfDay $row) => $this->money($this->discountTotal($row, [11])))
            ->addColumn('deduction_others', fn (EndOfDay $row) => $this->money($this->discountTotal($row, [4, 5, 29, 11], true)))
            ->addColumn('returns', fn (EndOfDay $row) => $this->money($this->returnTotal($row)))
            ->editColumn('total_discount_amount', fn (EndOfDay $row) => $this->money($row->total_discount_amount))
            ->addColumn('vat_adjustment', fn () => '0.00')
            ->addColumn('vat_payable', fn (EndOfDay $row) => $this->money($row->vat_amount))
            ->addColumn('net_of_vat', fn (EndOfDay $row) => $this->money($row->net_sales - $row->vat_amount))
            ->editColumn('total_short_over', fn (EndOfDay $row) => $this->money($row->total_short_over))
            ->addColumn('total_income', fn (EndOfDay $row) => $this->money($row->net_sales - $row->vat_amount))
            ->addColumn('reset_counter', fn (EndOfDay $row) => str_pad((string) $row->ending_gt_counter, 2, '0', STR_PAD_LEFT))
            ->addColumn('remarks', fn () => '');
    }

    public function query(EndOfDay $model): QueryBuilder
    {
        [$start, $end] = $this->reportDates();

        return $model->newQuery()
            ->where('branch_id', $this->reportBranchId())
            ->whereBetween('treg', [$start, $end]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::make('treg')->title('Date'),
            Column::make('beginning_or')->title('Beginning SI/OR No.'),
            Column::make('ending_or')->title('Ending SI/OR No.'),
            Column::make('ending_amount')->title('Grand Accum. Sales Ending Balance'),
            Column::make('beginning_amount')->title('Grand Accum. Beg.Balance'),
            Column::make('manual_sales')->title('Sales Issued w/ Manual SI/OR (per RR 16-2018)')->orderable(false)->searchable(false),
            Column::make('vatable_sales')->title('VATable Sales'),
            Column::make('vat_amount')->title('VATable Amount'),
            Column::make('vat_exempt_sales')->title('VAT-Exempt Sales'),
            Column::make('total_zero_rated_amount')->title('Zero-Rated Sales'),
            Column::make('deduction_sc')->title('Deductions - SC')->orderable(false)->searchable(false),
            Column::make('deduction_pwd')->title('Deductions - PWD')->orderable(false)->searchable(false),
            Column::make('deduction_naac')->title('Deductions - NAAC')->orderable(false)->searchable(false),
            Column::make('deduction_solo')->title('Deductions - Solo Parent')->orderable(false)->searchable(false),
            Column::make('deduction_others')->title('Deductions - Others')->orderable(false)->searchable(false),
            Column::make('returns')->title('Returns')->orderable(false)->searchable(false),
            Column::make('void_qty')->title('Voids'),
            Column::make('total_discount_amount')->title('Total Deductions'),
            Column::make('vat_adjustment')->title('Total VAT Adjustment')->orderable(false)->searchable(false),
            Column::make('vat_payable')->title('Vat Payable')->orderable(false)->searchable(false),
            Column::make('net_of_vat')->title('Net Sales')->orderable(false)->searchable(false),
            Column::make('total_short_over')->title('Sales Overrun / Overflow'),
            Column::make('total_income')->title('Total Income')->orderable(false)->searchable(false),
            Column::make('reset_counter')->title('Reset Counter')->orderable(false)->searchable(false),
            Column::make('reading_number')->title('Z -Counter'),
            Column::make('remarks')->title('Remarks')->orderable(false)->searchable(false),
        ];
    }

    protected function defaultOrder(): array
    {
        return [0, 'desc'];
    }

    private function cutOffIds(EndOfDay $row): array
    {
        return CutOff::where([
            'end_of_day_id' => $row->end_of_day_id,
            'branch_id' => $row->branch_id,
        ])->pluck('cut_off_id')->unique()->all();
    }

    private function discountTotal(EndOfDay $row, array $typeIds, bool $exclude = false): float
    {
        $cutOffIds = $this->cutOffIds($row);
        $query = Discount::where('branch_id', $row->branch_id)
            ->where('is_void', false)
            ->whereIn('cut_off_id', $cutOffIds);

        if ($exclude) {
            $query->whereNotIn('discount_type_id', $typeIds);
        } else {
            $query->whereIn('discount_type_id', $typeIds);
        }

        return (float) $query->sum('discount_amount');
    }

    private function returnTotal(EndOfDay $row): float
    {
        return (float) Transaction::where('branch_id', $row->branch_id)
            ->where('is_void', false)
            ->whereIn('cut_off_id', $this->cutOffIds($row))
            ->sum('total_return_amount');
    }
}
