<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Printer;

class PrinterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        //get TableLocation by branch_id
        $printers = Printer::where('branch_id', $branch->id)->get();
        
        return view('branch.printers.index', [
            'company' => $company,
            'branch' => $branch,
            'printers' => $printers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $departments = $company->departments;

        return view('branch.printers.create', [
            'company' => $company,
            'branch' => $branch,
            'departments' => $departments,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $tableData = $request->only([
            'name',
            'status'
        ]);

        $departmentData = $request->only([
            'departments'
        ]);

        $tableData['branch_id'] = $branch->id;

        if ($printer = Printer::create($tableData)) {
            $printer->departments()->sync($request->departments);

            return redirect()->route('branch.printers.index', [
                    'companySlug' => $request->attributes->get('company')->slug,
                    'branchSlug' => $request->attributes->get('branch')->slug
                ])->with('success', 'Printer created successfully.');
        }

        return redirect()->route('branch.printers.index', [
                'companySlug' => $request->attributes->get('company')->slug,
                'branchSlug' => $request->attributes->get('branch')->slug
            ])->with('error', 'Printer failed to create.');
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
        $printer = Printer::findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $departments = $company->departments;

        return view('branch.printers.edit', [
            'company' => $company,
            'branch' => $branch,
            'printer' => $printer,
            'departments' => $departments,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $printer = Printer::findOrFail($id);

        $request->validate([
            'name' => 'required'
        ]);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $tableData = $request->only([
            'name',
            'status'
        ]);

        $departmentData = $request->only([
            'departments'
        ]);

        $tableData['branch_id'] = $branch->id;

        if ($printer->update($tableData)) {
            $printer->departments()->sync($request->departments);

            return redirect()->route('branch.printers.index', [
                    'companySlug' => $request->attributes->get('company')->slug,
                    'branchSlug' => $request->attributes->get('branch')->slug
                ])->with('success', 'Printer updated successfully.');
        }

        return redirect()->route('branch.printers.index', [
                'companySlug' => $request->attributes->get('company')->slug,
                'branchSlug' => $request->attributes->get('branch')->slug
            ])->with('error', 'Printer failed to update.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
