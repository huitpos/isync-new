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

use App\Models\Branch;
use App\Models\PaymentType;
use App\Models\DiscountType;
use App\Models\Payment;
use App\Models\Discount;
use App\Models\EndOfDay;
use App\Models\Transaction;
use App\Models\CutOff;
use Faker\Core\Number;

class BirSeniorSalesReportExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $branchId;
    protected $paymentTypes;
    protected $discountTypes;
    protected $numberOfColumns;
    protected $headers;
    protected $machine;

    public function __construct($branchId, $startDate, $endDate)
    {
        $this->branchId = $branchId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        $branch = Branch::find($branchId);
        
        $this->machine = $branch->machines->first();

        $headers =  [
            'Date',
            'Beginning/nSI/OR No.',
            'Ending SI/OR No.',
            'Grand Accum. Sales Ending Balance',
            'Grand Accum. Beg.Balance',
            'Sales Issued w/ Manual SI/OR (per RR 16-2018)',
            'Gross Sales for the Day',
            'VATable Sales',
            'VAT Amount',
            'VAT-Exempt Sales',
            'Zero-Rated Sales',
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
        $discounts = Discount::where('discount_type_id', 4)
            // ->where('branch_id', $branchId)
            // ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        return new Collection($discounts);
    }

    public function headings(): array
    {
        return [
            [
                'Date',
                'Name of Senior Citizen (SC)',
                'OSCA ID No./ SC ID No.',
                'SC TIN',
                'SI/OR Number',
                'Sales (inclusive of VAT)',
                'VAT Amount',
                'VAT Exempt Sales',
                'Discount',
                'Net Sales',
            ],
        ];
    }

    public function map($discounts): array
    {
        $name = '';
        $scId = '';
        $tin = '';

        $otherInfos = $discounts->otherInfo;
        foreach ($otherInfos as $otherInfo) {
            if ($otherInfo->name == 'NAME OF SENIOR:') {
                $name = $otherInfo->value;
            }

            if ($otherInfo->name == 'OSCA/SC ID NO.:') {
                $scId = $otherInfo->value;
            }

            if ($otherInfo->name == 'TIN:') {
                $tin = $otherInfo->value;
            }
        }

        $transaction = $discounts->transaction;

        $data = [
            $discounts->treg,
            $name,
            $scId,
            $tin,
            $transaction->receipt_number,
            $transaction->gross_sales,
            $transaction->vat_amount,
            $transaction->vat_exempt_sales,
            '5%',
            $transaction->net_sales,
        ];

        return $data;
    }

    /**
     * Define the start cell for the export.
     *
     * @return string
     */
    public function startCell(): string
    {
        return 'A14'; // Data will start from cell A2
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

                $event->sheet->mergeCells('A13:'.$columnLetter.'13');
                $event->sheet->setCellValue('A13', 'Senior Citizen Sales Book/Report');
                $event->sheet->getStyle('A13')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A13')->getFont()->setBold(true);

                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A1')->getFont()->setBold(true);

                $event->sheet->mergeCells('A2:'.$columnLetter.'2');
                $event->sheet->setCellValue('A2', $branch->unit_floor_number . ', ' . $branch->street . ', ' . $branch->city->name . ', ' . $branch->province->name . ', ' . $branch->region->name);

                $event->sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->mergeCells('A3:'.$columnLetter.'3');
                $event->sheet->setCellValue('A3', $this->machine->tin);

                $event->sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->mergeCells('A5:'.$columnLetter.'5');
                $event->sheet->setCellValue('A5', 'iSync POS Version 1.0 November 25, 2024');
                
                $event->sheet->mergeCells('A6:'.$columnLetter.'6');
                $event->sheet->setCellValue('A6', $this->machine->serial_number."   ");

                $event->sheet->mergeCells('A7:'.$columnLetter.'7');
                $event->sheet->setCellValue('A7', $this->machine->min."   ");

                $event->sheet->mergeCells('A8:'.$columnLetter.'8');
                $event->sheet->setCellValue('A8', "Machine 1");

                $event->sheet->mergeCells('A9:'.$columnLetter.'9');
                $event->sheet->setCellValue('A9', now()->format('Y-m-d H:i:s'));

                $event->sheet->mergeCells('A10:'.$columnLetter.'10');
                $event->sheet->setCellValue('A10', auth()->user()->name);
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
