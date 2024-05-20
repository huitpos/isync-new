<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Permission;
use Spatie\Permission\Models\Role;

use App\DataTables\Company\RolesDataTable;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, RolesDataTable $dataTable)
    {
        $company = $request->attributes->get('company');

        return $dataTable->with('company_id', $company->id)->render('company.roles.index', ['company' => $company]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        $companyPermissions = Permission::where([
            'parent_id' => null,
            'level' => 'company_user'
        ])
        ->with([
            'children'
        ])
        ->get();

    $branchPermissions = Permission::where([
            'parent_id' => null,
            'level' => 'branch_user'
        ])
        ->with([
            'children'
        ])
        ->get();

        return view('company.roles.create', [
            'companyPermissions' => $companyPermissions,
            'branchPermissions' => $branchPermissions,
            'company' => $company,
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

        $role = new Role();
        $role->name = $request->name;
        $role->company_id = $company->id;

        if ($role->save()) {
            $permissions = Permission::whereIn('id', $request->permission)->pluck('name');
            $role->givePermissionTo($permissions);

            return redirect()->route('company.roles.index', ['companySlug' => $company->slug])->with('success', 'Role created successfully.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug, string $id)
    {
        $role = Role::where('id', $id)
            ->with([
                'permissions'
            ]);

        dd($role);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $companySlug, string $id)
    {
        $role = Role::findOrFail($id);

        $company = $request->attributes->get('company');

        $companyPermissions = Permission::where([
                'parent_id' => null,
                'level' => 'company_user'
            ])
            ->with([
                'children'
            ])
            ->get();

        $branchPermissions = Permission::where([
                'parent_id' => null,
                'level' => 'branch_user'
            ])
            ->with([
                'children'
            ])
            ->get();

        return view('company.roles.edit', [
            'role' => $role,
            'company' => $company,
            'companyPermissions' => $companyPermissions,
            'branchPermissions' => $branchPermissions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $id)
    {
        $role = Role::findOrFail($id);

        $company = $request->attributes->get('company');

        $role->name = $request->name;

        if ($role->update(['name' => $request->name])) {
            $role->syncPermissions([]);

            $role->givePermissionTo($request->permission);

            return redirect()->back()->with('success', 'Role created successfully.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
