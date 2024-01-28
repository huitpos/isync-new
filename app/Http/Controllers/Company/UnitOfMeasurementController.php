<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\UnitOfMeasurementRepositoryInterface;

class UnitOfMeasurementController extends Controller
{
    protected $uomRepository;

    public function __construct(UnitOfMeasurementRepositoryInterface $uomRepository)
    {
        $this->uomRepository = $uomRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.unitOfMeasurements.index', compact('company'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.unitOfMeasurements.create', [
            'company' => $company,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required',
            'name' => 'required',
            'status' => 'required',
        ]);

        $company = $request->attributes->get('company');

        if ($this->uomRepository->create($request->all())) {
            return redirect()->route('company.unit-of-measurements.index', ['companySlug' => $company->slug])->with('success', 'Data has been stored successfully!');
        }

        return redirect()->back()->with('success', 'Data has been stored successfully!');
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
    public function edit(Request $request, $companySlug, $uomId)
    {
        $company = $request->attributes->get('company');
        $uom = $this->uomRepository->find($uomId);

        if (empty($uom)) {
            return redirect()->route('company.unit-of-measurements.index', ['companySlug' => $company->slug])->with('error', 'Data not found!');
        }

        return view('company.unitOfMeasurements.edit', [
            'company' => $company,
            'uom' => $uom,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, $uomId)
    {
        $request->validate([
            'company_id' => 'required',
            'name' => 'required',
            'status'
        ]);

        $company = $request->attributes->get('company');
        $uom = $this->uomRepository->find($uomId);

        if (empty($uom)) {
            return redirect()->route('company.unit-of-measurements.index', ['companySlug' => $company->slug])->with('error', 'Data not found!');
        }

        if ($this->uomRepository->update($uomId, $request->all())) {
            return redirect()->route('company.unit-of-measurements.index', ['companySlug' => $company->slug])->with('success', 'Data has been updated successfully!');
        }

        return redirect()->back()->with('success', 'Data has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
