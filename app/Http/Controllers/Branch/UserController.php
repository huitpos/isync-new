<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\UserRepositoryInterface;

use App\DataTables\Branch\UsersDataTable;

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
        $branch = $request->attributes->get('branch');

        return $dataTable->with([
            'branch_id' => $branch->id,
            'branch_slug' => $branch->slug,
            'company_slug' => $company->slug,
        ])->render('branch.users.index', [
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

        return view('branch.users.create', [
            'company' => $company,
            'branch' => $branch,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $request->validate([
            'username' => 'required|unique:users,username',
            'password' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
        ]);

        $data = $request->all();
        $data['name'] = $data['first_name'] . ' ' . $data['last_name'];
        $data['company_id'] = $company->id;
        $data['branches'][] = $branch->id;
        $data['role'] = 'branch_user';

        if ($this->userRepository->create($data)) {
            return redirect()->route('branch.users.index', ['companySlug' => $request->attributes->get('company')->slug, 'branchSlug' => $request->attributes->get('branch')->slug])->with('success', 'User created successfully.');
        }

        return redirect()->route('branch.users.index', ['companySlug' => $request->attributes->get('company')->slug, 'branchSlug' => $request->attributes->get('branch')->slug])->with('error', 'User failed to create.');
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
    public function edit(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return redirect()->route('branch.users.index', ['companySlug' => $companySlug, 'branchSlug' => $branchSlug])->with('error', 'User not found.');
        }

        return view('branch.users.edit', [
            'user' => $user,
            'company' => $request->attributes->get('company'),
            'branch' => $request->attributes->get('branch'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $request->validate([
            'username' => 'required|unique:users,username,' . $id,
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $postData = $request->all();
        $postData['name'] = $postData['first_name'] . ' ' . $postData['last_name'];

        if (empty($postData['password'])) {
            unset($postData['password']);
        }

        if ($this->userRepository->update($id, $postData, false, false)) {
            return redirect()->route('branch.users.index', ['companySlug' => $companySlug, 'branchSlug' => $branchSlug])->with('success', 'User updated successfully.');
        }

        return redirect()->route('branch.users.index', ['companySlug' => $companySlug, 'branchSlug' => $branchSlug])->with('error', 'User failed to update.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
