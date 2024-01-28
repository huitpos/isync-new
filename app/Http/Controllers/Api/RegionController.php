<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->sendResponse(Region::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required'
        ]);

        return $this->sendResponse(Region::create($validatedData));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return $this->sendResponse(Region::find($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $region = Region::find($id);
        $validatedData = $request->validate([
            'name' => 'required',
        ]);

        $data = $request->all();

        $region->update($data);
        return $this->sendResponse($region);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->sendResponse(Region::destroy($id));
    }
}
