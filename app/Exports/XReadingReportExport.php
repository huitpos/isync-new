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

class XReadingReportExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $branchId;
    protected $paymentTypes;
    protected $discountTypes;
    protected $numberOfColumns;
    protected $headers;

    public function __construct($branchId, $startDate, $endDate)
    {
        $this->branchId = $branchId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        $branch = Branch::find($branchId);
        $this->paymentTypes = PaymentType::where('company_id', $branch->company_id)
            ->orWhere('company_id', null)
            ->orderBy('id')
            ->get();

        $this->discountTypes = DiscountType::where('company_id', $branch->company_id)
            ->orWhere('company_id', null)
            ->orderBy('id')
            ->get();

        $headers =  [
            'Machine #',
            'Shift No',
            'X-reading #',
            'Beginning SI #',
            'Ending SI #',
            'Cut Off Date',
            'Gross Sales',
            'Net Sales',
            'Vatable Sales',
            'Vat Exempt Sales',
            'Vat Amount',
            'Vat Discount',
        ];

        $paymentTypes = [];
        foreach ($this->paymentTypes as $paymentType) {
            $paymentTypes[] = $paymentType->name;
        }

        $headers =  array_merge($headers, $paymentTypes, [
            'Service Charge',
            'Short/Over',
            'Void',
        ]);

        $discountTypes = [];
        foreach ($this->discountTypes as $discountType) {
            $discountTypes[] = $discountType->name;
        }

        $headers = array_merge($headers, $discountTypes, [
            'Cashier Name'
        ]);

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
        $cutoffs = CutOff::where('branch_id', $this->branchId)
            ->whereBetween('treg', [$this->startDate, $this->endDate])
            ->get();

        return new Collection($cutoffs);
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

    public function map($cutoff): array
    {
        $transactions = Transaction::where([
            'cut_off_id' => $cutoff->cut_off_id,
            'branch_id' => $cutoff->branch_id,
        ])
        ->get();

        $transactionIds = $transactions->pluck('transaction_id')->unique()->toArray();

        $data = [
            $cutoff->machine->machine_number, //Machine #
            $cutoff->shift_number, //Shift No
            $cutoff->reading_number, //X-reading #
            $cutoff->beginning_or, //Beginning OR #
            $cutoff->ending_or, //Ending OR #
            $cutoff->treg, //Cut Off Date
            number_format($cutoff->gross_sales ?: 0, 2), //Gross Sales
            number_format($cutoff->net_sales - $cutoff->vat_amount, 2), //Net Sales
            number_format($cutoff->vatable_sales ?: 0, 2), //Vatable Sales
            number_format($cutoff->vat_exempt_sales ?: 0, 2), //Vat Exempt Sales
            number_format($cutoff->vat_amount ?: 0, 2), //Vat Amount,
            number_format($cutoff->vat_expense ?: 0, 2) //Vat Discount
        ];

        foreach($this->paymentTypes as $paymentType) {
            $payments = Payment::where([
                'cut_off_id' => $cutoff->cut_off_id,
                'payment_type_id' => $paymentType->id,
                'branch_id' => $cutoff->branch_id,
            ])
            ->get();

            $data[] = number_format($payments->sum('amount') ?: 0, 2);
        }

        $data =  array_merge($data, [
            number_format($cutoff->total_service_charge ?: 0, 2), //Service Charge
            number_format($cutoff->total_short_over ?: 0, 2), //Short/Over
            number_format($cutoff->void_amount ?: 0, 2) // void
        ]);

        foreach ($this->discountTypes as $discountType) {
            $discounts = Discount::where([
                'discount_type_id' => $discountType->id,
                'branch_id' => $cutoff->branch_id,
            ])
            ->whereIn('transaction_id', $transactionIds)
            ->get();


            $data[] = number_format($discounts->sum('discount_amount') ?: 0, 2);
        }

        $data[] = $cutoff->cashier_name;

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

                $totalColumns = [
                    'D',
                    'E',
                    'F',
                    'G',
                    'H',
                ];

                // $event->sheet->setCellValue('B' . ($totalRows + 1), 'Total');

                foreach ($totalColumns as $column) {
                    // $event->sheet->setCellValue($column . ($totalRows + 1), '=SUM('.$column.'10:' . $column . $totalRows . ')');
                }
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
