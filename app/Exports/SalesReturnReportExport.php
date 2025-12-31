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

class SalesReturnReportExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents, ShouldAutoSize, WithColumnFormatting
{
    protected $branchId;
    protected $startDate;
    protected $endDate;
    protected $branch;
    protected $totalReturnAmount = 0;

    public function __construct($branchId, $startDate, $endDate)
    {
        $this->branchId = $branchId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->branch = Branch::find($branchId);
    }

    public function collection()
    {
        // Get sales return data using raw SQL matching the transactional_db structure
        // Focus on orders with negative quantity (returns)
        $salesReturnQuery = "
            SELECT 
                isync.products.name as `name`, 
                transactional_db.transactions.pos_machine_id as machine_number,
                SUM(ABS(transactional_db.orders.qty)) as quantity,
                SUM(ABS(transactional_db.orders.gross)) as total_amount
            FROM transactional_db.transactions
            INNER JOIN transactional_db.orders ON transactions.transaction_id = orders.transaction_id
                AND transactions.branch_id = orders.branch_id
                AND transactions.pos_machine_id = orders.pos_machine_id
                AND orders.is_void = FALSE
                AND orders.is_completed = TRUE
                AND orders.is_back_out = FALSE
                AND orders.qty < 0  -- This is the key filter for returns
            INNER JOIN isync.products ON orders.product_id = products.id
            WHERE transactions.is_complete = TRUE
                AND transactions.branch_id = {$this->branchId}
                AND transactions.is_void = FALSE
                AND transactions.is_back_out = FALSE
                AND transactions.treg BETWEEN '{$this->startDate}' AND '{$this->endDate}'
            GROUP BY isync.products.name, transactional_db.transactions.pos_machine_id
            ORDER BY total_amount DESC
        ";

        $returnData = collect(DB::select($salesReturnQuery));

        // Calculate total
        $this->totalReturnAmount = $returnData->sum('total_amount');
        
        // Add a total row
        $returnData->push((object)[
            'name' => 'TOTAL',
            'machine_number' => '',
            'quantity' => $returnData->sum('quantity'),
            'total_amount' => $this->totalReturnAmount
        ]);

        return $returnData;
    }

    public function headings(): array
    {
        return [
            'Description',
            'Machine #',
            'Total Quantity',
            'Total Amount'
        ];
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->machine_number,
            $row->quantity,
            $row->total_amount
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
                $event->sheet->setCellValue('A4', 'Sales Return Report');
                $event->sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);
                $event->sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set date range
                $event->sheet->mergeCells('A5:D5');
                $dateRange = \Carbon\Carbon::parse($this->startDate)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($this->endDate)->format('M d, Y');
                $event->sheet->setCellValue('A5', $dateRange);
                $event->sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Format header row
                $headerRange = 'A8:D8';
                $event->sheet->getStyle($headerRange)->getFont()->setBold(true);
                $event->sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

                // Apply borders to all cells
                $lastRow = $event->sheet->getHighestRow();
                $dataRange = 'A8:D' . $lastRow;
                $event->sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Format total row
                $totalRowRange = 'A' . $lastRow . ':D' . $lastRow;
                $event->sheet->getStyle($totalRowRange)->getFont()->setBold(true);
                $event->sheet->getStyle($totalRowRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }
}
