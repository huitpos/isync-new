<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Region;
use App\Models\City;
use App\Models\Barangay;
use App\Models\Province;
use App\Models\ItemLocation;

use App\DataTables\Company\ItemLocationsDataTable;

class ItemLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ItemLocationsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $permissions = $request->attributes->get('permissionNames');

        return $dataTable->with([
            'company_id' => $company->id,
            'permissions' => $permissions
        ])->render('company.itemLocations.index', [
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

        $regions = Region::all();
        $provinces = [];
        $cities = [];
        $barangays = [];

        if ($request->old('region_id')) {
            $provinces = Province::where('region_id', $request->old('region_id'))->get();
        }

        if ($request->old('province_id')) {
            $cities = City::where('province_id', $request->old('province_id'))->get();
        }

        if ($request->old('city_id')) {
            $barangays = Barangay::where('city_id', $request->old('city_id'))->get();
        }

        return view('company.itemLocations.create', compact('company', 'regions', 'provinces', 'cities', 'barangays'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'unit_floor_number' => 'required',
            'street' => 'required',
            'region_id' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'barangay_id' => 'required',
        ]);

        $company = $request->attributes->get('company');

        $locationData = $request->only([
            'name',
            'unit_floor_number',
            'street',
            'region_id',
            'province_id',
            'city_id',
            'barangay_id',
            'phone_number',
            'status',
        ]);

        $locationData['company_id'] = $company->id;

        if (ItemLocation::create($locationData)) {
            return redirect()->route('company.item-locations.index', [
                    'companySlug' => $request->attributes->get('company')->slug
                ])->with('success', 'Delivery location created successfully.');
        }

        return redirect()->route('company.item-locations.index', [
                'companySlug' => $request->attributes->get('company')->slug
            ])->with('error', 'Delivery location failed to create.');
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
    public function edit(Request $request, string $companybranchSlug, string $id)
    {
        $location = ItemLocation::findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $regions = Region::all();
        $provinces = Province::where('region_id', $location->region_id)->get();
        $cities = City::where('province_id', $location->province_id)->get();
        $barangays = Barangay::where('city_id', $location->city_id)->get();

        return view('company.itemLocations.edit', compact('company', 'branch', 'regions', 'provinces', 'cities', 'barangays', 'location'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companybranchSlug, string $id)
    {
        $location = ItemLocation::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'unit_floor_number' => 'required',
            'street' => 'required',
            'region_id' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'barangay_id' => 'required',
        ]);

        $locationData = $request->only([
            'name',
            'unit_floor_number',
            'street',
            'region_id',
            'province_id',
            'city_id',
            'barangay_id',
            'phone_number',
            'status',
        ]);

        if ($location->update($locationData)) {
            return redirect()->route('company.item-locations.index', [
                    'companySlug' => $request->attributes->get('company')->slug,
                ])->with('success', 'Delivery location created successfully.');
        }

        return redirect()->route('company.item-locations.index', [
                'companySlug' => $request->attributes->get('company')->slug,
            ])->with('error', 'Delivery location failed to create.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
