<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use Illuminate\Http\Request;

class BarangayController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index($cityId)
    {
        return $this->sendResponse(Barangay::where('city_id', $cityId)->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required',
        ]);

        return $this->sendResponse(Barangay::create($validatedData));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return $this->sendResponse(Barangay::find($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $barangay = Barangay::find($id);

        $validatedData = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required',
        ]);

        $data = $request->all();

        $barangay->update($data);
        return $this->sendResponse($barangay);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->sendResponse(Barangay::destroy($id));
    }
}
