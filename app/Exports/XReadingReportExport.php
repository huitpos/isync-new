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

class XReadingReportExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents, ShouldAutoSize
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
        $cutoffs = CutOff::where('branch_id', $this->branchId)
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
        return [
            'Machine #', //done
            'Shift No', //done
            'X-reading #', //done
            'Beginning OR #', //done
            'Ending OR #', //done
            'Cut Off Date', //done
            'Gross Sales', //done
            'Net Sales', //done
            'Vatable Sales', //done
            'Vat Exempt Sales', //done
            'Vat Amount', //done
            'Vat Discount', //pending
            'Cash', //pending
            'Credit Card', //pending
            'Mobile', //pending
            'AR', //pending
            'Online', //pending
            'Service Charge', //done
            'Short/Over', //done
            'Void', //done
            'Senior Discount Count',
            'Senior Discount Amount',
            'PWD Discount Count',
            'PWD Discount Amount',
            'Other Discount Count',
            'Other Discount Amount',
            'Cashier Name'
        ];
    }

    public function map($cutoff): array
    {
        $data =  [
            $cutoff->machine->machine_number,
            $cutoff->shift_number,
            $cutoff->id,
            $cutoff->beginning_or,
            $cutoff->ending_or,
            $cutoff->treg,
            $cutoff->gross_sales,
            $cutoff->net_sales,
            $cutoff->vatable_sales,
            $cutoff->vat_exempt_sales,
            $cutoff->vat_amount,
            '0.00',
            '0.00',
            '0.00',
            '0.00',
            '0.00',
            '0.00',
            $cutoff->total_service_charge,
            $cutoff->total_short_over,
            $cutoff->void_amount,
        ];

        dd($data);

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
                $event->sheet->setCellValue('A4', 'X Reading Report');
                $event->sheet->getStyle('A4')->getFont()->setBold(true);

                $event->sheet->mergeCells('A5:Q5');
                $event->sheet->setCellValue('A5', 'Date range: mm/yyyy - mm/yyyy');

                $event->sheet->mergeCells('A6:Q6');
                $event->sheet->setCellValue('A6', 'Date generated:');

                $event->sheet->mergeCells('A7:Q7');
                $event->sheet->setCellValue('A7', 'Created by:');

                $totalRows = $event->sheet->getHighestRow();

                $totalColumns = [
                    'D',
                    'E',
                    'F',
                    'G',
                    'H',
                ];

                $event->sheet->setCellValue('B' . ($totalRows + 1), 'Total');

                foreach ($totalColumns as $column) {
                    $event->sheet->setCellValue($column . ($totalRows + 1), '=SUM('.$column.'10:' . $column . $totalRows . ')');
                }
            },
        ];
    }

}
