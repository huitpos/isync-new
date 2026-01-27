<?php

namespace App\Exports\StockCardSheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DisposalSheet implements FromArray, WithHeadings, WithTitle, WithEvents, ShouldAutoSize, WithColumnFormatting
{
    protected $data;
    protected $product;
    protected $pivotData;
    protected $startDate;
    protected $endDate;

    public function __construct($data, $product, $pivotData, $startDate, $endDate)
    {
        $this->data = $data;
        $this->product = $product;
        $this->pivotData = $pivotData;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data as $item) {
            $rows[] = [
                $item->date,
                $item->pdis_number,
                $item->quantity,
                $item->uom,
                $item->cost,
                $item->reason,
                $item->created_by,
                $item->action_by,
            ];
        }
        return $rows;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Disposal Number',
            'Quantity',
            'UOM',
            'Cost',
            'Reason',
            'Created By',
            'Action By',
        ];
    }

    public function title(): string
    {
        return 'Disposal';
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DATETIME,
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                // Add title and product info above the headers
                $sheet->setCellValue('A1', 'STOCK CARD - DISPOSAL');
                $sheet->mergeCells('A1:H1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue('A2', 'Product: ' . ($this->product->product_description ?? ''));
                $sheet->mergeCells('A2:H2');
                $sheet->getStyle('A2')->getFont()->setBold(true);

                $sheet->setCellValue('A3', 'SKU: ' . ($this->product->product_code ?? ''));
                $sheet->mergeCells('A3:H3');

                $sheet->setCellValue('A4', 'Date Range: ' . date('M d, Y', strtotime($this->startDate)) . ' - ' . date('M d, Y', strtotime($this->endDate)));
                $sheet->mergeCells('A4:H4');

                // Style header row
                $sheet->getStyle('A5:H5')->getFont()->setBold(true);
                $sheet->getStyle('A5:H5')->getFill()->setFillType(Fill::FILL_SOLID);
                $sheet->getStyle('A5:H5')->getFill()->getStartColor()->setARGB('FFD9D9D9');
            },
        ];
    }
}
