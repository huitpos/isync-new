<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Company;
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;

use Illuminate\Support\Str;

use App\Repositories\Interfaces\ClientRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\CompanyRepositoryInterface;

use Illuminate\Support\Facades\File;

class CompanyController extends Controller
{
    protected $clientRepository;
    protected $userRepository;
    protected $companyRepository;

    public function __construct(
        ClientRepositoryInterface $clientRepository,
        UserRepositoryInterface $userRepository,
        CompanyRepositoryInterface $companyRepository
    ) {
        $this->clientRepository = $clientRepository;
        $this->userRepository = $userRepository;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = Company::all();

        return view('admin.companies.index', [
            'companies' => $companies
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
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

        return view('admin.companies.create', [
            'regions' => $regions,
            'provinces' => $provinces,
            'cities' => $cities,
            'barangays' => $barangays,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'company_registered_name' => 'required|unique:companies,company_registered_name|max:100',
            'company_name' => 'required',
            'trade_name' => 'required|max:100',
            'phone_number' => 'required|max:10',
            'unit_floor_number' => 'required|max:100',
            'region_id' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'barangay_id' => 'required',
            'logo' => 'required',
        ]);

        $client = $this->clientRepository->create(
            [
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'telephone_number' => $request['phone_number'],
                'unit_number' => $request['unit_floor_number'],
                'floor_number' => $request['unit_floor_number'],
                'city' => $request['city_id'],
                'province' => $request['province_id'],
                'uuid' => Str::uuid(),
            ]
        );

        $base64Image = $request->input('logo');
        $image = json_decode($base64Image);
        $imageData = base64_decode($image->data);
        $companyName = Str::slug($request['trade_name']);

        // Define the directory path based on the company name
        $directoryPath = public_path('images/' . $companyName);

        $fileName = uniqid() . '.' . $image->name;

        $filePath = $directoryPath . '/' . $fileName;
        $shortFilePath = '/images/' . $companyName . '/' . $fileName;

        // Check if the directory exists, if not, create it recursively
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0777, true); // Recursive directory creation
        }

        file_put_contents($filePath, $imageData);

        $company = $this->companyRepository->create([
            'client_id' => $client->id,
            'company_registered_name' => $request['company_registered_name'],
            'company_name' => $request['company_name'],
            'trade_name' => $request['trade_name'],
            'phone_number' => $request['phone_number'],
            'unit_floor_number' => $request['unit_floor_number'],
            'logo' => $shortFilePath,
            'country' => $request['country'],
            'region_id' => $request['region_id'],
            'province_id' => $request['province_id'],
            'city_id' => $request['city_id'],
            'barangay_id' => $request['barangay_id'],
            'slug' => Str::slug($request['trade_name'])
        ]);

        $this->userRepository->create([
            'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'client_id' => $client->id,
            'company_id' => $company->id
        ]);

        return redirect()->route('admin.companies.index')->with('success', 'Data has been stored successfully!');
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
        if ($request->ajax()) {
            $company = $this->companyRepository->find($id);

            if ($company) {
                $company->status = $request->status;
                $company->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Company status updated successfully.'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Company not found.'
            ]);
        }

        $request->validate([
            'company_id' => 'required',
            'name' => 'required',
            'description' => 'required',
            'status' => 'required',
        ]);

        $company = $request->attributes->get('company');

        // if ($this->bankRepository->update($id, $request->all())) {
        //     return redirect()->route('company.banks.index', ['companySlug' => $company->slug])->with('success', 'Bank updated successfully.');
        // }

        // return redirect()->route('company.banks.index', ['companySlug' => $company->slug])->with('error', 'Bank failed to update.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
