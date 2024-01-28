<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\BankRepositoryInterface;

class BankController extends Controller
{
    protected $bankRepository;

    public function __construct(BankRepositoryInterface $bankRepository)
    {
        $this->bankRepository = $bankRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.banks.index', [
            'company' => $company
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.banks.create', [
            'company' => $company
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
            'description' => 'required',
            'status' => 'required',
        ]);

        $company = $request->attributes->get('company');

        if ($this->bankRepository->create($request->all())) {
            return redirect()->route('company.banks.index', ['companySlug' => $company->slug])->with('success', 'Bank created successfully.');
        }

        return redirect()->route('company.banks.index', ['companySlug' => $company->slug])->with('error', 'Bank failed to create.');
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
    public function edit(Request $request, string $companySlug, string $bankId)
    {
        $company = $request->attributes->get('company');
        $bank = $this->bankRepository->find($bankId);

        return view('company.banks.edit', [
            'company' => $company,
            'bank' => $bank
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $id)
    {
        $request->validate([
            'company_id' => 'required',
            'name' => 'required',
            'description' => 'required',
            'status' => 'required',
        ]);

        $company = $request->attributes->get('company');

        if ($this->bankRepository->update($id, $request->all())) {
            return redirect()->route('company.banks.index', ['companySlug' => $company->slug])->with('success', 'Bank updated successfully.');
        }

        return redirect()->route('company.banks.index', ['companySlug' => $company->slug])->with('error', 'Bank failed to update.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
