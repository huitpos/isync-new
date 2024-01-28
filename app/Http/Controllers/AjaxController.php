<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;

class AjaxController extends Controller
{
    public function getProvinces(Request $request)
    {
        $provinces = Province::where('region_id', $request->region_id)->get();
        return response()->json($provinces);
    }

    public function getCities(Request $request)
    {
        $cities = City::where('province_id', $request->province_id)->get();
        return response()->json($cities);
    }

    public function getBarangays(Request $request)
    {
        $barangays = Barangay::where('city_id', $request->city_id)->get();
        return response()->json($barangays);
    }
}
