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

class CategorySalesReportExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents, ShouldAutoSize, WithColumnFormatting
{
    protected $branchId;
    protected $startDate;
    protected $endDate;
    protected $branch;
    protected $totalRegularSales = 0;
    protected $totalDiscountSales = 0;

    public function __construct($branchId, $startDate, $endDate)
    {
        $this->branchId = $branchId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->branch = Branch::find($branchId);
    }

    public function collection()
    {
        // Get category sales data using raw SQL matching the transactional_db structure
        $categorySalesQuery = "
            SELECT 
                isync.categories.name as category, 
                SUM(transactional_db.orders.qty) as quantity_sold,
                SUM(CASE WHEN transactional_db.orders.discount_amount > 0 THEN transactional_db.orders.discount_amount ELSE 0 END) as discount_sales,
                SUM(transactional_db.orders.gross) as regular_sales
            FROM transactional_db.transactions
            INNER JOIN transactional_db.orders ON transactions.transaction_id = orders.transaction_id
                AND transactions.branch_id = orders.branch_id
                AND transactions.pos_machine_id = orders.pos_machine_id
                AND orders.is_void = FALSE
                AND orders.is_completed = TRUE
                AND orders.is_back_out = FALSE
            INNER JOIN isync.products ON orders.product_id = products.id
            INNER JOIN isync.categories ON products.category_id = categories.id
            WHERE transactions.is_complete = TRUE
                AND transactions.branch_id = {$this->branchId}
                AND transactions.is_void = FALSE
                AND transactions.is_back_out = FALSE
                AND transactions.treg BETWEEN '{$this->startDate}' AND '{$this->endDate}'
            GROUP BY isync.categories.name
            ORDER BY regular_sales DESC
        ";

        $categoryData = collect(DB::select($categorySalesQuery));

        // Calculate totals
        $this->totalRegularSales = $categoryData->sum('regular_sales');
        $this->totalDiscountSales = $categoryData->sum('discount_sales');
        
        // Add percentage to each category
        $categoryData->transform(function($item) {
            $item->percentage = $this->totalRegularSales > 0 ? round(($item->regular_sales / $this->totalRegularSales) * 100) : 0;
            return $item;
        });

        // Add a total row
        $categoryData->push((object)[
            'category' => 'TOTAL',
            'quantity_sold' => $categoryData->sum('quantity_sold'),
            'discount_sales' => $this->totalDiscountSales,
            'regular_sales' => $this->totalRegularSales,
            'percentage' => 100
        ]);

        return $categoryData;
    }

    public function headings(): array
    {
        return [
            'Category',
            'Quantity Sold',
            'Discount Sales',
            'Regular Sales',
            'Category Sales Percentage'
        ];
    }

    public function map($row): array
    {
        return [
            $row->category,
            $row->quantity_sold,
            $row->discount_sales,
            $row->regular_sales,
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
                $event->sheet->mergeCells('A1:E1');
                $event->sheet->setCellValue('A1', $this->branch->company->company_name);
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set branch info
                $event->sheet->mergeCells('A2:E2');
                $event->sheet->setCellValue('A2', $this->branch->name);
                $event->sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set address
                $event->sheet->mergeCells('A3:E3');
                $address = $this->branch->unit_floor_number . ', ' . $this->branch->street . ', ' . $this->branch->city->name . ', ' . $this->branch->province->name . ', ' . $this->branch->region->name;
                $event->sheet->setCellValue('A3', $address);
                $event->sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set report name
                $event->sheet->mergeCells('A4:E4');
                $event->sheet->setCellValue('A4', 'Category Sales Report');
                $event->sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);
                $event->sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set date range
                $event->sheet->mergeCells('A5:E5');
                $dateRange = \Carbon\Carbon::parse($this->startDate)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($this->endDate)->format('M d, Y');
                $event->sheet->setCellValue('A5', $dateRange);
                $event->sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Format header row
                $headerRange = 'A8:E8';
                $event->sheet->getStyle($headerRange)->getFont()->setBold(true);
                $event->sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

                // Apply borders to all cells
                $lastRow = $event->sheet->getHighestRow();
                $dataRange = 'A8:E' . $lastRow;
                $event->sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Format total row
                $totalRowRange = 'A' . $lastRow . ':E' . $lastRow;
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
        ];
    }
}
