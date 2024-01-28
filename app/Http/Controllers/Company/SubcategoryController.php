<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\SubcategoryRepositoryInterface;

class SubcategoryController extends Controller
{
    protected $subcategoryRepository;

    public function __construct(SubcategoryRepositoryInterface $subcategoryRepository)
    {
        $this->subcategoryRepository = $subcategoryRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.subcategories.index', compact('company'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.subcategories.create', compact('company'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required',
            'name' => 'required',
            'status' => 'required',
        ]);

        $company = $request->attributes->get('company');

        if ($subcategory = $this->subcategoryRepository->create($request->except('suppliers'))) {
            $this->subcategoryRepository->syncSuppliers($subcategory->id, $request->suppliers ?? []);

            return redirect()->route('company.subcategories.index', ['companySlug' => $company->slug])->with('success', 'Category created successfully.');
        }

        return redirect()->back()->with('error', 'Subcategory failed to create.');
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
    public function edit(Request $request, string $companySlug, $subcategoryId)
    {
        $company = $request->attributes->get('company');

        $subcategory = $this->subcategoryRepository->findOrFail($subcategoryId);

        return view('company.subcategories.edit', compact('company', 'subcategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $subcategoryId)
    {
        $request->validate([
            'company_id' => 'required',
            'name' => 'required',
            'status' => 'required',
        ]);

        $company = $request->attributes->get('company');

        if ($this->subcategoryRepository->update($subcategoryId, $request->except('suppliers'))) {
            $this->subcategoryRepository->syncSuppliers($subcategoryId, $request->suppliers ?? []);

            return redirect()->route('company.subcategories.index', ['companySlug' => $company->slug])->with('success', 'Subcategory updated successfully.');
        }

        return redirect()->back()->with('error', 'Category failed to update.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
