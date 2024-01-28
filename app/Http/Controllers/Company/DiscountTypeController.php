<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\DiscountTypeRepositoryInterface;

class DiscountTypeController extends Controller
{
    protected $discountTypeRepository;

    public function __construct(DiscountTypeRepositoryInterface $discountTypeRepository)
    {
        $this->discountTypeRepository = $discountTypeRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.discountTypes.index', [
            'company' => $company,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.discountTypes.create', [
            'company' => $company
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $company = $request->attributes->get('company');

        $request->validate([
            'company_id' => 'required',
            'name' => 'required',
            'description' => 'required',
            'type' => 'required',
            'discount' => 'required',
            'status' => 'required',
        ]);

        if ($this->discountTypeRepository->create($request->all())) {
            return redirect()->route('company.discount-types.index', ['companySlug' => $company->slug])->with('success', 'Discount type created successfully.');
        }

        return redirect()->route('company.discount-types.index', ['companySlug' => $company->slug])->with('error', 'Discount type failed to create.');
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
    public function edit(Request $request, string $companySlug, int $id)
    {
        $company = $request->attributes->get('company');

        $discountType = $this->discountTypeRepository->find($id);

        return view('company.discountTypes.edit', [
            'company' => $company,
            'discountType' => $discountType,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, int $id)
    {
        $company = $request->attributes->get('company');

        $request->validate([
            'company_id' => 'required',
            'name' => 'required',
            'description' => 'required',
            'type' => 'required',
            'discount' => 'required',
            'status' => 'required',
        ]);

        if ($this->discountTypeRepository->update($id, $request->all())) {
            return redirect()->route('company.discount-types.index', ['companySlug' => $company->slug])->with('success', 'Discount type updated successfully.');
        }

        return redirect()->route('company.discount-types.index', ['companySlug' => $company->slug])->with('error', 'Discount type failed to update.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $companySlug, int $id)
    {
        if ($this->discountTypeRepository->delete($id)) {
            return redirect()->route('company.discount-types.index', ['companySlug' => $companySlug])->with('success', 'Discount type deleted successfully.');
        }

        return redirect()->route('company.discount-types.index', ['companySlug' => $companySlug])->with('error', 'Discount type failed to delete.');
    }
}
