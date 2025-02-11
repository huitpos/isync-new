<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;

class ProcessExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $companyId;

    public function __construct($filePath, $companyId)
    {
        $this->filePath = $filePath;
        $this->companyId = $companyId;
    }

    public function handle()
    {
        try {
            // Run the import logic with Maatwebsite
            $import = new ProductsImport($this->companyId);
            Excel::import($import, $this->filePath);

            \Log::info('Excel import successful');
        } catch (\Exception $e) {
            \Log::error('Excel import failed: ' . $e->getMessage());
        }
    }
}
