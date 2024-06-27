<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithColumnLimit;

use App\Models\UnitOfMeasurement;
use App\Models\Department;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ItemType;
use App\Models\Product;

class ProductsImport implements ToCollection, WithValidation, WithStartRow, WithCalculatedFormulas, WithLimit, WithColumnLimit
{
    protected $companyId;
    protected $data = [];

    public function __construct(int $companyId)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');

        $this->companyId = $companyId;
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
        return 'O';
    }

    public function collection(Collection $rows)
    {
        dd($rows);
        $lastNumber = Product::where('company_id', $this->companyId)->max('code'); 

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

        foreach ($rows as $key => $row) {
            $lastNumber++;

            $this->data[] = [
                'status' => $row[0],
                'name' => $row[1],
                'description' => $row[2],
                'sku' => $row[3],
                'abbreviation' => $row[4],
                'uom_id' => array_search(strtolower($row[5]), $units),
                'barcode' => $row[6],
                'department_id' => array_search(strtolower($row[7]), $departments) ?? null,
                'category_id' => array_search(strtolower($row[8]), $categories),
                'subcategory_id' => array_search(strtolower($row[9]), $subcategories),
                'markup_type' => $row[10],
                'markup' => $row[11],
                'cost' => $row[12],
                'item_type_id' => array_search(strtolower($row[13]), $itemTypes),
                // 'srp' => $row[10] == 'percentage' ? $row[12] + ($row[12] * ($row[11] / 100)) : $row[12] + $row[11],
                'srp' => $row[14],
                'company_id' => $this->companyId,
                'code' => $lastNumber,
                'minimum_stock_level' => 0,
                'maximum_stock_level' => 0,
            ];
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
}