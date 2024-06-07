<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\PosMachine;
use App\Models\Branch;

use Illuminate\Support\Facades\DB;

use App\Repositories\Interfaces\PosMachineRepositoryInterface;
use App\Repositories\Interfaces\BranchRepositoryInterface;

use App\DataTables\Admin\MachineDevicesDataTable;

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
    public function store(Request $request)
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
            'valid_from' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    // Check if the value matches either 'Y-m-d\TH:i' or 'Y-m-d' format
                    if (!strtotime($value) && !strtotime(str_replace('T', ' ', $value))) {
                        $fail($attribute . ' is not in a valid date-time format.');
                    }
                },
            ],
            'valid_to' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    // Check if the value matches either 'Y-m-d\TH:i' or 'Y-m-d' format
                    if (!strtotime($value) && !strtotime(str_replace('T', ' ', $value))) {
                        $fail($attribute . ' is not in a valid date-time format.');
                    }
                },
            ],
            'tin' => 'required',
            'limit_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
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
    public function show(MachineDevicesDataTable $datatable, string $branchId, string $id)
    {
        $machine = $this->posMachineRepository->find($id);

        if (!$machine) {
            return abort(404, 'Machine not found');
        }

        return $datatable->with('machine_id', $machine->id)->render('admin.machines.show', [
            'machine' => $machine,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $branchId, $machineId)
    {
        $branch = Branch::find($branchId);

        if (!$branch) {
            return abort(404, 'Branch not found');
        }

        $machine = PosMachine::find($machineId);

        if (!$machine) {
            return abort(404, 'Machine not found');
        }

        return view('admin.machines.edit', [
            'machine' => $machine,
            'branch' => $branch
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $branchId, $machineId)
    {
        if ($request->ajax()) {
            $machine = $this->posMachineRepository->find($machineId);

            if ($machine) {
                $machine->status = $request->status;
                $machine->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Machine status updated successfully.'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Machine not found.'
            ]);
        }

        $request->validate([
            'branch_id' => 'required',
            'status' => 'required',
            // 'machine_number' => 'required',
            'serial_number' => 'required',
            'min' => 'required',
            'receipt_header' => 'required',
            'receipt_bottom_text' => 'required',
            'permit_number' => 'required',
            'accreditation_number' => 'required',
            'valid_from' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    // Check if the value matches either 'Y-m-d\TH:i' or 'Y-m-d' format
                    if (!strtotime($value) && !strtotime(str_replace('T', ' ', $value))) {
                        $fail($attribute . ' is not in a valid date-time format.');
                    }
                },
            ],
            'valid_to' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    // Check if the value matches either 'Y-m-d\TH:i' or 'Y-m-d' format
                    if (!strtotime($value) && !strtotime(str_replace('T', ' ', $value))) {
                        $fail($attribute . ' is not in a valid date-time format.');
                    }
                },
            ],
            'tin' => 'required',
            'limit_amount' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'vat' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
        ]);

        if ($this->posMachineRepository->update($machineId, $request->all())) {
            return redirect()->route('admin.branches.show', ['branch' => $branchId])->with('success', 'Data has been updated successfully!');
        }

        return redirect()->back()->with('error', 'Something went wrong. Please try again.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        dd("here");
    }
}
