<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index($provinceId)
    {
        return $this->sendResponse(City::where('province_id', $provinceId)->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'name' => 'required',
        ]);

        return $this->sendResponse(City::create($validatedData));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return $this->sendResponse(City::find($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $city = City::find($id);

        $validatedData = $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'name' => 'required',
        ]);

        $data = $request->all();

        $city->update($data);
        return $this->sendResponse($city);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->sendResponse(City::destroy($id));
    }
}
