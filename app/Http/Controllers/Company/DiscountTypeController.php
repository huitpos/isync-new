<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\DiscountTypeRepositoryInterface;

use App\DataTables\Company\DiscountTypesDataTable;

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
    public function index(Request $request, DiscountTypesDataTable $dataTable)
    {
        $company = $request->attributes->get('company');

        return $dataTable->with('company_id', $company->id)->render('company.discountTypes.index', [
            'company' => $company
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');
        $departments = $company->departments()->where('status', 'active')->get();

        return view('company.discountTypes.create', [
            'company' => $company,
            'departments' => $departments
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $company = $request->attributes->get('company');

        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'type' => 'required',
            'discount' => 'required',
            'status' => 'required',
            'discount_type_fields.*.name' => 'nullable',
            'discount_type_fields.*.field_type' => 'required_with:discount_type_fields.*.name',
            'discount_type_fields.*.options' => [
                'nullable',
                function ($attribute, $value, $fail) use($request) {
                    $index = explode('.', $attribute)[1];
                    $fieldType = $request->input("discount_type_fields.$index.field_type");

                    if (!empty($value) && !empty($fieldType) && $fieldType !== 'textbox') {
                        $nonBlankOptions = array_filter($value, function ($option) {
                            return !empty($option['option']);
                        });

                        if (empty($nonBlankOptions)) {
                            $fail('At least one non-blank option is required if field_type is not textbox.');
                        }
                    }
                },
            ],
        ], [
            'discount_type_fields.*.field_type' => 'The field type is required',
        ]);

        $postData = $request->all();
        $postData['company_id'] = $company->id;

        $discountTypeFields = [];
        foreach ($request->input('discount_type_fields') as $field) {
            if (empty($field['name'])) {
                continue;
            }

            $data = [
                'name' => $field['name'],
                'field_type' => $field['field_type'],
                'is_required' => isset($field['required']) ? true : false,
            ];

            if (!empty($field['options'])) {
                foreach ($field['options'] as $option) {
                    if (empty($option['option'])) {
                        continue;
                    }

                    $data['options'][] = $option['option'];
                }
            }

            $discountTypeFields[] = $data;
        }

        unset($postData['discount_type_fields']);
        unset($postData['departments']);
        if ($discountType = $this->discountTypeRepository->create($postData, $discountTypeFields)) {
            $this->discountTypeRepository->syncDepartments($discountType->id, $request->departments ?? []);
            return redirect()->route('company.discount-types.index', ['companySlug' => $company->slug])->with('success', 'Discount type created successfully.');
        }

        return redirect()->route('company.discount-types.index', ['companySlug' => $company->slug])->with('error', 'Discount type failed to create.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');

        $discountType = $this->discountTypeRepository->find($id);

        if (!$discountType) {
            return abort(404, 'Discount type not found.');
        }

        return view('company.discountTypes.show', [
            'company' => $company,
            'discountType' => $discountType,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $companySlug, int $id)
    {
        $company = $request->attributes->get('company');
        $departments = $company->departments()->where('status', 'active')->get();

        $discountType = $this->discountTypeRepository->find($id);

        return view('company.discountTypes.edit', [
            'company' => $company,
            'discountType' => $discountType,
            'departments' => $departments
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, int $id)
    {
        $company = $request->attributes->get('company');

        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'type' => 'required',
            'discount' => 'required',
            'status' => 'required',
        ]);

        $postData = $request->all();
        $postData['company_id'] = $company->id;
        $postData['is_vat_exempt'] = $request->is_vat_exempt ?? false;
        $postData['is_zero_rated'] = $request->is_zero_rated ?? false;

        $discountTypeFields = [];
        foreach ($request->input('discount_type_fields') as $key => $field) {
            if (empty($field['name'])) {
                continue;
            }

            $data = [
                'name' => $field['name'],
                'field_type' => $field['field_type'],
                'is_required' => $field['is_required'] ?? false,
                'is_required' => isset($field['required']) ? true : false,
            ];

            if (!empty($field['options'])) {
                foreach ($field['options'] as $option) {
                    if (empty($option['option'])) {
                        continue;
                    }

                    $data['options'][] = $option['option'];
                }
            }

            $discountTypeFields[] = $data;
        }

        unset($postData['discount_type_fields']);
        unset($postData['departments']);
        

        if ($this->discountTypeRepository->update($id, $postData, $discountTypeFields)) {
            $this->discountTypeRepository->syncDepartments($id, $request->departments ?? []);
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
