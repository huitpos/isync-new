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

class SalesTransactionReportExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents, ShouldAutoSize, WithColumnFormatting
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
        $transactions = Transaction::where('branch_id', $this->branchId)
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
            'OR No.', //done
            'Table No.', //done
            'Customer Count', //done
            'Cashier Details', //done
            'Shift', //done
            'Gross Sales', //done
            'Net Sales', //done
            'VAT Sales', //done
            'VAT Amount', //done
            'VAT Exempt', //done
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
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Gross Sales
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Net Sales
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // VAT Sales
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // VAT Amount
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Discount
            'P' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Amount Paid
            'Q' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Service Charge
        ];
    }

    public function map($transaction): array
    {
        $paymentTypeNames = $transaction->payments->pluck('payment_type_name');

        return [
            $transaction->treg,
            $transaction->machine->machine_number,
            $transaction->receipt_number,
            '',
            1,
            $transaction->cashier_name,
            $transaction->shift_number,
            $transaction->gross_sales,
            $transaction->net_sales,
            $transaction->vatable_sales ?: '0.00',
            $transaction->vat_amount ?: '0.00',
            $transaction->vat_exempt_sales ?: '0.00',
            $transaction->discount_amount ?: '0.00',
            $paymentTypeNames->join(', '),
            $transaction->is_void ? 1 : 0,
            $transaction->items->sum('qty'),
            $transaction->tender_amount ?: '0.00',
            $transaction->service_charge ?: '0.00',
            $transaction->type,
            $transaction->change ?: '0.00',
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
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->mergeCells('A1:Q1');
                $event->sheet->setCellValue('A1', 'Huit Enterprises Inc.');

                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A1')->getFont()->setBold(true);

                $event->sheet->mergeCells('A2:Q2');
                $event->sheet->setCellValue('A2', 'Branch Name');

                $event->sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->mergeCells('A3:Q3');
                $event->sheet->setCellValue('A3', 'Address');

                $event->sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->mergeCells('A4:Q4');
                $event->sheet->setCellValue('A4', 'Sales Summary Report');
                $event->sheet->getStyle('A4')->getFont()->setBold(true);

                $event->sheet->mergeCells('A5:Q5');
                $event->sheet->setCellValue('A5', 'Date range: mm/yyyy - mm/yyyy');

                $event->sheet->mergeCells('A6:Q6');
                $event->sheet->setCellValue('A6', 'Date generated:');

                $event->sheet->mergeCells('A7:Q7');
                $event->sheet->setCellValue('A7', 'Created by:');

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
                }
            },
        ];
    }

}