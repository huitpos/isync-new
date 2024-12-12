<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateOrCreateProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productData;
    protected $itemLocations;

    public function __construct(array $productData, $itemLocations = null)
    {
        $this->productData = $productData;
        $this->itemLocations = $itemLocations;
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
            dd($e->getMessage(), $this->productData);
        }
    }
}
