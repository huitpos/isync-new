<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TableStatus;

class TableStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        //get TableLocation by branch_id or branch_id is null
        $statuses = TableStatus::where('branch_id', $branch->id)->orWhereNull('branch_id')->get();
        
        return view('branch.tableStatuses.index', [
            'company' => $company,
            'branch' => $branch,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return view('branch.tableStatuses.create', [
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
            'color' => 'required',
        ]);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $tableData = $request->only([
            'name',
            'color',
            'status',
            'is_blinking'
        ]);

        $tableData['branch_id'] = $branch->id;

        if (TableStatus::create($tableData)) {
            return redirect()->route('branch.table-statuses.index', [
                    'companySlug' => $request->attributes->get('company')->slug,
                    'branchSlug' => $request->attributes->get('branch')->slug
                ])->with('success', 'Table status created successfully.');
        }

        return redirect()->route('branch.table-statuses.index', [
                'companySlug' => $request->attributes->get('company')->slug,
                'branchSlug' => $request->attributes->get('branch')->slug
            ])->with('error', 'Table status failed to create.');
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
        $status = TableStatus::findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return view('branch.tableStatuses.edit', [
            'company' => $company,
            'branch' => $branch,
            'status' => $status,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $status = TableStatus::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'color' => 'required',
        ]);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $tableData = $request->only([
            'name',
            'color',
            'status',
            'is_blinking'
        ]);

        $tableData['is_blinking'] = $request->is_blinking ? 1 : 0;

        if ($status->update($tableData)) {
            return redirect()->route('branch.table-statuses.index', [
                    'companySlug' => $request->attributes->get('company')->slug,
                    'branchSlug' => $request->attributes->get('branch')->slug
                ])->with('success', 'Table status updated successfully.');
        }

        return redirect()->route('branch.table-statuses.index', [
                'companySlug' => $request->attributes->get('company')->slug,
                'branchSlug' => $request->attributes->get('branch')->slug
            ])->with('error', 'Table status failed to update.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
