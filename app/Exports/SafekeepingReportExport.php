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
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

use App\Models\Branch;

class SafekeepingReportExport implements FromCollection, WithEvents, WithCustomStartCell, ShouldAutoSize, WithColumnFormatting
{
    protected $branchId;
    protected $startDate;
    protected $endDate;
    protected $branch;
    protected $data;

    public function __construct($branchId, $startDate, $endDate)
    {
        $this->branchId = $branchId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->branch = Branch::find($branchId);
    }

    public function collection()
    {
        $safekeepingQuery = "
            SELECT 
                safekeepings.created_at as date,
                safekeepings.pos_machine_id as machine_number,
                safekeepings.amount as amount,
                safekeepings.cashier_name as cashier_name
            FROM transactional_db.safekeepings
            WHERE safekeepings.branch_id = {$this->branchId}
                AND safekeepings.created_at BETWEEN '{$this->startDate} 00:00:00' AND '{$this->endDate} 23:59:59'
            ORDER BY safekeepings.created_at ASC
        ";

        $this->data = collect(DB::select($safekeepingQuery));
        return $this->data;
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                
                // Set report title
                $event->sheet->mergeCells('A1:D1');
                $event->sheet->setCellValue('A1', $this->branch->company->company_name);
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set branch info
                $event->sheet->mergeCells('A2:D2');
                $event->sheet->setCellValue('A2', $this->branch->name);
                $event->sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set address
                $event->sheet->mergeCells('A3:D3');
                $address = $this->branch->unit_floor_number . ', ' . $this->branch->street . ', ' . $this->branch->city->name . ', ' . $this->branch->province->name . ', ' . $this->branch->region->name;
                $event->sheet->setCellValue('A3', $address);
                $event->sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set report name
                $event->sheet->mergeCells('A4:D4');
                $event->sheet->setCellValue('A4', 'Safekeeping Report');
                $event->sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);
                $event->sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set date range
                $event->sheet->mergeCells('A5:D5');
                $dateRange = Carbon::parse($this->startDate)->format('M d, Y') . ' - ' . Carbon::parse($this->endDate)->format('M d, Y');
                $event->sheet->setCellValue('A5', 'Date range: ' . $dateRange);
                $event->sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Create date generated and created by lines
                $event->sheet->mergeCells('A6:D6');
                $event->sheet->setCellValue('A6', 'Date generated: ' . Carbon::now()->format('M d, Y'));
                
                $event->sheet->mergeCells('A7:D7');
                $event->sheet->setCellValue('A7', 'Created by: ');
                
                // Set header row
                $sheet->setCellValue('A8', 'Date');
                $sheet->setCellValue('B8', 'Machine #');
                $sheet->setCellValue('C8', 'Amount');
                $sheet->setCellValue('D8', 'Cashier name');
                
                // Style header row
                $headerRange = 'A8:D8';
                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($headerRange)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('CCCCCC');
                
                // Set data rows
                $rowIndex = 9;
                foreach ($this->data as $item) {
                    $sheet->setCellValue('A' . $rowIndex, Carbon::parse($item->date)->format('Y-m-d H:i:s'));
                    $sheet->setCellValue('B' . $rowIndex, $item->machine_number);
                    $sheet->setCellValue('C' . $rowIndex, $item->amount);
                    $sheet->setCellValue('D' . $rowIndex, $item->cashier_name);
                    
                    $rowIndex++;
                }
                
                // Add total row
                $sheet->setCellValue('A' . $rowIndex, 'Total');
                $sheet->mergeCells('A' . $rowIndex . ':B' . $rowIndex);
                $sheet->getStyle('A' . $rowIndex)->getFont()->setBold(true);
                $sheet->getStyle('A' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                
                // Set total formula
                $sheet->setCellValue('C' . $rowIndex, '=SUM(C9:C' . ($rowIndex-1) . ')');
                $sheet->getStyle('C' . $rowIndex)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
                $sheet->getStyle('C' . $rowIndex)->getFont()->setBold(true);
                
                // Apply borders to all cells
                $dataRange = 'A8:D' . $rowIndex;
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                // Auto-size columns
                foreach (range('A', 'D') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
