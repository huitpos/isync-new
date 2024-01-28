<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiService;
use Illuminate\Support\Str;

use App\Models\Region;
use App\Models\Branch;
use App\Models\City;
use App\Models\Province;
use App\Models\Barangay;

use App\Repositories\Interfaces\BranchRepositoryInterface;

class BranchController extends Controller
{
    protected $apiService;
    protected $branchRepository;

    public function __construct(
        ApiService $apiService,
        BranchRepositoryInterface $branchRepository
    ) {
        $this->apiService = $apiService;
        $this->branchRepository = $branchRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.branches.index', compact('company'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        $regions = Region::all();

        if ($request->old('region_id')) {
            $provinces = Province::where('region_id', $request->old('region_id'))->get();
        }

        if ($request->old('province_id')) {
            $cities = City::where('province_id', $request->old('province_id'))->get();
        }

        if ($request->old('city_id')) {
            $barangays = Barangay::where('city_id', $request->old('city_id'))->get();
        }

        return view('company.branches.create', [
            'regions' => $regions,
            'company' => $company,
            'provinces' => $provinces ?? [],
            'cities' => $cities ?? [],
            'barangays' => $barangays ?? [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required',
            'cluster_id' => 'required',
            'status' => 'required',
            'pos_type' => 'required',
            'name' => 'required',
            'code' => 'required',
            'location' => 'required',
            'unit_number' => 'required',
            'floor_number' => 'required',
            'street' => 'required',
            'zip' => 'required',
            'region_id' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'barangay_id' => 'required',
        ]);

        $company = $request->attributes->get('company');

        $data = $request->all();
        $data['slug'] = Str::slug($data['name']);

        if ($this->branchRepository->create($data)) {
            return redirect()->route('company.branches.index', ['companySlug' => $company->slug])->with('success', 'Data has been stored successfully!');
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
    public function edit(Request $request, $companySlug, $branchId)
    {
        $company = $request->attributes->get('company');

        $branch = Branch::findOrFail($branchId);

        $regions = Region::all();
        $provinces = Province::where('region_id', $branch->region_id)->get();
        $cities = City::where('province_id', $branch->province_id)->get();
        $barangays = Barangay::where('city_id', $branch->city_id)->get();


        return view('company.branches.edit', [
            'regions' => $regions,
            'company' => $company,
            'provinces' => $provinces ?? [],
            'cities' => $cities ?? [],
            'barangays' => $barangays ?? [],
            'branch' => $branch,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $companySlug, $branchId)
    {
        $branch = Branch::findOrFail($branchId);

        $validatedData = $request->validate([
            'company_id' => 'required',
            'cluster_id' => 'required',
            'status' => 'required',
            'pos_type' => 'required',
            'name' => 'required',
            'code' => 'required',
            'location' => 'required',
            'unit_number' => 'required',
            'floor_number' => 'required',
            'street' => 'required',
            'zip' => 'required',
            'region_id' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'barangay_id' => 'required',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($data['name']);

        if ($branch->update($data)) {
            return redirect()->back()->with('success', 'Data has been updated successfully!');
        }

        return redirect()->back()->with('error', 'Something went wrong. Please try again.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function fetchData()
    {
    }
}
