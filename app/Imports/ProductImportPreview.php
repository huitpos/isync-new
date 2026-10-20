<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ProductImportPreview implements ToCollection, WithStartRow, WithCalculatedFormulas
{
    public function startRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
    }
}
