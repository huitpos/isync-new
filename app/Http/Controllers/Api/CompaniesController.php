<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Company;

use Illuminate\Support\Str;


class CompaniesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Company::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'company_registered_name' => 'required|unique:companies,company_registered_name|max:100',
            'company_name' => 'required',
            'trade_name' => 'required|max:100',
            'phone_number' => 'max:10',
            'unit_floor_number' => 'max:100',
            'street_name' => 'max:100',
        ], [
            'client_id.exists' => 'The selected client ID does not exist.',
        ]);

        $data = $request->all();

        $data['slug'] = Str::slug($data['trade_name']);

        return Company::create($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Company::find($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $company = Company::find($id);
        $validatedData = $request->validate([
            'client_id' => 'required',
            'company_registered_name' => 'required|unique:companies,company_registered_name,' . $company->id,
            'company_name' => 'required',
            'trade_name' => 'required',
        ]);

        $data = $request->all();

        $data['slug'] = Str::slug($data['trade_name']);

        $company->update($data);
        return $company;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return Company::destroy($id);
    }
}
