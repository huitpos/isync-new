<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\ItemTypeRepositoryInterface;

use App\DataTables\Company\ItemTypesDataTable;

class ItemTypeController extends Controller
{
    protected $itemTypeRepository;

    public function __construct(ItemTypeRepositoryInterface $itemTypeRepository)
    {
        $this->itemTypeRepository = $itemTypeRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ItemTypesDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $permissions = $request->attributes->get('permissionNames');

        return $dataTable->with([
            'company_id' => $company->id,
            'permissions' => $permissions
        ])->render('company.itemTypes.index', [
            'company' => $company,
            'permissions' => $permissions
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.itemTypes.create', [
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
            'status' => 'required'
        ]);

        $company = $request->attributes->get('company');

        $postData = $request->all();;
        $postData['company_id'] = $company->id;

        if ($this->itemTypeRepository->create($postData)) {
            return redirect()->route('company.item-types.index', ['companySlug' => $company->slug])->with('success', 'Data has been stored successfully!');
        }

        return redirect()->back()->with('success', 'Data has been stored successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');

        $itemType = $this->itemTypeRepository->find($id);

        if (!$itemType || $itemType->company_id != $company->id) {
            return abort(404, 'Item Type not found!');
        }

        return view('company.itemTypes.show', [
            'company' => $company,
            'itemType' => $itemType
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');

        $itemType = $this->itemTypeRepository->find($id);

        return view('company.itemTypes.edit', [
            'company' => $company,
            'itemType' => $itemType
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $id)
    {
        $request->validate([
            'name' => 'required',
            'status' => 'required'
        ]);

        $company = $request->attributes->get('company');

        $postData = $request->all();;
        $postData['company_id'] = $company->id;
        $postData['show_in_cashier'] = $request->show_in_cashier ?? false;

        if ($this->itemTypeRepository->update($id, $postData)) {
            return redirect()->route('company.item-types.index', ['companySlug' => $company->slug])->with('success', 'Data has been updated successfully!');
        }

        return redirect()->back()->with('success', 'Data has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
