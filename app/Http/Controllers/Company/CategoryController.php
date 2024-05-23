<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\CategoryRepositoryInterface;

use App\DataTables\Company\CategoriesDataTable;

class CategoryController extends Controller
{
    protected $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, CategoriesDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $permissions = $request->attributes->get('permissionNames');

        return $dataTable->with([
            'company_id' => $company->id,
            'permissions' => $permissions,
        ])->render('company.categories.index', [
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
        $departments = $company->departments()->where('status', 'active')->get();
        $suppliers = $company->suppliers()->where('status', 'active')->get();

        return view('company.categories.create', compact('company', 'departments', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $company = $request->attributes->get('company');

        $request->validate([
            'status' => 'required',
            'name' => 'required',
            'department_id' => 'required',
        ]);

        $postData = $request->except('suppliers');

        $postData['company_id'] = $company->id;

        if ($category = $this->categoryRepository->create($postData)) {
            $this->categoryRepository->syncSuppliers($category->id, $request->suppliers ?? []);

            return redirect()->route('company.categories.index', ['companySlug' => $company->slug])->with('success', 'Category created successfully.');
        }

        return redirect()->back()->with('error', 'Category failed to create.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');

        $category = $this->categoryRepository->findOrFail($id);

        if ($category->company_id != $company->id) {
            abort(404);
        }

        return view('company.categories.show', compact('company', 'category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $companySlug, $categoryId)
    {
        $company = $request->attributes->get('company');

        $category = $this->categoryRepository->findOrFail($categoryId);

        $departments = $company->departments()->where('status', 'active')->get();
        $suppliers = $company->suppliers()->where('status', 'active')->get();

        return view('company.categories.edit', compact('company', 'category', 'departments', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $categoryId)
    {
        $request->validate([
            'status' => 'required',
            'name' => 'required',
        ]);

        $company = $request->attributes->get('company');

        $postData = $request->except('suppliers');
        $postData['company_id'] = $company->id;

        if ($this->categoryRepository->update($categoryId, $postData)) {
            $this->categoryRepository->syncSuppliers($categoryId, $request->suppliers ?? []);

            return redirect()->route('company.categories.index', ['companySlug' => $company->slug])->with('success', 'Category updated successfully.');
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
