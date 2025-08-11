<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateOrCreateProductJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productData;
    protected $itemLocations;
    protected $rowCount;

    public function __construct($productData, $itemLocations = null, $rowCount = null)
    {
        // Handle both single product and batch processing
        $this->productData = $productData;
        $this->itemLocations = $itemLocations;
        $this->rowCount = $rowCount;
    }

    public function handle()
    {
        try {
            // Check if this is batch processing (array of products) or single product
            if (is_array($this->productData) && isset($this->productData[0]) && is_array($this->productData[0])) {
                $this->processBatch();
            } else {
                $this->processSingle();
            }
        } catch (\Exception $e) {
            Log::error('Product import error: ' . $e->getMessage(), [
                'productData' => $this->productData,
                'rowCount' => $this->rowCount
            ]);
        }
    }

    protected function processBatch()
    {
        foreach ($this->productData as $productData) {
            $itemLocations = $productData['item_locations'] ?? null;
            unset($productData['item_locations']);

            $product = Product::updateOrCreate(
                [
                    'name' => $productData['name'],
                    'company_id' => $productData['company_id'],
                ],
                $productData
            );

            if ($itemLocations) {
                $product->itemLocations()->sync([$itemLocations]);
            }
        }
    }

    protected function processSingle()
    {
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
    }
}
