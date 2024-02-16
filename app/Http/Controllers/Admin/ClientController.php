<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\Company;
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;

use App\Repositories\Interfaces\ClientRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\CompanyRepositoryInterface;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use App\DataTables\Admin\ClientsDataTable;
use App\DataTables\Admin\CompanyBranchDataTable;

class ClientController extends Controller
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
    public function index(ClientsDataTable $dataTable)
    {
        return $dataTable->render('admin.clients.index');
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

        return view('admin.clients.create', [
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
            'owner_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
            'company_name' => 'required|unique:companies,company_name|max:100',
            'trade_name' => 'required|unique:companies,trade_name|max:100',
            'phone_number' => 'required',
            'unit_floor_number' => 'required|max:100',
            'region_id' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'barangay_id' => 'required',
            'street' => 'required',
            'pos_type' => 'required'
        ]);

        $client = $this->clientRepository->create(
            [
                'name' => $validatedData['owner_name'],
                'telephone_number' => $request['phone_number'],
                'unit_number' => $request['unit_floor_number'],
                'floor_number' => $request['unit_floor_number'],
                'city' => $request['city_id'],
                'province' => $request['province_id'],
                'uuid' => Str::uuid(),
            ]
        );

        $company = $this->companyRepository->create([
            'client_id' => $client->id,
            'company_name' => $request['company_name'],
            'trade_name' => $request['trade_name'],
            'phone_number' => $request['phone_number'],
            'unit_floor_number' => $request['unit_floor_number'],
            'logo' => '',
            'country' => $request['country'],
            'region_id' => $request['region_id'],
            'province_id' => $request['province_id'],
            'city_id' => $request['city_id'],
            'barangay_id' => $request['barangay_id'],
            'slug' => Str::slug($request['trade_name']),
            'street' => $request['street'],
            'company_registered_name' => ''
        ]);

        $path = '';

        if ($file = $request->file('logo')) {
            $customFileName = 'company_logo_' . $company->id . '.' . $file->extension();
            $path = Storage::disk('s3')->putFileAs('company_logos', $file, $customFileName, 'public');

            $this->companyRepository->update($company->id, [
                'logo' => $path,
            ]);
        }

        $user = $this->userRepository->create([
            'name' => $validatedData['owner_name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'client_id' => $client->id,
            'role' => 'company_admin'
        ]);

        return redirect()->route('admin.clients.index')->with('success', 'Data has been stored successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(CompanyBranchDataTable $dataTable, string $id)
    {
        $company = $this->companyRepository->find($id);

        if (!$company) {
            return abort(404, 'Company not found');
        }

        return $dataTable->with('company_id', $company->id)->render('admin.clients.show', [
            'company' => $company,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $company = $this->companyRepository->find($id);

        if (!$company) {
            return abort(404, 'Company not found');
        }

        $regions = Region::all();
        $provinces = Province::where('region_id', $company->region_id)->get();;
        $cities = City::where('province_id', $company->province_id)->get();
        $barangays = Barangay::where('city_id', $company->city_id)->get();

        if ($request->old('region_id')) {
            $provinces = Province::where('region_id', $request->old('region_id'))->get();
        }

        if ($request->old('province_id')) {
            $cities = City::where('province_id', $request->old('province_id'))->get();
        }

        if ($request->old('city_id')) {
            $barangays = Barangay::where('city_id', $request->old('city_id'))->get();
        }

        return view('admin.clients.edit', [
            'company' => $company,
            'regions' => $regions,
            'provinces' => $provinces,
            'cities' => $cities,
            'barangays' => $barangays,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $company = $this->companyRepository->find($id);

        if (empty($request->password)) {
            $request->request->remove('password');
            $request->request->remove('password_confirmation');
        }

        $validatedData = $request->validate([
            'owner_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $company->client->user->id,
            'password' => 'sometimes|min:6',
            'password_confirmation' => 'sometimes|same:password',
            'company_name' => 'required|max:100|unique:companies,company_name,' . $id,
            'trade_name' => 'required|max:100|unique:companies,trade_name,' . $id,
            'phone_number' => 'required',
            'unit_floor_number' => 'required|max:100',
            'region_id' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'barangay_id' => 'required',
            'street' => 'required',
            'pos_type' => 'required'
        ]);

        $this->clientRepository->update($company->client->id, [
            'name' => $validatedData['owner_name'],
            'telephone_number' => $request['phone_number'],
            'unit_number' => $request['unit_floor_number'],
            'floor_number' => $request['unit_floor_number'],
            'city' => $request['city_id'],
            'province' => $request['province_id'],
        ]);

        $path = '';
        if ($file = $request->file('logo')) {
            $customFileName = 'company_logo_' . $id . '.' . $file->extension();
            $path = Storage::disk('s3')->putFileAs('company_logos', $file, $customFileName, 'public');
        }

        $this->companyRepository->update($id, [
            'status' => $request['status'],
            'company_name' => $request['company_name'],
            'trade_name' => $request['trade_name'],
            'phone_number' => $request['phone_number'],
            'unit_floor_number' => $request['unit_floor_number'],
            'logo' => $path,
            'country' => $request['country'],
            'region_id' => $request['region_id'],
            'province_id' => $request['province_id'],
            'city_id' => $request['city_id'],
            'barangay_id' => $request['barangay_id'],
            'slug' => Str::slug($request['trade_name']),
            'street' => $request['street'],
            'company_registered_name' => ''
        ]);

        $userData = [
            'name' => $validatedData['owner_name'],
            'email' => $validatedData['email'],
        ];

        if (!empty($validatedData['password'])) {
            $userData['password'] = bcrypt($validatedData['password']);
        }

        $this->userRepository->update($company->client->user->id, $userData, false, false);

        return redirect()->route('admin.clients.index')->with('success', 'Data has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}