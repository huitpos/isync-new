<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\PaymentTypeRepositoryInterface;

use App\DataTables\Company\PaymentTypesDataTable;

class PaymentTypeController extends Controller
{
    protected $paymentTypeRepository;

    public function __construct(PaymentTypeRepositoryInterface $paymentTypeRepository)
    {
        $this->paymentTypeRepository = $paymentTypeRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, PaymentTypesDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $permissions = $request->attributes->get('permissionNames');

        return $dataTable->with([
                'company_id' => $company->id,
                'permissions' => $permissions
            ])
            ->render('company.paymentTypes.index', compact('company', 'permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.paymentTypes.create', [
            'company' => $company
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            // 'logo' => 'required',
            'status' => 'required',
            'payment_type_fields.*.name' => 'nullable',
            'payment_type_fields.*.field_type' => 'required_with:payment_type_fields.*.name',
            'payment_type_fields.*.options' => [
                'nullable',
                'required_with:payment_type_fields.*.name',
                function ($attribute, $value, $fail) use($request) {
                    $index = explode('.', $attribute)[1];
                    $fieldType = $request->input("payment_type_fields.$index.field_type");

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
            'payment_type_fields.*.field_type' => 'The field type is required',
        ]);

        $company = $request->attributes->get('company');

        $paymentTypeData = [
            'company_id' => $company->id,
            'status' => $request->input('status'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'is_ar' => $request->input('is_ar') ?? false,
        ];

        $paymentTypeFields = [];
        foreach ($request->input('payment_type_fields') as $field) {
            if (empty($field['name'])) {
                continue;
            }

            $data = [
                'name' => $field['name'],
                'field_type' => $field['field_type'],
                'mask' => $field['mask'][0] ?? false,
            ];

            if (!empty($field['options'])) {
                foreach ($field['options'] as $option) {
                    $data['options'][] = $option['option'];
                }
            }

            $paymentTypeFields[] = $data;
        }

        if ($this->paymentTypeRepository->create($paymentTypeData, $paymentTypeFields)) {
            return redirect()->route('company.payment-types.index', ['companySlug' => $company->slug])->with('success', 'Payment type successfully created.');
        }

        return redirect()->route('company.payment-types.index', ['companySlug' => $company->slug])->with('error', 'Payment type failed to create.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');

        $paymentType = $this->paymentTypeRepository->find($id);

        if (!$paymentType || $paymentType->company_id != $company->id) {
            return abort(404, 'Payment Type not found');
        }

        return view('company.paymentTypes.show', [
            'paymentType' => $paymentType,
            'company' => $company
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $companySlug, $paymentTypeId)
    {
        $company = $request->attributes->get('company');

        $paymentType = $this->paymentTypeRepository->find($paymentTypeId);

        return view('company.paymentTypes.edit', [
            'company' => $company,
            'paymentType' => $paymentType
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $paymentTypeId)
    {
        $request->validate([
            'name' => 'required',
            // 'logo' => 'required',
            'status' => 'required',
            'payment_type_fields.*.name' => 'nullable',
            'payment_type_fields.*.field_type' => 'required_with:payment_type_fields.*.name',
            'payment_type_fields.*.options' => [
                'nullable',
                'required_with:payment_type_fields.*.name',
                function ($attribute, $value, $fail) use($request) {
                    $index = explode('.', $attribute)[1];
                    $fieldType = $request->input("payment_type_fields.$index.field_type");

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
            'payment_type_fields.*.field_type' => 'The field type is required',
        ]);

        $company = $request->attributes->get('company');

        $paymentTypeData = [
            'company_id' => $company->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'is_ar' => $request->input('is_ar') ?? false,
        ];

        $paymentTypeFields = [];
        foreach ($request->input('payment_type_fields') as $field) {
            if (empty($field['name'])) {
                continue;
            }

            $data = [
                'name' => $field['name'],
                'field_type' => $field['field_type'],
                'mask' => $field['mask'][0] ?? false,
            ];

            if (!empty($field['options'])) {
                foreach ($field['options'] as $option) {
                    $data['options'][] = $option['option'];
                }
            }

            $paymentTypeFields[] = $data;
        }

        if ($this->paymentTypeRepository->update($paymentTypeId, $paymentTypeData, $paymentTypeFields)) {
            return redirect()->route('company.payment-types.index', ['companySlug' => $company->slug])->with('success', 'Payment type successfully updated.');
        }

        return redirect()->route('company.payment-types.index', ['companySlug' => $company->slug])->with('error', 'Payment type failed to update.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
