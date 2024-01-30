<?php

namespace App\Repositories;

use App\Models\Product;
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

        return $product;
    }

    public function update(String $id, array $productData, array $bundledData, array $rawData): Product
    {
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

        return $product;
    }

    public function delete(String $id): Bool
    {
        $paymentType = Product::findOrFail($id);
        return $paymentType->delete();
    }
}
