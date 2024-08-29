<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductPhysicalCount;

class TestController extends Controller
{
    public function mapData(Request $request)
    {
        $branches = Branch::all();

        foreach ($branches as $branch) {
            $branchCode = strtoupper($branch->code);

            //order by id
            $pcounts = ProductPhysicalCount::where('branch_id', $branch->id)->orderBy('id')->get();

            $pcounter = 0;
            
            foreach ($pcounts as $pcount) {
                $date = date('Ymd', strtotime($pcount->created_at));
                $counter = str_pad($pcounter+1, 4, '0', STR_PAD_LEFT);
                $pcountNumber = "PCOUNT$branchCode$date$counter";

                $pcount->pcount_number = $pcountNumber;
                $pcount->save();

                $pcounter++;
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