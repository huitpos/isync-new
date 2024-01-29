<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosDevice;
use Illuminate\Http\Request;

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
        unset($postData['product_key']);

        $machine = $this->posMachineRepository->get(['product_key' => $validatedData['product_key']])->first();

        $devices = PosDevice::where('pos_machine_id', $machine->id)
            ->where('status', 'active')
            ->count();

        if ($devices > 0) {
            return $this->sendError('Device already in use');
        }

        $machine->device()->create($postData);

        return $this->sendResponse($machine, 'Device activated successfully');
    }
}
