<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\SubcategoryRepositoryInterface;

use App\DataTables\Company\SubcategoriesDataTable;

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
    public function index(Request $request, SubcategoriesDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $permissions = $request->attributes->get('permissionNames');

        return $dataTable->with([
            'company_id' => $company->id,
            'permissions' => $permissions
        ])->render('company.subcategories.index', [
            'company' => $company,
            'permissions' => $permissions
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        $categories = $company->categories()->where('status', 'active')->get();

        return view('company.subcategories.create', compact('company', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'status' => 'required',
            'category_id' => 'required'
        ]);

        $company = $request->attributes->get('company');

        $postData = $request->all();
        $postData['company_id'] = $company->id;

        if ($subcategory = $this->subcategoryRepository->create($postData)) {
            $this->subcategoryRepository->syncSuppliers($subcategory->id, $request->suppliers ?? []);

            return redirect()->route('company.subcategories.index', ['companySlug' => $company->slug])->with('success', 'Category created successfully.');
        }

        return redirect()->back()->with('error', 'Subcategory failed to create.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');

        $subcategory = $this->subcategoryRepository->findOrFail($id);

        if ($subcategory->company_id != $company->id) {
            abort(404);
        }

        return view('company.subcategories.show', compact('company', 'subcategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $companySlug, $subcategoryId)
    {
        $company = $request->attributes->get('company');

        $subcategory = $this->subcategoryRepository->findOrFail($subcategoryId);

        $categories = $company->categories()->where('status', 'active')->get();

        return view('company.subcategories.edit', compact('company', 'subcategory', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $subcategoryId)
    {
        $request->validate([
            'name' => 'required',
            'status' => 'required',
        ]);

        $company = $request->attributes->get('company');

        $postData = $request->all();
        $postData['company_id'] = $company->id;

        if ($this->subcategoryRepository->update($subcategoryId, $postData)) {
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
