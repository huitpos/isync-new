<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\DeliveryLocation;
use App\Models\Product;

class TestController extends Controller
{
    public function mapData(Request $request)
    {
        $branches = Branch::all();

        foreach ($branches as $branch) {
            foreach ($branch->company->products as $product) {
                if ($branch->products()->where('product_id', $product->id)->exists()) {
                    // Product already exists in the branch, update the existing pivot record
                    $branch->products()->updateExistingPivot($product->id, [
                        'price' => $product->srp,
                        'stock' => 0
                    ]);
                } else {
                    // Product doesn't exist in the branch, create a new pivot record
                    $branch->products()->attach($product->id, [
                        'price' => $product->srp,
                        'stock' => 0
                    ]);
                }
            }
        }
    }

    public function mapUomData(Request $request)
    {
        $products = Product::all();
        foreach ($products as $product) {
            $product->delivery_uom_id = $product->uom_id;
            $product->save();
        }
    }
}