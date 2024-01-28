<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Cluster;

class ClustersController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->sendResponse(Cluster::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'company_id' => 'required',
            'name' => 'required',
        ]);

        return $this->sendResponse(Cluster::create($request->all()));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if ($cluster = Cluster::find($id)) {
            return $this->sendResponse($cluster);
        }
        return $this->sendError('Cluster not found.', [], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cluster = Cluster::find($id);
        $validatedData = $request->validate([
            'company_id' => 'required',
            'name' => 'required',
        ]);

        if ($cluster->update($request->all())) {
            return $this->sendResponse($cluster);
        }

        return $this->sendError('An error occurred. Please try again later', [], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (Cluster::destroy($id)) {
            return $this->sendResponse(true);
        }

        return $this->sendError('An error occurred. Please try again later', [], 200);
    }
}
