<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\DataTables\Company\UsersDataTable;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, UsersDataTable $dataTable)
    {
        $company = $request->attributes->get('company');

        return $dataTable->with('company_id', $company->id)->render('company.users.index', ['company' => $company]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.users.create', ['company' => $company]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $company = $request->attributes->get('company');

        $request->validate([
            'username' => 'required|unique:users,username',
            'password' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'nullable|email|unique:users,email',
            'branches' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->role === 'branch_user';
                }),
            ],
        ], [
            'branches.required' => 'At least one branch is required for users with the role "Branch User".',
        ]);

        $postData = $request->all();
        $postData['company_id'] = $company->id;
        $postData['name'] = $postData['first_name'] . ' ' . $postData['last_name'];
        if ($this->userRepository->create($postData)) {
            return redirect()->route('company.users.index', ['companySlug' => $company->slug])->with('success', 'User created successfully.');
        }

        return redirect()->back()->with('error', 'User creation failed.');
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
    public function edit(Request $request, string $companySlug, string $id)
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return redirect()->route('company.users.index', ['companySlug' => $companySlug])->with('error', 'User not found.');
        }

        $company = $request->attributes->get('company');

        return view('company.users.edit', ['company' => $company, 'user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');

        $request->validate([
            'username' => 'required|unique:users,username,' . $id,
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'branches' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->role === 'branch_user';
                }),
            ],
        ], [
            'branches.required' => 'At least one branch is required for users with the role "Branch User".',
        ]);

        $postData = $request->all();
        $postData['company_id'] = $company->id;
        $postData['is_active'] = $request->is_active ?? false;
        $postData['name'] = $postData['first_name'] . ' ' . $postData['last_name'];

        if (empty($postData['password'])) {
            unset($postData['password']);
        }

        if ($this->userRepository->update($id, $postData)) {
            return redirect()->route('company.users.index', ['companySlug' => $company->slug])->with('success', 'User created successfully.');
        }

        return redirect()->back()->with('error', 'User creation failed.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //asdfasdfasdf
    }
}
