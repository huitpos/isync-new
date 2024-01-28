<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Branch;

use Illuminate\Support\Str;

class BranchesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Branch::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'company_id' => 'required',
            'cluster_id' => 'required',
            'name' => 'required',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($data['name']);

        return Branch::create($data);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Branch::find($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $branch = Branch::find($id);
        $validatedData = $request->validate([
            'company_id' => 'required',
            'cluster_id' => 'required',
            'name' => 'required',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($data['name']);

        $branch->update($data);
        return $branch;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return Branch::destroy($id);
    }
}
