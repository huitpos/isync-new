<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PosDevice;

class DeviceController extends Controller
{
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
    public function update(Request $request, string $branchId, string $id)
    {
        if ($request->ajax()) {
            $device = PosDevice::find($id);

            if (!$device) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Device not found.'
                ]);
            }

            $activeDevices = PosDevice::where('pos_machine_id', $device->pos_machine_id)
                ->where([
                    ['status', 'active'],
                    ['id', '!=', $device->id]
                ])
                ->count();

            if ($activeDevices > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only one device can be active at a time.'
                ]);
            }

            $device->status = $request->status;
            $device->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Device status updated successfully.'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
