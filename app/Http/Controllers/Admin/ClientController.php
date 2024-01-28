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
        // $file = $request->file('avatar');


        // $path = Storage::disk('s3')->put('avatars', $file, 'public');

        // dd($path);

        // $url = Storage::disk('s3')->url($path);


        // dd($url);


        // $path = $request->file('avatar')->store(
        //     'companies/logos/'.uniqid(), 's3'
        // );

        // dd($path);

        // dd($request->file());
        // $path = $request->file('avatar')->store('avatars');

        // // $filePath = 'avatars/' . $path;

        // $fileUrl = Storage::url($path);

        // dd(asset($fileUrl));
        $validatedData = $request->validate([
            'owner_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
            'company_name' => 'required|unique:companies,company_name|max:100',
            'trade_name' => 'required|unique:companies,trade_name|max:100',
            'phone_number' => 'required|max:10',
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

        $shortFilePath = '';

        if ($request->input('logo')) {
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
        }

        $this->companyRepository->create([
            'client_id' => $client->id,
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
            'slug' => Str::slug($request['trade_name']),
            'street' => Str::slug($request['street']),
            'company_registered_name' => ''
        ]);

        $this->userRepository->create([
            'name' => $validatedData['owner_name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'client_id' => $client->id,
        ]);

        return redirect()->route('admin.clients.index')->with('success', 'Data has been stored successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $company = $this->companyRepository->find($id);

        return view('admin.clients.show', [
            'company' => $company,
        ]);
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
        dd($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
