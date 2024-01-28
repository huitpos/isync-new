<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\Request;

class ProvinceController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index($regionId)
    {
        return $this->sendResponse(Province::where('region_id', $regionId)->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required',
        ]);

        return $this->sendResponse(Province::create($validatedData));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return $this->sendResponse(Province::find($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $province = Province::find($id);

        $validatedData = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required',
        ]);

        $data = $request->all();

        $province->update($data);
        return $this->sendResponse($province);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->sendResponse(Province::destroy($id));
    }
}
