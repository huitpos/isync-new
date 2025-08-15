<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateOrCreateProductJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productData;
    protected $itemLocations;
    protected $rowCount;

    public function __construct(array $productData, $itemLocations = null, $rowCount = null)
    {
        $this->productData = $productData;
        $this->itemLocations = $itemLocations;
        $this->rowCount = $rowCount;
    }

    public function handle()
    {
        try {
            $product = Product::updateOrCreate(
                [
                    'name' => $this->productData['name'],
                    'company_id' => $this->productData['company_id'],
                ],
                $this->productData
            );
    
            if ($this->itemLocations) {
                $product->itemLocations()->sync([$this->itemLocations]);
            }
        } catch (\Exception $e) {
            dd($e->getMessage(), $this->productData, $this->rowCount);
        }
    }
}
