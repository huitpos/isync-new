<?php

namespace App\Exports;

use App\Models\ProductCountLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Str;

class ProductCountHistoryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $branchId;
    protected $productId;

    public function __construct($branchId, $productId)
    {
        $this->branchId = $branchId;
        $this->productId = $productId;
    }

    /**
     * Return a collection of data for the export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $logs = ProductCountLog::where('branch_id', $this->branchId)
            ->where('product_id', $this->productId)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        return new Collection($logs);
    }

    /**
     * Define the headings for the export.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Type',
            'Old Quantity',
            'New Quantity',
            'Date',
        ];
    }

    /**
     * Map the data for each row.
     *
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            Str::title(str_replace('_', ' ', $row->object_type)),
            $row->old_quantity,
            $row->new_quantity,
            $row->created_at ? $row->created_at->format('Y-m-d') : '',
        ];
    }
}
