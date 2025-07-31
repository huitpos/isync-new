<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Table;
use App\Models\TableLocation;

class TableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        //get TableLocation by branch_id
        $tables = Table::where('branch_id', $branch->id)->get();
        
        return view('branch.tables.index', [
            'company' => $company,
            'branch' => $branch,
            'tables' => $tables,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $tableLocations = TableLocation::where('branch_id', $branch->id)
            ->where('status', 'active')
            ->get();

        return view('branch.tables.create', [
            'company' => $company,
            'branch' => $branch,
            'tableLocations' => $tableLocations,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'capacity' => 'required|numeric',
            'table_location_id' => 'required|numeric',
        ]);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $tableData = $request->only([
            'name',
            'capacity',
            'table_location_id',
            'status'
        ]);

        $tableData['branch_id'] = $branch->id;

        if (Table::create($tableData)) {
            return redirect()->route('branch.tables.index', [
                    'companySlug' => $request->attributes->get('company')->slug,
                    'branchSlug' => $request->attributes->get('branch')->slug
                ])->with('success', 'Table created successfully.');
        }

        return redirect()->route('branch.tables.index', [
                'companySlug' => $request->attributes->get('company')->slug,
                'branchSlug' => $request->attributes->get('branch')->slug
            ])->with('error', 'Table failed to create.');
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
        $table = Table::findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $tableLocations = TableLocation::where('branch_id', $branch->id)
            ->where('status', 'active')
            ->get();

        return view('branch.tables.edit', [
            'company' => $company,
            'branch' => $branch,
            'table' => $table,
            'tableLocations' => $tableLocations,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $table = Table::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'capacity' => 'required|numeric',
            'table_location_id' => 'required|numeric',
        ]);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $tableData = $request->only([
            'name',
            'capacity',
            'table_location_id',
            'status'
        ]);

        if ($table->update($tableData)) {
            return redirect()->route('branch.tables.index', [
                    'companySlug' => $request->attributes->get('company')->slug,
                    'branchSlug' => $request->attributes->get('branch')->slug
                ])->with('success', 'Table updated successfully.');
        }

        return redirect()->route('branch.tables.index', [
                'companySlug' => $request->attributes->get('company')->slug,
                'branchSlug' => $request->attributes->get('branch')->slug
            ])->with('error', 'Table failed to updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
