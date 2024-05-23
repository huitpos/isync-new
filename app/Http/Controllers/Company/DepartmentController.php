<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\DepartmentRepositoryInterface;

use App\DataTables\Company\DepartmentsDataTable;

class DepartmentController extends Controller
{
    protected $departmentRepository;

    public function __construct(
        DepartmentRepositoryInterface $departmentRepository
    ) {
        $this->departmentRepository = $departmentRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DepartmentsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $permissions = $request->attributes->get('permissionNames');

        return $dataTable->with([
            'company_id' => $company->id,
            'permissions' => $permissions
        ])->render('company.departments.index', [
            'company' => $company,
            'permissions' => $permissions
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.departments.create', [
            'company' => $company
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $company = $request->attributes->get('company');

        $request->validate([
            'status' => 'required',
            'name' => 'required',
            'description' => 'required',
        ]);

        $data = $request->except('suppliers');
        $data['company_id'] = $company->id;

        if ($department = $this->departmentRepository->create($data)) {
            $this->departmentRepository->syncSuppliers($department->id, $request->suppliers ?? []);

            return redirect()->route('company.departments.index', ['companySlug' => $company->slug])->with('success', 'Data has been stored successfully!');
        }

        return redirect()->back()->with('error', 'Data not stored.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $companySlug, string $id)
    {
        $department = $this->departmentRepository->findOrFail($id);

        if (!$department) {
            return abort(404, 'Department not found.');
        }

        return view('company.departments.show', [
            'department' => $department
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $companySlug, string $departmentId)
    {
        $company = $request->attributes->get('company');

        $department = $this->departmentRepository->findOrFail($departmentId);

        return view('company.departments.edit', [
            'company' => $company,
            'department' => $department
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $departmentId)
    {
        $company = $request->attributes->get('company');

        $request->validate([
            'status' => 'required',
            'name' => 'required',
            'description' => 'required',
        ]);

        $data = $request->except('suppliers');

        $data['company_id'] = $company->id;

        if ($this->departmentRepository->update($departmentId, $data)) {
            $this->departmentRepository->syncSuppliers($departmentId, $request->suppliers ?? []);

            return redirect()->route('company.departments.index', ['companySlug' => $company->slug])->with('success', 'Data has been updated successfully!');
        }

        return redirect()->back()->with('error', 'Data not stored.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
