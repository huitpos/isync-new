<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;
use App\Models\Cluster;
use App\Models\Product;
use App\Models\Department;
use App\Models\UnitOfMeasurement;

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

    public function getDepartmentSuppliers(Request $request)
    {
        $department = Department::find($request->department_id);

        $suppliers = $department->suppliers()->where([
            'status' => 'active'
        ])->get();

        return response()->json($suppliers);
    }

    public function getCategorySubcategories(Request $request)
    {
        $subcategories = $this->subcategoryRepository->get([
            'category_id' => $request->category_id,
            'status' => 'active'
        ]);

        return response()->json($subcategories);
    }

    public function getProducts(Request $request)
    {
        $productsQuery = Product::query();

        if ($request->has('department_id')) {
            $productsQuery->where('department_id', $request->department_id);
        }

        if ($request->has('term')) {
            $term = '%' . $request->term . '%'; // Add wildcard % before and after the search term
            $productsQuery->where('name', 'like', $term);
        }

        $products = $productsQuery->get();

        $responseData = [];
        foreach ($products as $product) {
            $responseData[] = [
                'id' => $product->id,
                'text' => $product->name
            ];
        }

        return response()->json(['results' => $responseData]);
    }

    public function getProductUoms(Request $request)
    {
        $product = Product::find($request->product_id);

        $uom = $product->uom;

        $responseData[] = [
            'id' => $uom->id,
            'text' => $uom->name
        ];

        return response()->json($responseData);
    }

    public function getProductDetails()
    {
        $product = Product::find(request()->product_id);

        return response()->json($product);
    }

    public function getUomConversions(Request $request)
    {
        $uom = UnitOfMeasurement::find($request->uom_id);
        $conversions = $uom->conversionsTo;

        $responseData[] = [
            'id' => $uom->id,
            'text' => $uom->name
        ];
        foreach ($conversions as $conversion) {
            $responseData[] = [
                'id' => $conversion->from_unit_id,
                'text' => $conversion->fromUnit->name. ' (' . $conversion->value . ' ' . $uom->name . ')'
            ];
        }

        return response()->json($responseData);
    }
}
