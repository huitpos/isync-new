<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

use App\Repositories\Interfaces\PosMachineRepositoryInterface;

class MachineController extends BaseController
{
    protected $posMachineRepository;

    public function __construct(PosMachineRepositoryInterface $posMachineRepository)
    {
        $this->posMachineRepository = $posMachineRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function activate(Request $request)
    {
        $validatedData = $request->validate([
            'product_key' => 'required|exists:pos_machines,product_key',
            'serial' => 'required',
            'model' => 'required',
            'android_id' => 'required',
            'manufacturer' => 'required',
            'board' => 'required',
        ]);

        $postData = $request->all();

        $machine = $this->posMachineRepository->get([
            'product_key' => $validatedData['product_key'],
            'status' => 'active'
        ])->first();


        if (!$machine) {
            return $this->sendError('Invalid product key', [], 404, Config::get('app.status_codes')['invalid_product_key']);
        }

        if ($machine->branch->status != "active") {
            return $this->sendError('Inactive Branch', [], 404, Config::get('app.status_codes')['branch_not_active']);
        }

        if ($machine->branch->company->status != "active") {
            return $this->sendError('Inactive Company', [], 404, Config::get('app.status_codes')['company_not_active']);
        }

        $devices = PosDevice::where('pos_machine_id', $machine->id)
            ->where('status', 'active');

        if ($request->has('device_id')) {
            $device = PosDevice::with('machine.branch')->where('id', $request->has('device_id'))->first();

            if (!$device) {
                return $this->sendError('Invalid device', [], 404, Config::get('app.status_codes')['invalid_device']);
            }

            $devices->where('id', '!=', $request->input('device_id'));
        }

        if ($devices->count() > 0) {
            return $this->sendError('Device already in use', [], 404, Config::get('app.status_codes')['device_already_in_use']);
        }

        if (!$request->has('device_id')) {
            unset($postData['product_key']);
            unset($postData['device_id']);
            $machine->device()->create($postData);
        }

        $machine = $this->posMachineRepository->getWithActivationData(['product_key' => $validatedData['product_key']])->first();

        return $this->sendResponse($machine, 'Device activated successfully');
    }
}