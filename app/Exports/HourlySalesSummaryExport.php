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

class HourlySalesSummaryExport implements FromCollection, WithEvents, WithCustomStartCell, ShouldAutoSize
{
    protected $branchId;
    protected $startDate;
    protected $endDate;
    protected $branch;
    protected $timeSlots = [];
    protected $days = [];
    protected $salesByHour = [];
    protected $daySummary = [];
    protected $totals = [];
    protected $hourlyTotals = [];

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
        
        $this->totals = [
            'transactions' => 0,
            'gross_sales' => 0,
            'net_sales' => 0,
            'discounts' => 0,
            'vat_sales' => 0,
            'vat_exempt_sales' => 0,
            'vat_amount' => 0,
            'cash_sales' => 0,
            'card_sales' => 0,
            'mobile_sales' => 0,
            'ar_sales' => 0,
            'online_sales' => 0,
            'unit_cost' => 0,
            'service_charge' => 0,
            'gross_profit' => 0,
            'gross_profit_percentage' => 0
        ];
        
        $this->hourlyTotals = array_fill(0, 24, 0);
    }

    public function collection()
    {
        // Get hourly sales data
        $hourlySalesQuery = "
            SELECT 
                DATE(transactions.treg) as sale_date,
                HOUR(transactions.treg) as sale_hour,
                SUM(transactions.gross_sales) as total_sales
            FROM transactional_db.transactions
            WHERE transactions.is_complete = TRUE
                AND transactions.branch_id = {$this->branchId}
                AND transactions.is_void = FALSE
                AND transactions.is_back_out = FALSE
                AND transactions.treg BETWEEN '{$this->startDate} 00:00:00' AND '{$this->endDate} 23:59:59'
            GROUP BY DATE(transactions.treg), HOUR(transactions.treg)
            ORDER BY sale_date, sale_hour
        ";

        $salesData = collect(DB::select($hourlySalesQuery));
        
        // Get daily summary data
        $daySummaryQuery = "
            SELECT 
                DATE(t.treg) as sale_date,
                COUNT(*) as transactions,
                SUM(t.gross_sales) as gross_sales,
                SUM(t.net_sales) as net_sales,
                SUM(t.discount_amount) as discounts,
                SUM(t.vatable_sales) as vat_sales,
                SUM(t.vat_exempt_sales) as vat_exempt_sales,
                SUM(t.vat_amount) as vat_amount,
                SUM(CASE WHEN p.payment_type = 'cash' THEN p.amount ELSE 0 END) as cash_sales,
                SUM(CASE WHEN p.payment_type = 'card' THEN p.amount ELSE 0 END) as card_sales,
                SUM(CASE WHEN p.payment_type = 'mobile' THEN p.amount ELSE 0 END) as mobile_sales,
                SUM(CASE WHEN p.payment_type = 'ar' THEN p.amount ELSE 0 END) as ar_sales,
                SUM(CASE WHEN p.payment_type = 'online' THEN p.amount ELSE 0 END) as online_sales,
                SUM(t.unit_cost) as unit_cost,
                SUM(t.service_charge) as service_charge,
                SUM(t.net_sales - t.unit_cost) as gross_profit,
                CASE 
                    WHEN SUM(t.net_sales) > 0 THEN (SUM(t.net_sales - t.unit_cost) / SUM(t.net_sales)) * 100
                    ELSE 0
                END as gross_profit_percentage
            FROM transactional_db.transactions t
            LEFT JOIN transactional_db.payments p ON p.transaction_id = t.id
            WHERE t.is_complete = TRUE
                AND t.branch_id = {$this->branchId}
                AND t.is_void = FALSE
                AND t.is_back_out = FALSE
                AND t.treg BETWEEN '{$this->startDate} 00:00:00' AND '{$this->endDate} 23:59:59'
            GROUP BY DATE(t.treg)
            ORDER BY sale_date
        ";
        
        $summaryData = collect(DB::select($daySummaryQuery));
        
        // Organize data by date and hour
        foreach ($salesData as $sale) {
            $this->salesByHour[$sale->sale_date][intval($sale->sale_hour)] = $sale->total_sales;
            $this->hourlyTotals[intval($sale->sale_hour)] += $sale->total_sales;
        }
        
        foreach ($summaryData as $summary) {
            $this->daySummary[$summary->sale_date] = [
                'transactions' => $summary->transactions,
                'gross_sales' => $summary->gross_sales,
                'net_sales' => $summary->net_sales,
                'discounts' => $summary->discounts,
                'vat_sales' => $summary->vat_sales,
                'vat_exempt_sales' => $summary->vat_exempt_sales,
                'vat_amount' => $summary->vat_amount,
                'cash_sales' => $summary->cash_sales,
                'card_sales' => $summary->card_sales,
                'mobile_sales' => $summary->mobile_sales,
                'ar_sales' => $summary->ar_sales,
                'online_sales' => $summary->online_sales,
                'unit_cost' => $summary->unit_cost,
                'service_charge' => $summary->service_charge,
                'gross_profit' => $summary->gross_profit,
                'gross_profit_percentage' => $summary->gross_profit_percentage
            ];
            
            // Calculate totals
            $this->totals['transactions'] += $summary->transactions;
            $this->totals['gross_sales'] += $summary->gross_sales;
            $this->totals['net_sales'] += $summary->net_sales;
            $this->totals['discounts'] += $summary->discounts;
            $this->totals['vat_sales'] += $summary->vat_sales;
            $this->totals['vat_exempt_sales'] += $summary->vat_exempt_sales;
            $this->totals['vat_amount'] += $summary->vat_amount;
            $this->totals['cash_sales'] += $summary->cash_sales;
            $this->totals['card_sales'] += $summary->card_sales;
            $this->totals['mobile_sales'] += $summary->mobile_sales;
            $this->totals['ar_sales'] += $summary->ar_sales;
            $this->totals['online_sales'] += $summary->online_sales;
            $this->totals['unit_cost'] += $summary->unit_cost;
            $this->totals['service_charge'] += $summary->service_charge;
            $this->totals['gross_profit'] += $summary->gross_profit;
        }
        
        // Calculate overall gross profit percentage
        if ($this->totals['net_sales'] > 0) {
            $this->totals['gross_profit_percentage'] = ($this->totals['gross_profit'] / $this->totals['net_sales']) * 100;
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
                
                // Add header with company information
                $sheet->setCellValue('G1', $this->branch->company->name);
                $sheet->setCellValue('G2', $this->branch->name);
                $sheet->setCellValue('G3', $this->branch->address);
                
                // Add report title and date
                $sheet->setCellValue('A5', 'Hourly Sales Summary Report');
                $sheet->setCellValue('A6', 'Date range: ' . Carbon::parse($this->startDate)->format('m/d/Y') . ' - ' . Carbon::parse($this->endDate)->format('m/d/Y'));
                $sheet->setCellValue('A7', 'Date generated: ' . Carbon::now()->format('m/d/Y H:i:s'));
                
                // Set header styles
                $sheet->getStyle('G1:G3')->getFont()->setBold(true);
                $sheet->getStyle('G1')->getFont()->setSize(14);
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('G1:G3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Set header row for data table
                $colHeaders = ['Date', 'Days', 'No. of Transactions', 'Gross Sales', 'Net Sales', 
                    'Discounts Amount', 'VAT Sales', 'VAT Exempts Sales', 'VAT Amount', 
                    'Cash Sales', 'Card Sales', 'Mobile Sales', 'AR Sales', 'Online Sales', 
                    'Unit Cost', 'Service Charge', 'Gross Profit', 'Gross Profit %'];
                
                // Add time slots to headers
                foreach ($this->timeSlots as $slot) {
                    $colHeaders[] = $slot;
                }
                
                // Set column headers
                $currentColumn = 'A';
                foreach ($colHeaders as $header) {
                    $sheet->setCellValue($currentColumn . '8', $header);
                    $currentColumn++;
                }
                
                // Style the header row
                $lastCol = 'R'; // Column for Gross Profit %
                $sheet->getStyle("A8:{$lastCol}8")->getFont()->setBold(true);
                $sheet->getStyle("A8:{$lastCol}8")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $sheet->getStyle("A8:{$lastCol}8")->getFill()->getStartColor()->setRGB('C9D8FD');
                
                // Add data rows
                $row = 9;
                foreach ($this->days as $day) {
                    $date = $day['date'];
                    $sheet->setCellValue('A' . $row, $day['formatted_date']);
                    $sheet->setCellValue('B' . $row, $day['day_name']);
                    
                    // Add summary data if available
                    if (isset($this->daySummary[$date])) {
                        $summary = $this->daySummary[$date];
                        $sheet->setCellValue('C' . $row, $summary['transactions']);
                        $sheet->setCellValue('D' . $row, $summary['gross_sales']);
                        $sheet->setCellValue('E' . $row, $summary['net_sales']);
                        $sheet->setCellValue('F' . $row, $summary['discounts']);
                        $sheet->setCellValue('G' . $row, $summary['vat_sales']);
                        $sheet->setCellValue('H' . $row, $summary['vat_exempt_sales']);
                        $sheet->setCellValue('I' . $row, $summary['vat_amount']);
                        $sheet->setCellValue('J' . $row, $summary['cash_sales']);
                        $sheet->setCellValue('K' . $row, $summary['card_sales']);
                        $sheet->setCellValue('L' . $row, $summary['mobile_sales']);
                        $sheet->setCellValue('M' . $row, $summary['ar_sales']);
                        $sheet->setCellValue('N' . $row, $summary['online_sales']);
                        $sheet->setCellValue('O' . $row, $summary['unit_cost']);
                        $sheet->setCellValue('P' . $row, $summary['service_charge']);
                        $sheet->setCellValue('Q' . $row, $summary['gross_profit']);
                        $sheet->setCellValue('R' . $row, $summary['gross_profit_percentage'] . '%');
                    } else {
                        // Fill with zeros if no data
                        for ($col = 'C'; $col <= 'Q'; $col++) {
                            $sheet->setCellValue($col . $row, 0);
                        }
                        $sheet->setCellValue('R' . $row, '0%');
                    }
                    
                    // Add hourly sales
                    $currentCol = 'S';
                    for ($hour = 0; $hour < 24; $hour++) {
                        if (isset($this->salesByHour[$date][$hour]) && $this->salesByHour[$date][$hour] > 0) {
                            $sheet->setCellValue($currentCol . $row, $this->salesByHour[$date][$hour]);
                        } else {
                            $sheet->setCellValue($currentCol . $row, '');
                        }
                        $currentCol++;
                    }
                    
                    $row++;
                }
                
                // Add totals row
                $sheet->setCellValue('A' . $row, 'Total');
                $sheet->setCellValue('B' . $row, '');
                $sheet->setCellValue('C' . $row, $this->totals['transactions']);
                $sheet->setCellValue('D' . $row, $this->totals['gross_sales']);
                $sheet->setCellValue('E' . $row, $this->totals['net_sales']);
                $sheet->setCellValue('F' . $row, $this->totals['discounts']);
                $sheet->setCellValue('G' . $row, $this->totals['vat_sales']);
                $sheet->setCellValue('H' . $row, $this->totals['vat_exempt_sales']);
                $sheet->setCellValue('I' . $row, $this->totals['vat_amount']);
                $sheet->setCellValue('J' . $row, $this->totals['cash_sales']);
                $sheet->setCellValue('K' . $row, $this->totals['card_sales']);
                $sheet->setCellValue('L' . $row, $this->totals['mobile_sales']);
                $sheet->setCellValue('M' . $row, $this->totals['ar_sales']);
                $sheet->setCellValue('N' . $row, $this->totals['online_sales']);
                $sheet->setCellValue('O' . $row, $this->totals['unit_cost']);
                $sheet->setCellValue('P' . $row, $this->totals['service_charge']);
                $sheet->setCellValue('Q' . $row, $this->totals['gross_profit']);
                $sheet->setCellValue('R' . $row, $this->totals['gross_profit_percentage'] . '%');
                
                // Add hourly totals
                $currentCol = 'S';
                for ($hour = 0; $hour < 24; $hour++) {
                    if ($this->hourlyTotals[$hour] > 0) {
                        $sheet->setCellValue($currentCol . $row, $this->hourlyTotals[$hour]);
                    } else {
                        $sheet->setCellValue($currentCol . $row, '');
                    }
                    $currentCol++;
                }
                
                // Style the totals row
                $sheet->getStyle("A{$row}:R{$row}")->getFont()->setBold(true);
                
                // Format currency cells
                $moneyColumns = ['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q'];
                foreach ($moneyColumns as $col) {
                    $sheet->getStyle("{$col}9:{$col}{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
                }
                
                // Format percentage cells
                $sheet->getStyle("R9:R{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
                
                // Format transaction count cells
                $sheet->getStyle("C9:C{$row}")->getNumberFormat()->setFormatCode('#,##0');
                
                // Add formulas explanation
                $formulaRow = $row + 2;
                $sheet->setCellValue('A' . $formulaRow, 'Formulas:');
                $sheet->getStyle('A' . $formulaRow)->getFont()->setBold(true);
                
                $sheet->setCellValue('A' . ($formulaRow + 1), 'Gross Sales = VATable Sales + VAT Exempt Sales + Zero Rated Sales + VAT + SC/PWD Discount');
                $sheet->setCellValue('A' . ($formulaRow + 2), 'Net Sales = Gross Sales - VAT - SC/PWD Discount');
                $sheet->setCellValue('A' . ($formulaRow + 3), 'Gross Profit = Net Sales - Unit Cost');
                $sheet->setCellValue('A' . ($formulaRow + 4), 'Gross Profit % = (Gross Profit / Net Sales) * 100');
                
                // Border all cells in the data section
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ];
                $sheet->getStyle('A8:' . $currentCol . $row)->applyFromArray($styleArray);
            },
        ];
    }
}
