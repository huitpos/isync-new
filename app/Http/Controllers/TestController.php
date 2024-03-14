<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\DeliveryLocation;

class TestController extends Controller
{
    public function mapData(Request $request)
    {
        $branches = Branch::all();

        foreach ($branches as $branch) {
            $locationData = [
                'branch_id' => $branch->id,
                'name' => $branch->name,
                'unit_floor_number' => $branch->unit_floor_number,
                'street' => $branch->street,
                'region_id' => $branch->region_id,
                'province_id' => $branch->province_id,
                'city_id' => $branch->city_id,
                'barangay_id' => $branch->barangay_id,
                'is_default' => true,
            ];

            //create DeliveryLocation
            DeliveryLocation::create($locationData);
        }

        dd("here");
    }
}