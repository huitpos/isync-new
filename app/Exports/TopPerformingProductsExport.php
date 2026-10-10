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
    protected $limit;
    protected $branch;

    public function __construct($branchId, $startDate, $endDate, $limit = '100')
    {
        $this->branchId = $branchId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->limit = $limit === 'all' ? 'all' : '100';
        $this->branch = Branch::find($branchId);
    }

    public function collection()
    {
        $transactionalDb = config('database.connections.transactional_db.database');
        $mainDb = config('database.connections.mysql.database');
        $branchId = (int) $this->branchId;

        $limitSql = $this->limit === 'all' ? '' : 'LIMIT 100';

        $query = "SELECT
                    COALESCE(products.name, orders_agg.product_name) AS `description`,
                    products.sku,
                    departments.name AS `department`,
                    categories.name AS `category`,
                    subcategories.name AS `sub_category`,
                    orders_agg.quantity_sold,
                    0 AS `ar_unpaid_quantity`,
                    orders_agg.total_unit_cost,
                    orders_agg.discount_sales,
                    orders_agg.regular_sales,
                    CASE
                        WHEN SUM(orders_agg.regular_sales) OVER () = 0 THEN 0
                        ELSE orders_agg.regular_sales / SUM(orders_agg.regular_sales) OVER () * 100
                    END AS `sales_percentage`
                FROM (
                    SELECT
                        orders.product_id,
                        MAX(orders.name) AS product_name,
                        SUM(orders.qty) AS quantity_sold,
                        SUM(orders.total_cost) AS total_unit_cost,
                        SUM(orders.discount_amount) AS discount_sales,
                        SUM(orders.total) AS regular_sales
                    FROM {$transactionalDb}.transactions
                    INNER JOIN {$transactionalDb}.orders
                        ON orders.branch_id = transactions.branch_id
                        AND orders.transaction_id = transactions.transaction_id
                        AND orders.pos_machine_id = transactions.pos_machine_id
                        AND orders.is_void = 0
                        AND orders.is_completed = 1
                        AND orders.is_back_out = 0
                        AND orders.is_return = 0
                    WHERE transactions.branch_id = ?
                        AND transactions.is_complete = 1
                        AND transactions.is_void = 0
                        AND transactions.is_back_out = 0
                        AND transactions.treg BETWEEN ? AND ?
                    GROUP BY orders.product_id
                ) AS orders_agg
                LEFT JOIN {$mainDb}.products ON products.id = orders_agg.product_id
                LEFT JOIN {$mainDb}.departments ON departments.id = products.department_id
                LEFT JOIN {$mainDb}.categories ON categories.id = products.category_id
                LEFT JOIN {$mainDb}.subcategories ON subcategories.id = products.subcategory_id
                ORDER BY orders_agg.regular_sales DESC
                {$limitSql}";

        return collect(DB::select($query, [$branchId, $this->startDate, $this->endDate]));
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
