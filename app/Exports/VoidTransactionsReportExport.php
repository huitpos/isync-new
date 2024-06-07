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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use App\Models\Transaction;
use App\Models\Branch;

class VoidTransactionsReportExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $branchId;

    public function __construct($branchId, $startDate, $endDate)
    {
        $this->branchId = $branchId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Return a collection of data for the export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $transactions = Transaction::where([
            'branch_id' => $this->branchId,
            'is_void' => true
        ])->get();

        return new Collection($transactions);
    }

    /**
     * Define the headings for the export.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Void No.', //done
            'Date', //done
            'Sales Invoice #', //done
            'Machine #', //done
            'Discount Amount', //done
            'Gross Sales', //done
            'Net Sales', //done
            'Remarks', //done
            'Cashier', //done
            'Approved By',
        ];
    }

    public function map($transaction): array
    {
        $paymentTypeNames = $transaction->payments->pluck('payment_type_name');

        return [
            $transaction->void_counter,
            $transaction->treg,
            $transaction->receipt_number,
            $transaction->machine->machine_number,
            $transaction->discount_amount ?: '0.00',
            $transaction->gross_sales ?: '0.00',
            $transaction->net_sales ?: '0.00',
            $transaction->void_remarks,
            $transaction->cashier_name,
            ''
        ];
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
        $branch = Branch::find($this->branchId);
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        return [
            AfterSheet::class => function(AfterSheet $event) use($branch, $startDate, $endDate) {
                $event->sheet->mergeCells('A1:Q1');
                $event->sheet->setCellValue('A1', $branch->company->company_name);

                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A1')->getFont()->setBold(true);

                $event->sheet->mergeCells('A2:Q2');
                $event->sheet->setCellValue('A2', $branch->name);

                $event->sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->mergeCells('A3:Q3');
                $event->sheet->setCellValue('A3', $branch->unit_floor_number . ', ' . $branch->street . ', ' . $branch->city->name . ', ' . $branch->province->name . ', ' . $branch->region->name);

                $event->sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->mergeCells('A4:Q4');
                $event->sheet->setCellValue('A4', 'Sales Summary Report');
                $event->sheet->getStyle('A4')->getFont()->setBold(true);

                $event->sheet->mergeCells('A5:Q5');
                $event->sheet->setCellValue('A5', 'Date range: ' . $startDate . ' - ' . $endDate);

                $event->sheet->mergeCells('A6:Q6');
                $event->sheet->setCellValue('A6', 'Date generated: '. now()->format('Y-m-d H:i:s'));

                $event->sheet->mergeCells('A7:Q7');
                $event->sheet->setCellValue('A7', 'Created by: ' . auth()->user()->name);

                $totalRows = $event->sheet->getHighestRow();

                $totalColumns = [];

                foreach ($totalColumns as $column) {
                    $event->sheet->setCellValue($column . ($totalRows + 1), '=SUM('.$column.'10:' . $column . $totalRows . ')');
                }
            },
        ];
    }
}
