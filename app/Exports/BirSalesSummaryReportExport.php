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

class BirSalesSummaryReportExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents, ShouldAutoSize
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
        $endOfDays = EndOfDay::where('branch_id', $this->branchId)
            ->whereBetween('treg', [$this->startDate, $this->endDate])
            ->get();

        return new Collection($endOfDays);
    }

    public function headings(): array
    {
        return [
            [
                'Date',
                'Beginning SI/OR No.',
                'Ending SI/OR No.', 
                
                'Grand Accum. Sales Ending Balance',
                'Grand Accum. Beg. Balance',
                'Sales Issued w/ Manual SI/OR (per RR 16-2018)',
                'Gross Sales for the Day', 
                
                'VATable Sales', 
                'VAT Amount',
                'VAT-Exempt Sales',
                'Zero-Rated Sales',
                'Deductions', '', '', '', '', '','','',

                'Adjustment on VAT', '', '', 'ss', 'sss', 'Total VAT Adjustment',
                'VAT Payable',
                'Net Sales',
                'Sales Overrun /Overflow',
                'Total Income',
                'Reset Counter',
                'Z -Counter',
                'Remarks'
            ],
            [
                '', '', '', '', '', '', '', '', '', '', '',
                'Discount','','','','','Returns',
                'Voids','Total Deductions',
                'Discount', '', '',
                'VAT on Returns',
                'Others',
                'Total VAT Adjustment'
            ],
            [
                '', '', '', '', '', '', '', '', '', '', '',
                'SC', 'PWD', 'NAAC', 'Solo Parent', 'Others',
                '', '', '',
                'SC', 'PWD', 'Others'
            ],
        ];
    }

    public function map($endOfDays): array
    {
        $cutOffs = CutOff::where('branch_id', $endOfDays->branch_id)
            ->where([
                'end_of_day_id' => $endOfDays->end_of_day_id
            ])
            ->get();
        $cutOffIds = $cutOffs->pluck('cut_off_id')->unique()->toArray();

        $sc = Discount::where([
            'discount_type_id' => 4,
            'branch_id' => $endOfDays->branch_id,
            'is_void' => false
        ])
        ->whereIn('cut_off_id', $cutOffIds)
        ->get();

        $pwd = Discount::where([
            'discount_type_id' => 5,
            'branch_id' => $endOfDays->branch_id,
            'is_void' => false
        ])
        ->whereIn('cut_off_id', $cutOffIds)
        ->get();

        $naac = Discount::where([
            'discount_type_id' => 29,
            'branch_id' => $endOfDays->branch_id,
            'is_void' => false
        ])
        ->whereIn('cut_off_id', $cutOffIds)
        ->get();

        $soloParent = Discount::where([
            'discount_type_id' => 11,
            'branch_id' => $endOfDays->branch_id,
            'is_void' => false
        ])
        ->whereIn('cut_off_id', $cutOffIds)
        ->get();

        $otherDiscounts = Discount::where([
            'branch_id' => $endOfDays->branch_id,
            'is_void' => false
        ])
        ->whereNotIn('discount_type_id', [4, 5, 29, 11])
        ->whereIn('cut_off_id', $cutOffIds)
        ->get();

        $resetCounter = intval(explode('-', $endOfDays->ending_or)[0]);

        $data = [
            $endOfDays->treg,
            $endOfDays->beginning_or,
            $endOfDays->ending_or,
            $endOfDays->ending_amount,
            $endOfDays->beginning_amount,
            0,
            $endOfDays->gross_sales,
            $endOfDays->vatable_sales,
            $endOfDays->vat_amount,
            $endOfDays->vat_exempt_sales,
            $endOfDays->total_zero_rated_amount,
            number_format($sc->sum('discount_amount'), 2),
            number_format($pwd->sum('discount_amount'), 2),
            number_format($naac->sum('discount_amount'), 2),
            number_format($soloParent->sum('discount_amount'), 2),
            number_format($otherDiscounts->sum('discount_amount'), 2),
            number_format(0, 2),
            number_format(0, 2),
            number_format(0, 2),
            number_format(0, 2),
            number_format(0, 2),
            number_format(0, 2),
            number_format(0, 2),
            number_format(0, 2),
            number_format(0, 2),
            number_format(0, 2),
            number_format($endOfDays->net_sales - $endOfDays->vat_amount, 2),
            number_format($endOfDays->total_short_over, 2),
            number_format($endOfDays->net_sales - $endOfDays->vat_amount, 2),
            str_pad($resetCounter, 2, '0', STR_PAD_LEFT),
            $endOfDays->reading_number,
            '',
            
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
                $event->sheet->setCellValue('A13', 'BIR SALES SUMMARY REPORT');
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

                $sheet = $event->sheet;
                $sheet->mergeCells('A14:A16');
                $sheet->mergeCells('B14:B16');
                $sheet->mergeCells('C14:C16');
                $sheet->mergeCells('D14:D16');
                $sheet->mergeCells('E14:E16');
                $sheet->mergeCells('F14:F16');
                $sheet->mergeCells('G14:G16');
                $sheet->mergeCells('H14:H16');
                $sheet->mergeCells('I14:I16');
                $sheet->mergeCells('J14:J16');
                $sheet->mergeCells('K14:K16');
                
                // Merge cells for "Deductions" and "Adjustment on VAT" groups
                $sheet->mergeCells('L14:S14');
                $sheet->mergeCells('T14:Y14');
        
                // Merge cells for individual columns under "Deductions"
                $sheet->mergeCells('L15:P15');
                $sheet->mergeCells('T15:V15');

                // Merge cells for individual columns under "Adjustment on VAT"
                $sheet->mergeCells('Q15:Q16');
                $sheet->mergeCells('R15:R16');
                $sheet->mergeCells('S15:S16');
                $sheet->mergeCells('W15:W16');
                $sheet->mergeCells('X15:X16');
                $sheet->mergeCells('Y15:Y16');

                // Merge cells for remaining top-level headers
                $sheet->mergeCells('Z14:Z16');
                $sheet->mergeCells('AA14:AA16');
                $sheet->mergeCells('AB14:AB16');
                $sheet->mergeCells('AC14:AC16');
                $sheet->mergeCells('AD14:AD16');
                $sheet->mergeCells('AE14:AE16');
                $sheet->mergeCells('AF14:AF16');

                // Apply styling
                $sheet->getStyle('A14:AF16')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 10,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Apply background colors (example colors, adjust as needed)
                $sheet->getStyle('A14:C16')->getFill()->applyFromArray([
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'A6A6A6'],
                ]);
                $sheet->getStyle('D14:G16')->getFill()->applyFromArray([
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => '00B0F0'],
                ]);
                $sheet->getStyle('H14:K16')->getFill()->applyFromArray([
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFF00'],
                ]);
                $sheet->getStyle('L14:S16')->getFill()->applyFromArray([
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFC000'],
                ]);
                $sheet->getStyle('T14:Y16')->getFill()->applyFromArray([
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => '92D050'],
                ]);
                $sheet->getStyle('Z14:AF16')->getFill()->applyFromArray([
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'A6A6A6'],
                ]);
                
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
