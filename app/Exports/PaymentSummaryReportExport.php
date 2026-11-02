<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

use App\Models\Branch;

class PaymentSummaryReportExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents, ShouldAutoSize, WithColumnFormatting
{
    protected $payments;
    protected $branch;
    protected $startDate;
    protected $endDate;
    protected $totalAmount;

    public function __construct($payments, Branch $branch, $startDate, $endDate)
    {
        $this->payments = $payments;
        $this->branch = $branch;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->totalAmount = $payments->sum('amount');
    }

    public function collection()
    {
        $rows = $this->payments->map(function ($payment) {
            $payment->share = $this->totalAmount > 0
                ? round(($payment->amount / $this->totalAmount) * 100, 2)
                : 0;

            return $payment;
        });

        $rows->push((object) [
            'payment_type' => 'TOTAL',
            'qty' => $this->payments->sum('qty'),
            'amount' => $this->totalAmount,
            'share' => $this->payments->isEmpty() ? 0 : 100,
        ]);

        return new Collection($rows);
    }

    public function headings(): array
    {
        return [
            'Payment Type',
            'Quantity',
            'Amount',
            'Share',
        ];
    }

    public function map($row): array
    {
        return [
            $row->payment_type,
            $row->qty,
            $row->amount,
            $row->share . '%',
        ];
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->mergeCells('A1:D1');
                $event->sheet->setCellValue('A1', $this->branch->company->company_name);
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $event->sheet->mergeCells('A2:D2');
                $event->sheet->setCellValue('A2', $this->branch->name);
                $event->sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $event->sheet->mergeCells('A3:D3');
                $address = $this->branch->unit_floor_number . ', ' . $this->branch->street . ', ' . $this->branch->city->name . ', ' . $this->branch->province->name . ', ' . $this->branch->region->name;
                $event->sheet->setCellValue('A3', $address);
                $event->sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $event->sheet->mergeCells('A4:D4');
                $event->sheet->setCellValue('A4', 'Payment Summary Report');
                $event->sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);
                $event->sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $event->sheet->mergeCells('A5:D5');
                $dateRange = \Carbon\Carbon::parse($this->startDate)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($this->endDate)->format('M d, Y');
                $event->sheet->setCellValue('A5', $dateRange);
                $event->sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $headerRange = 'A8:D8';
                $event->sheet->getStyle($headerRange)->getFont()->setBold(true);
                $event->sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

                $lastRow = $event->sheet->getHighestRow();
                $dataRange = 'A8:D' . $lastRow;
                $event->sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $totalRowRange = 'A' . $lastRow . ':D' . $lastRow;
                $event->sheet->getStyle($totalRowRange)->getFont()->setBold(true);
                $event->sheet->getStyle($totalRowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }
}
