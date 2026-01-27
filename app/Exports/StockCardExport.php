<?php

namespace App\Exports;

use Illuminate\Support\Collection;
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
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\StockCardSheets\PhysicalCountSheet;
use App\Exports\StockCardSheets\TransactionsSheet;
use App\Exports\StockCardSheets\IncomingStocksSheet;
use App\Exports\StockCardSheets\TransferInSheet;
use App\Exports\StockCardSheets\TransferOutSheet;
use App\Exports\StockCardSheets\DisposalSheet;

class StockCardExport implements WithMultipleSheets
{
    protected $branchId;
    protected $productId;
    protected $product;
    protected $pivotData;
    protected $physicalCounts;
    protected $transactions;
    protected $incomingStocks;
    protected $stockTransferIn;
    protected $stockTransferOut;
    protected $disposals;
    protected $startDate;
    protected $endDate;

    public function __construct(
        $branchId,
        $productId,
        $product,
        $pivotData,
        $physicalCounts,
        $transactions,
        $incomingStocks,
        $stockTransferIn,
        $stockTransferOut,
        $disposals,
        $startDate,
        $endDate
    ) {
        $this->branchId = $branchId;
        $this->productId = $productId;
        $this->product = $product;
        $this->pivotData = $pivotData;
        $this->physicalCounts = $physicalCounts;
        $this->transactions = $transactions;
        $this->incomingStocks = $incomingStocks;
        $this->stockTransferIn = $stockTransferIn;
        $this->stockTransferOut = $stockTransferOut;
        $this->disposals = $disposals;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function sheets(): array
    {
        return [
            'Physical Count' => new PhysicalCountSheet(
                $this->physicalCounts,
                $this->product,
                $this->pivotData,
                $this->startDate,
                $this->endDate
            ),
            'Transactions' => new TransactionsSheet(
                $this->transactions,
                $this->product,
                $this->pivotData,
                $this->startDate,
                $this->endDate
            ),
            'Incoming Stocks' => new IncomingStocksSheet(
                $this->incomingStocks,
                $this->product,
                $this->pivotData,
                $this->startDate,
                $this->endDate
            ),
            'Transfer In' => new TransferInSheet(
                $this->stockTransferIn,
                $this->product,
                $this->pivotData,
                $this->startDate,
                $this->endDate
            ),
            'Transfer Out' => new TransferOutSheet(
                $this->stockTransferOut,
                $this->product,
                $this->pivotData,
                $this->startDate,
                $this->endDate
            ),
            'Disposal' => new DisposalSheet(
                $this->disposals,
                $this->product,
                $this->pivotData,
                $this->startDate,
                $this->endDate
            ),
        ];
    }
}
