<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\CategoryRepositoryInterface;

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
    public function index(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.categories.index', compact('company'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.categories.create', compact('company'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required',
            'status' => 'required',
            'name' => 'required',
        ]);

        $company = $request->attributes->get('company');

        if ($category = $this->categoryRepository->create($request->except('suppliers'))) {
            $this->categoryRepository->syncSuppliers($category->id, $request->suppliers ?? []);

            return redirect()->route('company.categories.index', ['companySlug' => $company->slug])->with('success', 'Category created successfully.');
        }

        return redirect()->back()->with('error', 'Category failed to create.');
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
    public function edit(Request $request, string $companySlug, $categoryId)
    {
        $company = $request->attributes->get('company');

        $category = $this->categoryRepository->findOrFail($categoryId);

        return view('company.categories.edit', compact('company', 'category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $categoryId)
    {
        $request->validate([
            'company_id' => 'required',
            'status' => 'required',
            'name' => 'required',
        ]);

        $company = $request->attributes->get('company');

        if ($this->categoryRepository->update($categoryId, $request->except('suppliers'))) {
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
