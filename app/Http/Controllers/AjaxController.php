<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;
use App\Models\Cluster;

use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\SubcategoryRepositoryInterface;

class AjaxController extends Controller
{
    protected $categoryRepository;
    protected $subcategoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository, SubcategoryRepositoryInterface $subcategoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->subcategoryRepository = $subcategoryRepository;
    }

    public function getProvinces(Request $request)
    {
        $provinces = Province::where('region_id', $request->region_id)->get();
        return response()->json($provinces);
    }

    public function getCities(Request $request)
    {
        $cities = City::where('province_id', $request->province_id)->get();
        return response()->json($cities);
    }

    public function getBarangays(Request $request)
    {
        $barangays = Barangay::where('city_id', $request->city_id)->get();
        return response()->json($barangays);
    }

    public function getClusters(Request $request)
    {
        $clusters = Cluster::where('company_id', $request->company_id)->get();
        return response()->json($clusters);
    }

    public function getDepartmentCategories(Request $request)
    {
        $categories = $this->categoryRepository->get([
            'department_id' => $request->department_id,
            'status' => 'active'
        ]);

        return response()->json($categories);
    }

    public function getCategorySubcategories(Request $request)
    {
        $subcategories = $this->subcategoryRepository->get([
            'category_id' => $request->category_id,
            'status' => 'active'
        ]);

        return response()->json($subcategories);
    }
}
