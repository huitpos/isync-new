<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Company;
use App\Models\Branch;
use App\Models\Region;
use App\Models\City;
use App\Models\Barangay;
use App\Models\Province;

use Illuminate\Support\Str;

use App\Repositories\Interfaces\BranchRepositoryInterface;
use App\Repositories\Interfaces\CompanyRepositoryInterface;

class BranchController extends Controller
{
    protected $branchRepository;
    protected $companyRepository;

    public function __construct(
        BranchRepositoryInterface $branchRepository,
        CompanyRepositoryInterface $companyRepository
    ) {
        $this->branchRepository = $branchRepository;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = $this->branchRepository->all();

        return view('admin.branches.index', [
            'branches' => $branches
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $regions = Region::all();
        $companies = $this->companyRepository->all();

        if ($request->old('region_id')) {
            $provinces = Province::where('region_id', $request->old('region_id'))->get();
        }

        if ($request->old('province_id')) {
            $cities = City::where('province_id', $request->old('province_id'))->get();
        }

        if ($request->old('city_id')) {
            $barangays = Barangay::where('city_id', $request->old('city_id'))->get();
        }

        return view('admin.branches.create', [
            'companies' => $companies,
            'regions' => $regions,
            'provinces' => $provinces ?? [],
            'cities' => $cities ?? [],
            'barangays' => $barangays ?? []
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
            'unit_floor_number' => 'required',
            'street' => 'required',
            'region_id' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'barangay_id' => 'required',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($data['name']);

        if ($this->branchRepository->create($data)) {
            return redirect()->route('admin.branches.index')->with('success', 'Data has been updated successfully!');
        } else {
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $branch = $this->branchRepository->find($id);

        if (!$branch) {
            return abort(404, 'Branch not found');
        }

        return view('admin.branches.show', [
            'branch' => $branch
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $branchId)
    {
        $branch = Branch::find($branchId);

        if (!$branch) {
            return abort(404, 'Brach not found');
        }

        $regions = Region::all();
        $provinces = Province::where('region_id', $branch->region_id)->get();
        $cities = City::where('province_id', $branch->province_id)->get();
        $barangays = Barangay::where('city_id', $branch->city_id)->get();

        return view('admin.branches.edit', [
            'branch' => $branch,
            'company' => $company,
            'clusters' => $company->clusters,
            'regions' => $regions,
            'provinces' => $provinces,
            'cities' => $cities,
            'barangays' => $barangays
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $companyId, $branchId)
    {
        $branch = Branch::find($branchId);

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

        $data = $request->all();
        $data['slug'] = Str::slug($data['name']);

        if ($this->branchRepository->update($branchId, $data)) {
            return redirect()->route('admin.branches.index', ['companyId' => $companyId])->with('success', 'Data has been updated successfully!');
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
}
