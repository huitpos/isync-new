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


use App\Models\CutOff;
use App\Models\Branch;
use App\Models\PaymentType;
use App\Models\DiscountType;
use App\Models\Payment;
use App\Models\Discount;
use App\Models\Transaction;

class ItemSalesReportExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $branchId;
    protected $paymentTypes;
    protected $discountTypes;
    protected $numberOfColumns;
    protected $headers;
    protected $totalNetSales;
    protected $itemSales;

    public function __construct($branchId, $startDate, $endDate)
    {
        $this->branchId = $branchId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        $headers =  [
            'Department',
            'SKU',
            'Product Name',
            'Qty Sold',
            'Item Cost',
            'Selling Price',
            'Gross Amount',
            'Discounts',
            'Net Sales',
            'Percentage',
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
        $query = "SELECT
                    transactional_db.orders.product_id,
                    products.name AS `product_name`,
                    products.sku,
                    products.cost,
                    products.srp,
                    departments.name AS `department`,
                    SUM(transactional_db.orders.gross) AS gross,
                    SUM(transactional_db.orders.qty) AS qty,
                    SUM(transactional_db.discount_details.discount_amount) AS `discount`,
                    SUM(transactional_db.orders.total) AS `net`
                FROM transactional_db.transactions
                INNER JOIN transactional_db.orders ON transactions.transaction_id = orders.transaction_id
                    AND transactions.branch_id = orders.branch_id
                    AND transactions.pos_machine_id = orders.pos_machine_id
                    AND orders.is_void = FALSE
                    AND orders.is_completed = TRUE
                    AND orders.is_back_out = FALSE
                LEFT JOIN transactional_db.discount_details ON orders.order_id = discount_details.order_id
                    AND orders.branch_id = discount_details.branch_id
                    AND orders.pos_machine_id = discount_details.pos_machine_id
                INNER JOIN isync.products ON orders.product_id = products.id
                INNER JOIN isync.departments on products.department_id = departments.id
                WHERE transactions.is_complete = TRUE
                    AND transactions.branch_id = $this->branchId
                    AND transactions.is_void = FALSE
                    AND transactions.is_back_out = FALSE
                    AND transactions.treg BETWEEN '$this->startDate' AND '$this->endDate'
                GROUP BY orders.product_id";

        $itemSales = DB::select($query);

        $this->totalNetSales = collect($itemSales)->sum('net');

        $this->itemSales = new Collection($itemSales);

        return new Collection($itemSales);
    }

    /**
     * Define the headings for the export.
     *
     * @return array
     */
    public function headings(): array
    {
        return $this->headers;
    }

    public function map($item): array
    {
        $data = [
            $item->department,
            $item->sku,
            $item->product_name,
            number_format($item->qty, 2),
            number_format($item->cost, 2),
            number_format($item->srp, 2),
            number_format($item->gross, 2),
            number_format($item->discount, 2),
            number_format($item->net, 2),
            number_format($item->net / $this->totalNetSales * 100, 2)
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
        return 'A12';
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

                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A1')->getFont()->setBold(true);

                $event->sheet->mergeCells('A2:'.$columnLetter.'2');
                $event->sheet->setCellValue('A2', $branch->name);

                $event->sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->mergeCells('A3:'.$columnLetter.'3');
                $event->sheet->setCellValue('A3', $branch->unit_floor_number . ', ' . $branch->street . ', ' . $branch->city->name . ', ' . $branch->province->name . ', ' . $branch->region->name);

                $event->sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->mergeCells('A4:'.$columnLetter.'4');
                $event->sheet->setCellValue('A4', 'Item Sales Report');
                $event->sheet->getStyle('A4')->getFont()->setBold(true);

                $event->sheet->mergeCells('A5:'.$columnLetter.'5');
                $event->sheet->setCellValue('A5', 'Date range: ' . $startDate . ' - ' . $endDate);

                $event->sheet->mergeCells('A6:Q6');
                $event->sheet->setCellValue('A6', 'Date generated: ' . now()->format('Y-m-d H:i:s'));

                $event->sheet->mergeCells('A7:Q7');
                $event->sheet->setCellValue('A7', 'Created by: ' . auth()->user()->name);

                $event->sheet->setCellValue('B9', 'Qty Sold');
                $event->sheet->setCellValue('C9', 'Item Cost');
                $event->sheet->setCellValue('D9', 'Selling Price');
                $event->sheet->setCellValue('E9', 'Gross Amount');
                $event->sheet->setCellValue('F9', 'Discounts');
                $event->sheet->setCellValue('G9', 'Net Sales');
                $event->sheet->setCellValue('H9', 'Percentage');

                $event->sheet->setCellValue('A10', 'Total');
                $event->sheet->setCellValue('B10', number_format($this->itemSales->sum('qty'), 2));
                $event->sheet->setCellValue('C10', number_format($this->itemSales->sum('cost'), 2));
                $event->sheet->setCellValue('D10', number_format($this->itemSales->sum('srp'), 2));
                $event->sheet->setCellValue('E10', number_format($this->itemSales->sum('gross'), 2));
                $event->sheet->setCellValue('F10', number_format($this->itemSales->sum('discount'), 2));
                $event->sheet->setCellValue('G10', number_format($this->itemSales->sum('net'), 2));
                $event->sheet->setCellValue('H10', number_format(100, 2));

                $totalRows = $event->sheet->getHighestRow();

                $cellRange = 'A9:J'.$totalRows;
                $event->sheet->getStyle($cellRange)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $event->sheet->getStyle($cellRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
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
