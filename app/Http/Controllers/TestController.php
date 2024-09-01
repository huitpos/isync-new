<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductDisposal;
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

            $disposals = ProductDisposal::where('branch_id', $branch->id)->orderBy('id')->get();

            $disposalCounter = 0;
            foreach ($disposals as $disposal) {
                $date = date('Ymd', strtotime($pcount->created_at));
                $counter = str_pad($disposalCounter+1, 4, '0', STR_PAD_LEFT);
                $pdisNumber = "PDIS$branchCode$date$counter";

                $disposal->pdis_number = $pdisNumber;
                $disposal->save();

                $disposalCounter++;
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