<?php

namespace App\Imports;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Queue\ShouldQueue;


use App\Models\UnitOfMeasurement;
use App\Models\Department;
use App\Models\Category;
use App\Models\ItemLocation;
use App\Models\Subcategory;
use App\Models\ItemType;
use App\Models\Product;

use App\Jobs\UpdateOrCreateProductJob;

use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithColumnLimit;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductsImport implements ToCollection,
    WithValidation,
    WithStartRow,
    WithCalculatedFormulas,
    // WithLimit,
    WithColumnLimit,
    ShouldQueue,
    WithChunkReading,
    SkipsOnFailure
{
    protected $companyId;
    protected $userId;
    protected $data = [];

    public function __construct(int $companyId, $userId)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');

        $this->companyId = $companyId;
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    public function limit(): int
    {
        return 500;
    }

    public function endColumn(): string
    {
        return 'T';
    }

    public function collection(Collection $rows)
    {
        $lastNumber = Product::where('company_id', $this->companyId)->max('code'); 

        $units = UnitOfMeasurement::where('company_id', $this->companyId)->get()->pluck('name', 'id')->toArray();
        $units = array_map('strtolower', $units);

        $departments = Department::where('company_id', $this->companyId)->get()->pluck('name', 'id')->toArray();
        $departments = array_map('strtolower', $departments);

        $categories = Category::where('company_id', $this->companyId)->get()->pluck('name', 'id')->toArray();
        $categories = array_map('strtolower', $categories);

        $subcategoriesData = Subcategory::where('company_id', $this->companyId)
            ->select('id', 'name', 'category_id')
            ->get();

        $subcategoriesMap = [];
        foreach ($subcategoriesData as $subcategory) {
            $key = strtolower($subcategory->name) . '_' . $subcategory->category_id;
            $subcategoriesMap[$key] = $subcategory->id;
        }

        $subcategories = Subcategory::where('company_id', $this->companyId)->get()->pluck('name', 'id')->toArray();
        $subcategories = array_map('strtolower', $subcategories);

        $itemTypes = ItemType::where('company_id', $this->companyId)->get()->pluck('name', 'id')->toArray();
        $itemTypes = array_map('strtolower', $itemTypes);

        $itemLocations = ItemLocation::where('company_id', $this->companyId)->get()->pluck('name', 'id')->toArray();
        $itemLocations = array_map('strtolower', $itemLocations);

        foreach ($rows as $key => $row) {
            $lastNumber++;
            
            $categoryId = array_search(strtolower($row[8]), $categories) ?? null;
            
            $lookupKey = strtolower($row[9]) . '_' . $categoryId;
            $subCategoryId = $subcategoriesMap[$lookupKey] ?? array_search(strtolower($row[9]), $subcategories) ?? null;

            $productData = [
                'status' => $row[0], //A
                'name' => $row[1], //B
                'description' => $row[2], //C
                'sku' => $row[3], //D
                'abbreviation' => $row[4], //E
                'uom_id' => array_search(strtolower($row[5]), $units), //F
                'barcode' => $row[6], //G
                'department_id' => array_search(strtolower($row[7]), $departments) ?? null, //H
                'category_id' => $categoryId, //I
                'subcategory_id' => $subCategoryId ?? null, //J
                'markup_type' => $row[10], //K
                'markup' => $row[11], //L
                'cost' => $row[12], //M
                'srp' => $row[13], //N
                'item_type_id' => array_search(strtolower($row[14]), $itemTypes), //O
                'item_locations' => array_search(strtolower($row[15]), $itemLocations), //P
                'max_discount' => $row[16] ?? 0, //Q
                'minimum_stock_level' => $row[17] ?? 0, //R
                'maximum_stock_level' => $row[18] ?? 0, //S
                'part_number' => $row[19] ?? null, //T

                'company_id' => $this->companyId,
                'code' => $lastNumber,

                'created_by' => $this->userId,
            ];

            $this->data[] = $productData;

            $importItemLocations = $productData['item_locations'];
            unset($productData['item_locations']);


            UpdateOrCreateProductJob::dispatch($productData, $importItemLocations);
        }
    }

    public function rules(): array
    {
        $units = UnitOfMeasurement::where('company_id', $this->companyId)->get()->pluck('name', 'id')->toArray();
        $units = array_map('strtolower', $units);

        $departments = Department::where('company_id', $this->companyId)->get()->pluck('name', 'id')->toArray();
        $departments = array_map('strtolower', $departments);

        $categories = Category::where('company_id', $this->companyId)->get()->pluck('name', 'id')->toArray();
        $categories = array_map('strtolower', $categories);

        $subcategories = Subcategory::where('company_id', $this->companyId)->get()->pluck('name', 'id')->toArray();
        $subcategories = array_map('strtolower', $subcategories);

        $itemTypes = ItemType::where('company_id', $this->companyId)->get()->pluck('name', 'id')->toArray();
        $itemTypes = array_map('strtolower', $itemTypes);

        return [
            '*.0' => [
                // 'required',
                Rule::in(['active', 'inactive']),
            ],
            '*.1' => [
                'required',
                'distinct',
            ],
            '*.2' => [
                // 'required',
            ],
            '*.3' => [
                // 'required',
                // 'distinct',
                // 'unique:products,sku,NULL,id,company_id,' . $this->companyId,
            ],
            '*.4' => [
                // 'required',
            ],
            '*.5' => [
                // 'required',
                function ($attribute, $value, $fail) use($units) {
                    if (!array_search(strtolower($value), $units)) {
                        // $fail('UOM does not exists');
                    }
                },
            ],
            '*.6' => [
                // 'required',
                // 'distinct',
                // 'unique:products,barcode,NULL,id,company_id,' . $this->companyId,
            ],
            '*.7' => [
                // 'required',
                function ($attribute, $value, $fail) use($departments) {
                    if (!array_search(strtolower($value), $departments)) {
                        // $fail('Department does not exists');
                    }
                },
            ],
            '*.8' => [
                // 'required',
                function ($attribute, $value, $fail) use($categories) {
                    if (!array_search(strtolower($value), $categories)) {
                        // $fail('Category does not exists');
                    }
                },
            ],
            '*.9' => [
                // 'required',
                function ($attribute, $value, $fail) use($subcategories) {
                    if (!array_search(strtolower($value), $subcategories)) {
                        // $fail('Subcategory does not exists');
                    }
                },
            ],
            '*.10' => [
                // 'required',
                // Rule::in(['fixed', 'percentage']),
            ],
            '*.11' => [
                // 'required',
                // 'numeric'
            ],
            '*.12' => [
                // 'required',
                // 'numeric'
            ],
            '*.13' => [
                // 'required',
                function ($attribute, $value, $fail) use($itemTypes) {
                    if (!array_search(strtolower($value), $itemTypes)) {
                        // $fail('Item Type does not exists');
                    }
                },
            ],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.0.required' => 'Status is required.',
            '*.0.in' => 'The status must be either "inactive" or "active".',

            '*.1.required' => 'Product Name is required.',
            '*.1.unique' => 'Product Name already taken.',
            '*.1.distinct' => 'Duplicate Product Name',

            '*.2.required' => 'Description is required.',

            '*.3.required' => 'SKU is required.',
            '*.3.unique' => 'SKU already taken.',
            '*.3.distinct' => 'Duplicate SKU',

            '*.4.required' => 'Item Abbreviation is required.',

            '*.5.required' => 'UOM is required.',
            '*.5.exists' => 'UOM does not exists.',

            '*.6.required' => 'Barcode is required.',
            '*.6.distinct' => 'Duplicate Barcode',
            '*.6.unique' => 'Barcode already taken',

            '*.7.required' => 'Department is required.',
            '*.7.exists' => 'Department does not exists.',

            '*.8.required' => 'Category is required.',
            '*.8.exists' => 'Category does not exists.',

            '*.9.required' => 'Subcategory is required.',
            '*.9.exists' => 'Subcategory does not exists.',

            '*.10.required' => 'Markup option is required.',
            '*.10.in' => 'The Markup option must be either "fixed" or "percentage".',

            '*.11.required' => 'Markup is required.',
            '*.11.numeric' => 'Markup must be a number.',

            '*.12.required' => 'Cost is required.',
            '*.12.numeric' => 'Cost must be a number.',

            '*.13.required' => 'Item Type is required.',
            '*.13.exists' => 'Item Type does not exists.',
        ];
    }

    public function getData()
    {
        return $this->data;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            echo 'Excel import failure: ' . $failure->values()[1] . ' at row ' . $failure->row() . ' in column ' . $failure->attribute() . ': ' . $failure->errors()[0] . PHP_EOL;
        }
    }
}