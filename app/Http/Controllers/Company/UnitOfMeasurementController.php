<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\UnitOfMeasurementRepositoryInterface;

use App\DataTables\Company\UnitOfMeasurementsDataTable;

use App\Models\UnitConversion;

class UnitOfMeasurementController extends Controller
{
    protected $uomRepository;

    public function __construct(UnitOfMeasurementRepositoryInterface $uomRepository)
    {
        $this->uomRepository = $uomRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, UnitOfMeasurementsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');

        return $dataTable->with('company_id', $company->id)->render('company.unitOfMeasurements.index', [
            'company' => $company
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.unitOfMeasurements.create', [
            'company' => $company,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'status' => 'required',
        ]);

        $company = $request->attributes->get('company');

        $postData = $request->all();
        $postData['company_id'] = $company->id;

        if ($this->uomRepository->create($postData)) {
            return redirect()->route('company.unit-of-measurements.index', ['companySlug' => $company->slug])->with('success', 'Data has been stored successfully!');
        }

        return redirect()->back()->with('error', 'Error occurred while saving data!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $uom = $this->uomRepository->find($id);

        if (empty($uom)) {
            return abort(404);
        }

        $otherUoms = $this->uomRepository->get([
            ['company_id', '=',  $company->id],
            ['id', '!=', $id]
        ]);

        $conversions = $uom->conversions;
        $toConversions = $uom->conversionsTo;

        return view('company.unitOfMeasurements.show', compact('company', 'uom', 'otherUoms', 'conversions', 'toConversions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $companySlug, $uomId)
    {
        $company = $request->attributes->get('company');
        $uom = $this->uomRepository->find($uomId);

        if (empty($uom)) {
            return redirect()->route('company.unit-of-measurements.index', ['companySlug' => $company->slug])->with('error', 'Data not found!');
        }

        return view('company.unitOfMeasurements.edit', [
            'company' => $company,
            'uom' => $uom,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, $uomId)
    {
        $request->validate([
            'name' => 'required',
            'status'
        ]);

        $company = $request->attributes->get('company');
        $uom = $this->uomRepository->find($uomId);

        if (empty($uom)) {
            return redirect()->route('company.unit-of-measurements.index', ['companySlug' => $company->slug])->with('error', 'Data not found!');
        }

        $company = $request->attributes->get('company');

        $postData = $request->all();
        $postData['company_id'] = $company->id;

        if ($this->uomRepository->update($uomId, $postData)) {
            return redirect()->route('company.unit-of-measurements.index', ['companySlug' => $company->slug])->with('success', 'Data has been updated successfully!');
        }

        return redirect()->back()->with('error', 'Error occurred while updating data!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function saveConversion(Request $request)
    {
        $allConversions = UnitConversion::where('to_unit_id', $request->input('uom_id'))->get();
        $allFromConversions = $allConversions->pluck('from_unit_id')->toArray();

        $request->validate([
            'unit_conversions.*.to_unit_id' => [
                'required_with:unit_conversions.*.value',
                'distinct',
                function ($attribute, $value, $fail) use ($request, $allFromConversions) {
                    if (in_array($value, $allFromConversions)) {
                        $fail('Conversion already exists!');
                    }
                },
            ],
            'unit_conversions.*.value' => 'required_with:unit_conversions.*.to_unit_id',
        ], [
            'unit_conversions.*.to_unit_id' => 'This field is required or has duplicate value', 
            'unit_conversions.*.value' => 'The field is required',
        ]);

        $company = $request->attributes->get('company');

        $postData = $request->all();
        $conversionData = [];
        if (!empty($postData['unit_conversions'])) {
            foreach ($postData['unit_conversions'] as $conversion) {
                if (empty($conversion['value']) && empty($conversion['to_unit_id'])) {
                    continue;
                }

                $conversionData[] = [
                    'to_unit_id' => $conversion['to_unit_id'],
                    'value' => $conversion['value'],
                    'company_id' => $company->id,
                ];
            }
        }

        if ($this->uomRepository->syncConversion($postData['uom_id'], $conversionData)) {
            return redirect()->back()->with('success', 'Data has been updated successfully!');
        }

        return redirect()->back()->with('error', 'Error occurred while updating data!');
    }
}
