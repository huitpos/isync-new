<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use App\Models\CutOff;
use App\Models\Branch;
use App\Models\PaymentType;
use App\Models\DiscountType;
use App\Models\Payment;
use App\Models\Discount;
use App\Models\Transaction;

class DiscountsReportExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $branchId;
    protected $paymentTypes;
    protected $discountTypes;
    protected $numberOfColumns;
    protected $headers;

    public function __construct($branchId, $startDate, $endDate, $filterDiscountTypes)
    {
        $this->branchId = $branchId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filterDiscountTypes = $filterDiscountTypes;

        $headers =  [
            'Date',
            'Sales Invoice No.',
            'Name',
            'OSCA/SC/PWD ID',
            'Gross Sales',
            'Sales Discount Granted',
        ];

        $this->numberOfColumns = count($headers);
        $this->headers = $headers;
    }

    /**
     * Return a collection of data for the export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $discounts = Discount::select([
                'transactions.completed_at as date',
                'transactions.receipt_number',
                'transactions.gross_sales',
                'discounts.discount_name',
                'discounts.discount_amount',
                'discounts.discount_id',
                'discounts.pos_machine_id',
                'discounts.branch_id',
            ])
            ->join('transactions', function($join) {
                    $join->on('transactions.transaction_id', '=', 'discounts.transaction_id');
                    $join->on('transactions.branch_id', '=', 'discounts.branch_id');
                    $join->on('transactions.pos_machine_id', '=', 'discounts.pos_machine_id');
            })
            ->whereBetween('discounts.treg', [$this->startDate, $this->endDate])
            ->where('discounts.is_void', false)
            ->where('transactions.is_void', false)
            ->where('transactions.is_complete', true)
            ->whereIn('discounts.discount_type_id', $this->filterDiscountTypes)
            ->where('discounts.branch_id', $this->branchId)
            ->get();

        return new Collection($discounts);
    }

    /**
     * Define the headings for the export.
     *
     * @return array
     */
    public function headings(): array
    {
        return $this->headers;
    }

    public function map($discount): array
    {
        $data = [
            $discount->date,
            $discount->receipt_number,
            $discount->discount_name,
        ];

        $otherInfos = '';
        foreach ($discount->otherInfo as $otherInfo) {
            $otherInfos .= "$otherInfo->name: $otherInfo->value\n";
        }

        $data[] = $otherInfos;
        $data[] = $discount->gross_sales;
        $data[] = $discount->discount_amount;

        return $data;
    }

    /**
     * Define the start cell for the export.
     *
     * @return string
     */
    public function startCell(): string
    {
        return 'A9'; // Data will start from cell A2
    }

    /**
     * Register events to modify the sheet.
     *
     * @return array
     */
    public function registerEvents(): array
    {
        $columnLetter = $this->getColumnLetter($this->numberOfColumns);
        $branch = Branch::find($this->branchId);
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        return [
            AfterSheet::class => function(AfterSheet $event) use($columnLetter, $branch, $startDate, $endDate) {
                $event->sheet->mergeCells('A1:'.$columnLetter.'1');
                $event->sheet->setCellValue('A1', $branch->company->company_name);

                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A1')->getFont()->setBold(true);

                $event->sheet->mergeCells('A2:'.$columnLetter.'2');
                $event->sheet->setCellValue('A2', $branch->name);

                $event->sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->mergeCells('A3:'.$columnLetter.'3');
                $event->sheet->setCellValue('A3', $branch->unit_floor_number . ', ' . $branch->street . ', ' . $branch->city->name . ', ' . $branch->province->name . ', ' . $branch->region->name);

                $event->sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->mergeCells('A4:'.$columnLetter.'4');
                $event->sheet->setCellValue('A4', 'X Reading Report');
                $event->sheet->getStyle('A4')->getFont()->setBold(true);

                $event->sheet->mergeCells('A5:'.$columnLetter.'5');
                $event->sheet->setCellValue('A5', 'Date range: ' . $startDate . ' - ' . $endDate);

                $event->sheet->mergeCells('A6:Q6');
                $event->sheet->setCellValue('A6', 'Date generated: ' . now()->format('Y-m-d H:i:s'));

                $event->sheet->mergeCells('A7:Q7');
                $event->sheet->setCellValue('A7', 'Created by: ' . auth()->user()->name);

                $totalRows = $event->sheet->getHighestRow();
            },
        ];
    }

    function getColumnLetter($columnNumber)
    {
        $letter = '';
        while ($columnNumber > 0) {
            $columnNumber--;
            $letter = chr(65 + ($columnNumber % 26)) . $letter;
            $columnNumber = intval($columnNumber / 26);
        }
        return $letter;
    }

}
