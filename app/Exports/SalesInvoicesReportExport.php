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
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use App\Models\Transaction;
use App\Models\Branch;

class SalesInvoicesReportExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents, ShouldAutoSize, WithColumnFormatting
{
    protected $startDate;
    protected $endDate;
    protected $branchId;

    public function __construct($branchId, $startDate, $endDate)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

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
        $transactions = Transaction::where('branch_id', $this->branchId)
            ->where('is_complete', true)
            ->whereBetween('completed_at', [$this->startDate, $this->endDate])
            ->get();

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
            'Date', //done
            'Machine No', //done
            'SI No.', //done
            'Table No.', //done
            'Customer Count', //done
            'Cashier Details', //done
            'Shift', //done
            'Gross Sales', //done
            'Net Sales', //done
            'VAT Sales', //done
            'VAT Amount', //done
            'VAT Exempt', //done
            'VAT Expense', //done
            'Discount', //done
            'Type Of Payment', //done
            'Void', //done
            'Quantity', //done
            'Amount Paid', //done
            'Service Charge', //done
            'Transaction Type', //done
            'Change', //done
            'Approver', //done
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'P' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'Q' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'R' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'T' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function map($transaction): array
    {
        $paymentTypeNames = $transaction->payments->pluck('payment_type_name');

        return [
            $transaction->completed_at,
            $transaction->machine->machine_number,
            $transaction->receipt_number,
            '',
            1,
            $transaction->cashier_name,
            $transaction->shift_number,
            $transaction->gross_sales,
            $transaction->net_sales - $transaction->vat_amount,
            $transaction->vatable_sales ?: 0,
            $transaction->vat_amount ?: 0,
            $transaction->vat_exempt_sales ?: 0,
            $transaction->vat_expense ?: 0,
            $transaction->discount_amount ?: 0,
            $paymentTypeNames->join(', '),
            $transaction->is_void ? 'Yes' : 'No',
            $transaction->nonVoiditems->sum('qty'),
            $transaction->tender_amount ?: 0,
            $transaction->service_charge ?: 0,
            $transaction->type,
            $transaction->change ?: 0,
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
                $event->sheet->setCellValue('A6', 'Date generated: ' . now()->format('Y-m-d H:i:s'));

                $event->sheet->mergeCells('A7:Q7');
                $event->sheet->setCellValue('A7', 'Created by: ' . auth()->user()->name);

                $totalRows = $event->sheet->getHighestRow();

                $totalColumns = [
                    'H',
                    'I',
                    'J',
                    'K',
                    'L',
                    'M',
                    'P',
                    'Q',
                    'R',
                    'T',
                ];

                $event->sheet->setCellValue('A' . ($totalRows + 1), 'Total');

                foreach ($totalColumns as $column) {
                    $event->sheet->setCellValue($column . ($totalRows + 1), '=SUM('.$column.'10:' . $column . $totalRows . ')');
                    $event->sheet->getStyle($column . ($totalRows + 1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                }
            },
        ];
    }
}
