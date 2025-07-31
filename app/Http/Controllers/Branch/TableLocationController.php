<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TableLocation;

class TableLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        //get TableLocation by branch_id
        $tableLocations = TableLocation::where('branch_id', $branch->id)->get();
        
        return view('branch.tableLocation.index', [
            'company' => $company,
            'branch' => $branch,
            'tableLocations' => $tableLocations,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return view('branch.tableLocation.create', [
            'company' => $company,
            'branch' => $branch,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $locationData = $request->only([
            'name',
            'status',
        ]);

        $locationData['branch_id'] = $branch->id;

        if (TableLocation::create($locationData)) {
            return redirect()->route('branch.table-locations.index', [
                    'companySlug' => $request->attributes->get('company')->slug,
                    'branchSlug' => $request->attributes->get('branch')->slug
                ])->with('success', 'Table location created successfully.');
        }

        return redirect()->route('branch.table-locations.index', [
                'companySlug' => $request->attributes->get('company')->slug,
                'branchSlug' => $request->attributes->get('branch')->slug
            ])->with('error', 'Table location failed to create.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $tableLocation = TableLocation::findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return view('branch.tableLocation.edit', [
            'company' => $company,
            'branch' => $branch,
            'tableLocation' => $tableLocation
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $tableLocation = TableLocation::findOrFail($id);

        $locationData = $request->only([
            'name',
            'status',
        ]);

        if ($tableLocation->update($locationData)) {
            return redirect()->route('branch.table-locations.index', [
                    'companySlug' => $request->attributes->get('company')->slug,
                    'branchSlug' => $request->attributes->get('branch')->slug
                ])->with('success', 'Table location updated successfully.');
        }

        return redirect()->route('branch.table-locations.index', [
                'companySlug' => $request->attributes->get('company')->slug,
                'branchSlug' => $request->attributes->get('branch')->slug
            ])->with('error', 'Table location failed to update.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
