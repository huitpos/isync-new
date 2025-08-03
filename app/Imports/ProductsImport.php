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
    protected $lookupCache = [];

    public function __construct(int $companyId, $userId)
    {
        ini_set('memory_limit', '256M'); // Lower memory limit
        ini_set('max_execution_time', '180'); // 3 minutes

        $this->companyId = $companyId;
        $this->userId = $userId;
        
        // Load lookup data once sa constructor
        $this->loadLookupData();
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    public function endColumn(): string
    {
        return 'T';
    }

    protected function loadLookupData()
    {
        // Pre-load all lookup data once to avoid repeated queries
        // Use select() para sa specific columns lang para mas efficient
        $this->lookupCache['units'] = UnitOfMeasurement::where('company_id', $this->companyId)
            ->select('id', 'name')
            ->get()
            ->pluck('name', 'id')
            ->map(function($name) { return strtolower(trim($name)); })
            ->toArray();

        $this->lookupCache['departments'] = Department::where('company_id', $this->companyId)
            ->select('id', 'name')
            ->get()
            ->pluck('name', 'id')
            ->map(function($name) { return strtolower(trim($name)); })
            ->toArray();

        $this->lookupCache['categories'] = Category::where('company_id', $this->companyId)
            ->select('id', 'name')
            ->get()
            ->pluck('name', 'id')
            ->map(function($name) { return strtolower(trim($name)); })
            ->toArray();

        $this->lookupCache['itemTypes'] = ItemType::where('company_id', $this->companyId)
            ->select('id', 'name')
            ->get()
            ->pluck('name', 'id')
            ->map(function($name) { return strtolower(trim($name)); })
            ->toArray();

        $this->lookupCache['itemLocations'] = ItemLocation::where('company_id', $this->companyId)
            ->select('id', 'name')
            ->get()
            ->pluck('name', 'id')
            ->map(function($name) { return strtolower(trim($name)); })
            ->toArray();

        // Build subcategories map - more efficient
        $subcategoriesData = Subcategory::where('company_id', $this->companyId)
            ->select('id', 'name', 'category_id')
            ->get();

        $this->lookupCache['subcategoriesMap'] = [];
        $this->lookupCache['subcategories'] = [];
        
        foreach ($subcategoriesData as $subcategory) {
            $name = strtolower(trim($subcategory->name));
            $key = $name . '_' . $subcategory->category_id;
            $this->lookupCache['subcategoriesMap'][$key] = $subcategory->id;
            $this->lookupCache['subcategories'][$subcategory->id] = $name;
        }
        
        // Clear subcategoriesData to free memory
        unset($subcategoriesData);
    }

    public function collection(Collection $rows)
    {
        // Skip empty rows immediately
        $rows = $rows->filter(function($row) {
            return !empty(array_filter($row->toArray()));
        });
        
        if ($rows->isEmpty()) {
            return;
        }

        $lastNumber = Product::where('company_id', $this->companyId)->max('code') ?? 0;
        $batchData = [];

        foreach ($rows as $key => $row) {
            $lastNumber++;
            
            // More efficient lookups with null coalescing
            $categoryName = strtolower(trim($row[8] ?? ''));
            $categoryId = $this->findInCache($categoryName, $this->lookupCache['categories']);
            
            $subcategoryId = null;
            if ($categoryId && !empty($row[9])) {
                $subcategoryName = strtolower(trim($row[9]));
                $lookupKey = $subcategoryName . '_' . $categoryId;
                $subcategoryId = $this->lookupCache['subcategoriesMap'][$lookupKey] ?? 
                               $this->findInCache($subcategoryName, $this->lookupCache['subcategories']);
            }

            $productData = [
                'status' => trim($row[0] ?? 'active'),
                'name' => trim($row[1] ?? ''),
                'description' => trim($row[2] ?? ''),
                'sku' => trim($row[3] ?? ''),
                'abbreviation' => trim($row[4] ?? ''),
                'uom_id' => $this->findInCache(strtolower(trim($row[5] ?? '')), $this->lookupCache['units']),
                'barcode' => trim($row[6] ?? ''),
                'department_id' => $this->findInCache(strtolower(trim($row[7] ?? '')), $this->lookupCache['departments']),
                'category_id' => $categoryId,
                'subcategory_id' => $subcategoryId,
                'markup_type' => trim($row[10] ?? 'percentage'),
                'markup' => (float)($row[11] ?? 0),
                'cost' => (float)($row[12] ?? 0),
                'srp' => (float)($row[13] ?? 0),
                'item_type_id' => $this->findInCache(strtolower(trim($row[14] ?? '')), $this->lookupCache['itemTypes']),
                'item_locations' => $this->findInCache(strtolower(trim($row[15] ?? '')), $this->lookupCache['itemLocations']),
                'max_discount' => (float)($row[16] ?? 0),
                'minimum_stock_level' => (int)($row[17] ?? 0),
                'maximum_stock_level' => (int)($row[18] ?? 0),
                'part_number' => trim($row[19] ?? ''),
                'company_id' => $this->companyId,
                'code' => $lastNumber,
                'created_by' => $this->userId
            ];

            $batchData[] = $productData;
        }

        // Process in batches instead of individual jobs
        if (!empty($batchData)) {
            UpdateOrCreateProductJob::dispatch($batchData);
        }

        // Clear memory after processing
        unset($batchData, $rows);
        gc_collect_cycles();
    }

    /**
     * Helper method para sa efficient array search
     */
    private function findInCache($needle, $haystack)
    {
        if (empty($needle) || empty($haystack)) {
            return null;
        }
        
        return array_search($needle, $haystack) ?: null;
    }

    public function rules(): array
    {
        // Simplified validation rules para lower CPU usage
        return [
            '*.0' => [Rule::in(['active', 'inactive'])],
            '*.1' => ['required', 'distinct'], // Product name
            // Disable other validations muna para sa testing
            // '*.5' => [
            //     function ($attribute, $value, $fail) {
            //         if (!empty($value) && !$this->findInCache(strtolower(trim($value)), $this->lookupCache['units'])) {
            //             $fail('UOM does not exist');
            //         }
            //     },
            // ],
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
        return 2000;
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            echo 'Excel import failure: ' . $failure->values()[1] . ' at row ' . $failure->row() . ' in column ' . $failure->attribute() . ': ' . $failure->errors()[0] . PHP_EOL;
        }
    }
}