<?php

namespace App\DataTables\Reports;

use App\DataTables\Concerns\UniformAjaxTable;
use App\DataTables\Reports\Concerns\ReadsReportFilters;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BirDiscountBookDataTable extends DataTable
{
    use ReadsReportFilters;
    use UniformAjaxTable;

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $book = $this->book ?: 'senior';

        return (new EloquentDataTable($query))
            ->addColumn('guest_name', fn (Discount $row) => $this->infoValue($row, $this->nameKeys($book)))
            ->addColumn('guest_id', fn (Discount $row) => $this->infoValue($row, $this->idKeys($book)))
            ->addColumn('guest_tin', fn (Discount $row) => $this->infoValue($row, ['TIN:']))
            ->addColumn('child_name', fn (Discount $row) => $this->infoValue($row, ['NAME OF CHILD:']))
            ->addColumn('child_birth', fn (Discount $row) => $this->infoValue($row, ['BIRTH DATE OF CHILD:', 'BIRTHDATE OF CHILD:']))
            ->addColumn('child_age', fn (Discount $row) => $this->infoValue($row, ['AGE OF CHILD:', 'AGE:']))
            ->addColumn('receipt_number', fn (Discount $row) => $row->transaction->receipt_number ?? '')
            ->addColumn('gross_sales', fn (Discount $row) => $this->money($row->transaction->gross_sales ?? 0))
            ->addColumn('vat_amount', fn (Discount $row) => $this->money($row->transaction->vat_amount ?? 0))
            ->addColumn('vat_exempt_sales', fn (Discount $row) => $this->money($row->transaction->vat_exempt_sales ?? 0))
            ->editColumn('discount_amount', fn (Discount $row) => $this->money($row->discount_amount))
            ->addColumn('net_sales', fn (Discount $row) => $this->money($row->transaction->net_sales ?? 0))
            ->filterColumn('receipt_number', function ($query, $keyword) {
                $query->whereHas('transaction', function ($transaction) use ($keyword) {
                    $transaction->where('receipt_number', 'like', '%' . $keyword . '%');
                });
            });
    }

    public function query(Discount $model): QueryBuilder
    {
        [$start, $end] = $this->reportDates();

        return $model->newQuery()
            ->with(['otherInfo', 'transaction'])
            ->where('discount_type_id', $this->typeId())
            ->where('branch_id', $this->reportBranchId())
            ->where('is_void', false)
            ->whereBetween('treg', [$start, $end]);
    }

    public function getColumns(): array
    {
        if (($this->book ?: 'senior') === 'solo') {
            return [
                Column::make('treg')->title('Date'),
                Column::make('guest_name')->title('Name of Solo Parent')->orderable(false)->searchable(false),
                Column::make('guest_id')->title('SPIC No.')->orderable(false)->searchable(false),
                Column::make('child_name')->title('Name of child')->orderable(false)->searchable(false),
                Column::make('child_birth')->title('Birth Date of child')->orderable(false)->searchable(false),
                Column::make('child_age')->title('Age of child')->orderable(false)->searchable(false),
                Column::make('receipt_number')->title('SI / OR Number')->orderable(false),
                Column::make('gross_sales')->title('Gross Sales')->orderable(false)->searchable(false),
                Column::make('discount_amount')->title('Discount'),
                Column::make('net_sales')->title('Net Sales')->orderable(false)->searchable(false),
            ];
        }

        $titles = [
            'senior' => ['Name of Senior Citizen (SC)', 'OSCA ID No./ SC ID No.', 'SC TIN'],
            'pwd' => ['Name of Person with Disability (PWD)', 'PWD ID No.', 'PWD TIN'],
            'naac' => ['Name of National Athlete/Coach', 'PNSTM ID No.', 'TIN'],
        ];
        [$name, $id, $tin] = $titles[$this->book ?: 'senior'] ?? $titles['senior'];

        return [
            Column::make('treg')->title('Date'),
            Column::make('guest_name')->title($name)->orderable(false)->searchable(false),
            Column::make('guest_id')->title($id)->orderable(false)->searchable(false),
            Column::make('guest_tin')->title($tin)->orderable(false)->searchable(false),
            Column::make('receipt_number')->title('SI/OR Number')->orderable(false),
            Column::make('gross_sales')->title('Sales (inclusive of VAT)')->orderable(false)->searchable(false),
            Column::make('vat_amount')->title('VAT Amount')->orderable(false)->searchable(false),
            Column::make('vat_exempt_sales')->title('VAT Exempt Sales')->orderable(false)->searchable(false),
            Column::make('discount_amount')->title('Discount'),
            Column::make('net_sales')->title('Net Sales')->orderable(false)->searchable(false),
        ];
    }

    private function typeId(): int
    {
        return [
            'senior' => 4,
            'pwd' => 5,
            'naac' => 29,
            'solo' => 11,
        ][$this->book ?: 'senior'] ?? 4;
    }

    private function nameKeys(string $book): array
    {
        return match ($book) {
            'pwd' => ['NAME:'],
            'naac' => ['NAME OF NATIONAL ATHELETE/COACH:'],
            'solo' => ['NAME OF SOLO PARENT:', 'NAME:'],
            default => ['NAME OF SENIOR:'],
        };
    }

    private function idKeys(string $book): array
    {
        return match ($book) {
            'pwd' => ['PWD ID NO.:'],
            'naac' => ['PNSTM ID No.'],
            'solo' => ['SPIC NO.:', 'ID NO.:'],
            default => ['OSCA/SC ID NO.:'],
        };
    }

    private function infoValue(Discount $discount, array $keys): string
    {
        foreach ($discount->otherInfo as $info) {
            if (in_array($info->name, $keys, true)) {
                return $info->value;
            }
        }

        return '';
    }
}
