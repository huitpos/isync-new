<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Branch;
use App\Models\ProductCountLog;
use App\Models\UnitConversion;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    public function all(): Collection
    {
        return Product::all();
    }

    public function get($parameters = []): ?Collection
    {
        return Product::where($parameters)->get();
    }

    public function find(String $id): ?Product
    {
        return Product::with([
            'bundledItems',
            'rawItems',
        ])->find($id);
    }

    public function create(array $productData, array $bundledData, array $rawData): Product
    {
        $lastNumber = Product::where('company_id', $productData['company_id'])->max('code');
        $productData['code'] = $lastNumber + 1;
        
        $itemLocations = $productData['item_locations'] ?? null;
        unset($productData['item_locations']);
        
        $product = Product::create($productData);

        if (!empty($bundledData)) {
            foreach ($bundledData as $bundledItem) {
                $product->bundledItems()->attach($bundledItem['product_id'], ['quantity' => $bundledItem['quantity']]);
            }
        }

        if (!empty($rawData)) {
            foreach ($rawData as $rawItem) {
                $product->rawItems()->attach($rawItem['product_id'], [
                    'quantity' => $rawItem['quantity'],
                    'uom_id' => $rawItem['uom_id'],
                ]);
            }
        }

        if ($itemLocations) {
            $product->itemLocations()->sync($itemLocations);
        }

        return $product;
    }

    public function update(String $id, array $productData, array $bundledData, array $rawData): Product
    {
        $itemLocations = $productData['item_locations'] ?? null;
        unset($productData['item_locations']);
        

        $product = Product::findOrFail($id);
        $product->update($productData);

        if (!empty($bundledData)) {
            $product->bundledItems()->detach();

            foreach ($bundledData as $bundledItem) {
                $product->bundledItems()->attach($bundledItem['product_id'], ['quantity' => $bundledItem['quantity']]);
            }
        }

        $product->rawItems()->detach();
        if (!empty($rawData)) {
            foreach ($rawData as $rawItem) {
                $product->rawItems()->attach($rawItem['product_id'], [
                    'quantity' => $rawItem['quantity'],
                    'uom_id' => $rawItem['uom_id'],
                ]);
            }
        }

        if ($itemLocations) {
            $product->itemLocations()->sync($itemLocations);
        }

        return $product;
    }

    public function delete(String $id): Bool
    {
        $paymentType = Product::findOrFail($id);
        return $paymentType->delete();
    }

    public function updateBranchQuantity(Product $product, Branch $branch, $objectId, $objectType, int $qty, $srp = null, $operation = 'add', $uomId): Bool
    {
        $pivotData = $product->branches->where('id', $branch->id)->first()->pivot;
        if ($product->uom_id != $uomId) {
            $conversion = UnitConversion::where([
                'from_unit_id' => $product->uom_id,
                'to_unit_id' => $uomId
            ])->first();

            if (empty($conversion)) {
                $conversion = UnitConversion::where([
                    'to_unit_id' => $product->uom_id,
                    'from_unit_id' => $uomId
                ])->first();

                $qty = $qty * $conversion->value;
            } else {
                $qty = $qty / $conversion->value;
            }
        }

        if ($operation == 'add') {
            $newStock = $pivotData->stock + $qty;
        } elseif ($operation == 'replace') {
            $newStock = $qty;
        } else {
            $newStock = $pivotData->stock - $qty;
        }

        $updateData = [
            'stock' => $newStock
        ];

        if ($srp) {
            $updateData['price'] = $srp;
        }

        if ($branch->products()->where('product_id', $product->id)->exists()) {
            // Product already exists in the branch, update the existing pivot record
            $branch->products()->updateExistingPivot($product->id, $updateData);
        } else {
            // Product doesn't exist in the branch, create a new pivot record
            $branch->products()->attach($product->id, $updateData);
        }

        //new ProductCountLog
        $productCountLog = new ProductCountLog();
        $productCountLog->branch_id = $branch->id;
        $productCountLog->product_id = $product->id;
        $productCountLog->object_id = $objectId;
        $productCountLog->object_type = $objectType;
        $productCountLog->old_quantity = $pivotData->stock;
        $productCountLog->new_quantity = $newStock;
        $productCountLog->uom_id = $uomId;
        $productCountLog->save();

        return true;
    }
}
