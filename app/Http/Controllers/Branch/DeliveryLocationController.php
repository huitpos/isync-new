<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Region;
use App\Models\City;
use App\Models\Barangay;
use App\Models\Province;
use App\Models\DeliveryLocation;

use App\DataTables\Branch\DeliveryLocationsDataTable;

class DeliveryLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DeliveryLocationsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return $dataTable->with([
            'branch_id' => $branch->id,
            'branch_slug' => $branch->slug,
            'company_slug' => $company->slug,
        ])->render('branch.deliveryLocations.index', [
            'company' => $company,
            'branch' => $branch,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

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

        return view('branch.deliveryLocations.create', compact('company', 'branch', 'regions', 'provinces', 'cities', 'barangays'));
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

        $branch = $request->attributes->get('branch');

        $locationData = $request->only([
            'name',
            'unit_floor_number',
            'street',
            'region_id',
            'province_id',
            'city_id',
            'barangay_id',
            'phone_number',
        ]);

        $locationData['branch_id'] = $branch->id;

        if (DeliveryLocation::create($locationData)) {
            return redirect()->route('branch.delivery-locations.index', [
                    'companySlug' => $request->attributes->get('company')->slug,
                    'branchSlug' => $request->attributes->get('branch')->slug
                ])->with('success', 'Delivery location created successfully.');
        }

        return redirect()->route('branch.delivery-locations.index', [
                'companySlug' => $request->attributes->get('company')->slug,
                'branchSlug' => $request->attributes->get('branch')->slug
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
