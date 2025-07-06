<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use App\Models\Branch;

class TopPerformingProductsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithCustomStartCell, WithTitle, WithStyles
{
    protected $branchId;
    protected $startDate;
    protected $endDate;
    protected $branch;

    public function __construct($branchId, $startDate, $endDate)
    {
        $this->branchId = $branchId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->branch = Branch::find($branchId);
    }

    public function collection()
    {
        $query = "SELECT
                    products.name AS `description`,
                    products.sku,
                    departments.name AS `department`,
                    categories.name AS `category`,
                    subcategories.name AS `sub_category`,
                    SUM(transactional_db.orders.qty) AS `quantity_sold`,
                    0 AS `ar_unpaid_quantity`,
                    SUM(transactional_db.orders.qty * products.cost) AS `total_unit_cost`,
                    SUM(transactional_db.discount_details.discount_amount) AS `discount_sales`,
                    SUM(transactional_db.orders.total) AS `regular_sales`,
                    (SUM(transactional_db.orders.total) / (SELECT SUM(total) FROM transactional_db.orders WHERE branch_id = {$this->branchId}) * 100) AS `sales_percentage`
                FROM transactional_db.transactions
                INNER JOIN transactional_db.orders ON transactions.transaction_id = orders.transaction_id
                    AND transactions.branch_id = orders.branch_id
                    AND transactions.pos_machine_id = orders.pos_machine_id
                    AND orders.is_void = FALSE
                    AND orders.is_completed = TRUE
                    AND orders.is_back_out = FALSE
                    AND orders.is_return = FALSE
                LEFT JOIN transactional_db.discount_details ON orders.order_id = discount_details.order_id
                    AND orders.branch_id = discount_details.branch_id
                    AND orders.pos_machine_id = discount_details.pos_machine_id
                INNER JOIN isync.products ON orders.product_id = products.id
                INNER JOIN isync.departments ON products.department_id = departments.id
                LEFT JOIN isync.categories ON products.category_id = categories.id
                LEFT JOIN isync.subcategories ON products.subcategory_id = subcategories.id
                WHERE transactions.is_complete = TRUE
                    AND transactions.branch_id = {$this->branchId}
                    AND transactions.is_void = FALSE
                    AND transactions.is_back_out = FALSE
                    AND transactions.treg BETWEEN '{$this->startDate}' AND '{$this->endDate}'
                GROUP BY orders.product_id
                ORDER BY `regular_sales` DESC
                LIMIT 100";

        return collect(DB::select($query));
    }

    public function headings(): array
    {
        return [
            'Description',
            'SKU',
            'Department',
            'Category',
            'Sub Category',
            'Quantity Sold',
            'AR Unpaid Quantity',
            'Total Unit Cost',
            'Discount Sales',
            'Regular Sales',
            'Sales Percentage'
        ];
    }

    public function map($row): array
    {
        return [
            $row->description,
            $row->sku,
            $row->department,
            $row->category,
            $row->sub_category,
            $row->quantity_sold,
            $row->ar_unpaid_quantity,
            $row->total_unit_cost,
            $row->discount_sales,
            $row->regular_sales,
            number_format($row->sales_percentage, 0) . '%'
        ];
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function title(): string
    {
        return 'Top Performing Products';
    }

    public function styles(Worksheet $sheet)
    {
        // Format the header cells
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', config('app.name'));
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', $this->branch->name ?? 'All Branches');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells('A3:K3');
        $sheet->setCellValue('A3', $this->branch->address ?? '');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Report title and metadata
        $sheet->setCellValue('A5', 'Top Performing Products Report');
        $sheet->getStyle('A5')->getFont()->setBold(true);
        
        $startDate = Carbon::parse($this->startDate)->format('m/d/Y');
        $endDate = Carbon::parse($this->endDate)->format('m/d/Y');
        $sheet->setCellValue('A6', "Date range: {$startDate} - {$endDate}");
        
        $sheet->setCellValue('A7', "Date generated: " . Carbon::now()->format('m/d/Y'));
        
        // Style the headings row
        $headingsRow = 8;
        $sheet->getStyle("A{$headingsRow}:K{$headingsRow}")->applyFromArray([
            'font' => ['bold' => true],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE0E0E0'],
            ]
        ]);

        // Set number formats for currency and percentage columns
        $dataRows = $sheet->getHighestRow();
        $sheet->getStyle("H9:J{$dataRows}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("K9:K{$dataRows}")->getNumberFormat()->setFormatCode('0%');
        
        return [
            $headingsRow => [
                'font' => ['bold' => true],
            ]
        ];
    }
}
