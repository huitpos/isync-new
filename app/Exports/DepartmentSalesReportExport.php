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

use App\Models\Branch;

class DepartmentSalesReportExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents, ShouldAutoSize, WithColumnFormatting
{
    protected $branchId;
    protected $startDate;
    protected $endDate;
    protected $branch;
    protected $totalGrossSales = 0;
    protected $totalDiscountSales = 0;
    protected $totalNetSales = 0;

    public function __construct($branchId, $startDate, $endDate)
    {
        $this->branchId = $branchId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->branch = Branch::find($branchId);
    }

    public function collection()
    {
        // Get department sales data using raw SQL matching the transactional_db structure
        $departmentSalesQuery = "
            SELECT 
                isync.departments.name as department, 
                SUM(transactional_db.orders.qty) as quantity_sold,
                SUM(transactional_db.orders.gross) as gross_sales,
                SUM(CASE WHEN transactional_db.orders.discount_amount > 0 THEN transactional_db.orders.discount_amount ELSE 0 END) as discount_sales,
                SUM(transactional_db.orders.gross - CASE WHEN transactional_db.orders.discount_amount > 0 THEN transactional_db.orders.discount_amount ELSE 0 END) as net_sales
            FROM transactional_db.transactions
            INNER JOIN transactional_db.orders ON transactions.transaction_id = orders.transaction_id
                AND transactions.branch_id = orders.branch_id
                AND transactions.pos_machine_id = orders.pos_machine_id
                AND orders.is_void = FALSE
                AND orders.is_completed = TRUE
                AND orders.is_back_out = FALSE
            INNER JOIN isync.products ON orders.product_id = products.id
            INNER JOIN isync.departments ON products.department_id = departments.id
            WHERE transactions.is_complete = TRUE
                AND transactions.branch_id = {$this->branchId}
                AND transactions.is_void = FALSE
                AND transactions.is_back_out = FALSE
                AND transactions.treg BETWEEN '{$this->startDate}' AND '{$this->endDate}'
            GROUP BY isync.departments.name
            ORDER BY gross_sales DESC
        ";

        $departmentData = collect(DB::select($departmentSalesQuery));

        // Calculate totals
        $this->totalGrossSales = $departmentData->sum('gross_sales');
        $this->totalDiscountSales = $departmentData->sum('discount_sales');
        $this->totalNetSales = $departmentData->sum('net_sales');
        
        // Add percentage to each department
        $departmentData->transform(function($item) {
            $item->percentage = $this->totalNetSales > 0 ? round(($item->net_sales / $this->totalNetSales) * 100) : 0;
            return $item;
        });

        // Add a total row
        $departmentData->push((object)[
            'department' => 'Total',
            'quantity_sold' => $departmentData->sum('quantity_sold'),
            'gross_sales' => $this->totalGrossSales,
            'discount_sales' => $this->totalDiscountSales,
            'net_sales' => $this->totalNetSales,
            'percentage' => 100
        ]);

        return $departmentData;
    }

    public function headings(): array
    {
        return [
            'Department',
            'Quantity Sold',
            'Gross Sales',
            'Discount Sales',
            'Net Sales',
            'Department Percentage Sales'
        ];
    }

    public function map($row): array
    {
        return [
            $row->department,
            $row->quantity_sold,
            $row->gross_sales,
            $row->discount_sales,
            $row->net_sales,
            $row->percentage . '%'
        ];
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Set report title
                $event->sheet->mergeCells('A1:F1');
                $event->sheet->setCellValue('A1', $this->branch->company->company_name);
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set branch info
                $event->sheet->mergeCells('A2:F2');
                $event->sheet->setCellValue('A2', $this->branch->name);
                $event->sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set address
                $event->sheet->mergeCells('A3:F3');
                $address = $this->branch->unit_floor_number . ', ' . $this->branch->street . ', ' . $this->branch->city->name . ', ' . $this->branch->province->name . ', ' . $this->branch->region->name;
                $event->sheet->setCellValue('A3', $address);
                $event->sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set report name
                $event->sheet->mergeCells('A4:F4');
                $event->sheet->setCellValue('A4', 'Department Sales Report');
                $event->sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);
                $event->sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set date range
                $event->sheet->mergeCells('A5:F5');
                $dateRange = \Carbon\Carbon::parse($this->startDate)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($this->endDate)->format('M d, Y');
                $event->sheet->setCellValue('A5', $dateRange);
                $event->sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Format header row
                $headerRange = 'A8:F8';
                $event->sheet->getStyle($headerRange)->getFont()->setBold(true);
                $event->sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

                // Apply borders to all cells
                $lastRow = $event->sheet->getHighestRow();
                $dataRange = 'A8:F' . $lastRow;
                $event->sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Format total row
                $totalRowRange = 'A' . $lastRow . ':F' . $lastRow;
                $event->sheet->getStyle($totalRowRange)->getFont()->setBold(true);
                $event->sheet->getStyle($totalRowRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }
}
