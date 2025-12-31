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
use Carbon\CarbonPeriod;

use App\Models\Branch;

class HourlySalesReportExport implements FromCollection, WithEvents, WithCustomStartCell, ShouldAutoSize
{
    protected $branchId;
    protected $startDate;
    protected $endDate;
    protected $branch;
    protected $timeSlots = [];
    protected $days = [];
    protected $salesByHour = [];

    public function __construct($branchId, $startDate, $endDate)
    {
        $this->branchId = $branchId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->branch = Branch::find($branchId);
        
        // Define time slots (hourly intervals)
        $this->timeSlots = [
            '00:00-01:00', '01:00-02:00', '02:00-3:00', '3:00-4:00', '4:00-5:00',
            '5:00-6:00', '6:00-7:00', '7:00-08:00', '8:00-9:00', '9:00-10:00',
            '10:00-11:00', '11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00',
            '15:00-16:00', '16:00-17:00', '17:00-18:00', '18:00-19:00', '19:00-20:00',
            '20:00-21:00', '21:00-22:00', '22:00-23:00', '23:00-24:00'
        ];
        
        // Create date range
        $period = CarbonPeriod::create($startDate, $endDate);
        
        foreach ($period as $date) {
            $this->days[] = [
                'date' => $date->format('Y-m-d'),
                'formatted_date' => $date->format('M j, Y'),
                'day_name' => $date->format('l')
            ];
        }
    }

    public function collection()
    {
        // Get hourly sales data
        $hourlySalesQuery = "
            SELECT 
                DATE(transactions.treg) as sale_date,
                HOUR(transactions.treg) as sale_hour,
                SUM(transactions.total_amount) as total_sales
            FROM transactional_db.transactions
            WHERE transactions.is_complete = TRUE
                AND transactions.branch_id = {$this->branchId}
                AND transactions.is_void = FALSE
                AND transactions.is_back_out = FALSE
                AND transactions.treg BETWEEN '{$this->startDate}' AND '{$this->endDate} 23:59:59'
            GROUP BY DATE(transactions.treg), HOUR(transactions.treg)
            ORDER BY sale_date, sale_hour
        ";

        $salesData = collect(DB::select($hourlySalesQuery));
        
        // Organize data by date and hour
        foreach ($salesData as $sale) {
            $this->salesByHour[$sale->sale_date][intval($sale->sale_hour)] = $sale->total_sales;
        }
        
        // Create a simple collection - we will actually format the data in the registerEvents method
        return collect($this->days);
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                
                // Set report title
                $event->sheet->mergeCells('A1:X1');
                $event->sheet->setCellValue('A1', $this->branch->company->company_name);
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set branch info
                $event->sheet->mergeCells('A2:X2');
                $event->sheet->setCellValue('A2', $this->branch->name);
                $event->sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set address
                $event->sheet->mergeCells('A3:X3');
                $address = $this->branch->unit_floor_number . ', ' . $this->branch->street . ', ' . $this->branch->city->name . ', ' . $this->branch->province->name . ', ' . $this->branch->region->name;
                $event->sheet->setCellValue('A3', $address);
                $event->sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set report name
                $event->sheet->mergeCells('A4:X4');
                $event->sheet->setCellValue('A4', 'Hourly Sales Report');
                $event->sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);
                $event->sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set date range
                $event->sheet->mergeCells('A5:X5');
                $dateRange = Carbon::parse($this->startDate)->format('M d, Y') . ' - ' . Carbon::parse($this->endDate)->format('M d, Y');
                $event->sheet->setCellValue('A5', 'Date range: ' . $dateRange);
                $event->sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Create date generated and created by lines
                $event->sheet->mergeCells('A6:X6');
                $event->sheet->setCellValue('A6', 'Date generated: ' . Carbon::now()->format('M d, Y'));
                
                $event->sheet->mergeCells('A7:X7');
                $event->sheet->setCellValue('A7', 'Created by: ');
                
                // Set header row
                $sheet->setCellValue('A8', 'Date');
                $sheet->setCellValue('B8', 'Days');
                
                // Set time slot headers
                foreach ($this->timeSlots as $index => $timeSlot) {
                    $colLetter = $this->getColumnLetter($index + 2); // +2 because A and B are used for Date and Days
                    $sheet->setCellValue($colLetter . '8', $timeSlot);
                }
                
                // Style header row
                $headerRange = 'A8:' . $this->getColumnLetter(count($this->timeSlots) + 1) . '8';
                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($headerRange)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('CCCCCC');
                
                // Set data rows
                $rowIndex = 9;
                foreach ($this->days as $day) {
                    $sheet->setCellValue('A' . $rowIndex, $day['formatted_date']);
                    $sheet->setCellValue('B' . $rowIndex, $day['day_name']);
                    
                    // Fill in sales data for each hour
                    for ($hour = 0; $hour < 24; $hour++) {
                        $colLetter = $this->getColumnLetter($hour + 2); // +2 because A and B are used for Date and Days
                        $saleAmount = $this->salesByHour[$day['date']][$hour] ?? 0;
                        $sheet->setCellValue($colLetter . $rowIndex, $saleAmount > 0 ? $saleAmount : '');
                        
                        // Format as currency
                        if ($saleAmount > 0) {
                            $sheet->getStyle($colLetter . $rowIndex)->getNumberFormat()
                                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                        }
                    }
                    
                    $rowIndex++;
                }
                
                // Apply borders to all cells
                $dataRange = 'A8:' . $this->getColumnLetter(count($this->timeSlots) + 1) . ($rowIndex - 1);
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                // Auto-size columns
                foreach (range('A', $this->getColumnLetter(count($this->timeSlots) + 1)) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
    
    /**
     * Get Excel column letter from index (0-based)
     */
    private function getColumnLetter($columnIndex) {
        // For columns beyond Z
        if ($columnIndex >= 26) {
            return chr(65 + floor($columnIndex / 26) - 1) . chr(65 + ($columnIndex % 26));
        }
        // For columns A-Z
        return chr(65 + $columnIndex);
    }
}
