<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\PosMachine;

use Illuminate\Support\Facades\DB;

use App\Repositories\Interfaces\PosMachineRepositoryInterface;
use App\Repositories\Interfaces\BranchRepositoryInterface;

class MachineController extends Controller
{
    protected $posMachineRepository;
    protected $branchRepository;

    public function __construct(
        PosMachineRepositoryInterface $posMachineRepository,
        BranchRepositoryInterface $branchRepository
    ) {
        $this->posMachineRepository = $posMachineRepository;
        $this->branchRepository = $branchRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $company = Company::find($id);

        if (!$company) {
            return abort(404, 'Company not found');
        }

        $machines = $this->posMachineRepository->getAllUnderCompany($company->id);

        return view('admin.machines.index', [
            'company' => $company,
            'machines' => $machines,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($branchId)
    {
        $branch = $this->branchRepository->find($branchId);

        if (!$branch) {
            return abort(404, 'Branch not found');
        }

        return view('admin.machines.create', [
            'branch' => $branch,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $companyId)
    {
        $request->validate([
            'branch_id' => 'required',
            'status' => 'required',
            'name' => 'required',
            'serial_number' => 'required',
            'min' => 'required',
            'receipt_header' => 'required',
            'receipt_bottom_text' => 'required',
            'permit_number' => 'required',
            'accreditation_number' => 'required',
            'valid_from' => 'required|date_format:Y-m-d\TH:i',
            'valid_to' => 'required|date_format:Y-m-d\TH:i',
            'tin' => 'required',
            'limit_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'vat' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
        ]);

        if ($this->posMachineRepository->create($request->all())) {
            return redirect()->route('admin.branches.show', ['branch' => $request->branch_id])->with('success', 'Data has been stored successfully!');
        } else {
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
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
    public function edit(Request $request, $companyId, $machineId)
    {
        $company = Company::find($companyId);

        if (!$company) {
            return abort(404, 'Company not found');
        }

        $machine = PosMachine::find($machineId);

        if (!$machine) {
            return abort(404, 'Machine not found');
        }

        $branches = DB::table('branches')
            ->where('branches.company_id', '=', $company->id)
            ->get();

        return view('admin.machines.edit', [
            'machine' => $machine,
            'company' => $company,
            'branches' => $branches,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $companyId, $machineId)
    {
        $machine = PosMachine::find($machineId);

        $validatedData = $request->validate([
            'branch_id' => 'required',
            'status' => 'required',
            'machine_number' => 'required',
            'serial_number' => 'required',
            'min' => 'required',
            'receipt_header' => 'required',
            'receipt_bottom_text' => 'required',
            'permit_number' => 'required',
            'accreditation_number' => 'required',
            'valid_from' => 'required|date_format:Y-m-d\TH:i',
            'valid_to' => 'required|date_format:Y-m-d\TH:i',
            'tin' => 'required',
            'limit_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'vat' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
        ]);

        if ($this->posMachineRepository->update($machineId, $request->all())) {
            return redirect()->route('admin.machines.index', ['companyId' => $companyId])->with('success', 'Data has been updated successfully!');
        }

        return redirect()->back()->with('error', 'Something went wrong. Please try again.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
