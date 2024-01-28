<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\PaymentTypeRepositoryInterface;

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
    public function index(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.paymentTypes.index', [
            'company' => $company
        ]);
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
        $paymentTypeFields = $request->input('payment_type_fields');

        if (isset($paymentTypeFields[0])) {
            unset($paymentTypeFields[0]);
        }

        $request->merge(['payment_type_fields' => $paymentTypeFields]);

        $request->validate([
            'company_id' => 'required',
            'name' => 'required',
            // 'logo' => 'required',
            'status' => 'required',
            'payment_type_fields.*.name' => ['required'],
            'payment_type_fields.*.field_type' => ['required'],
            'payment_type_fields.*.option_list' => [
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1];
                    $fieldType = $request->input("payment_type_fields.$index.field_type");

                    // Check if options are required based on field type
                    if ($fieldType !== 'textbox' && empty($value)) {
                        $fail("Option for field #:index is required");
                    }

                    if ($fieldType !== 'textbox') {
                        foreach ($value as $option) {
                            if (empty($option['option'])) {
                                $fail("Option name for field #:index is required");
                            }
                        }
                    }
                },
            ],
        ], [
            'payment_type_fields.*.name.required' => 'Field name for field #:index is required',
            'payment_type_fields.*.field_type.required' => 'Field type for field #:index is required',
        ]);

        $company = $request->attributes->get('company');

        $paymentTypeData = [
            'company_id' => $request->input('company_id'),
            'status' => $request->input('status'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'is_ar' => $request->input('is_ar') ?? false,
        ];

        $paymentTypeFields = [];
        foreach ($request->input('payment_type_fields') as $field) {
            $data = [
                'name' => $field['name'],
                'field_type' => $field['field_type'],
            ];

            if (!empty($field['option_list'])) {
                foreach ($field['option_list'] as $option) {
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
    public function show(string $id)
    {
        //
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
        $paymentTypeFields = $request->input('payment_type_fields');

        if (isset($paymentTypeFields[0])) {
            unset($paymentTypeFields[0]);
        }

        $request->merge(['payment_type_fields' => $paymentTypeFields]);

        $request->validate([
            'company_id' => 'required',
            'name' => 'required',
            'status' => 'required',
            // 'logo' => 'required',
            'payment_type_fields.*.name' => ['required'],
            'payment_type_fields.*.field_type' => ['required'],
            'payment_type_fields.*.option_list' => [
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1];
                    $fieldType = $request->input("payment_type_fields.$index.field_type");

                    // Check if options are required based on field type
                    if ($fieldType !== 'textbox' && empty($value)) {
                        $fail("Option for field #:index is required");
                    }

                    if ($fieldType !== 'textbox') {
                        foreach ($value as $option) {
                            if (empty($option['option'])) {
                                $fail("Option name for field #:index is required");
                            }
                        }
                    }
                },
            ],
        ], [
            'payment_type_fields.*.name.required' => 'Field name for field #:index is required',
            'payment_type_fields.*.field_type.required' => 'Field type for field #:index is required',
        ]);

        $company = $request->attributes->get('company');

        $paymentTypeData = [
            'company_id' => $request->input('company_id'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'is_ar' => $request->input('is_ar') ?? false,
        ];

        $paymentTypeFields = [];
        foreach ($request->input('payment_type_fields') as $field) {
            $data = [
                'name' => $field['name'],
                'field_type' => $field['field_type'],
            ];

            if (!empty($field['option_list'])) {
                foreach ($field['option_list'] as $option) {
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
