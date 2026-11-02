<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

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
        $machine = $this->posMachineRepository->find($id);

        if (!$machine) {
            return $this->sendError('Machine not found', [], 404, Config::get('app.status_codes')['machine_not_found']);
        }

        return $this->sendResponse($machine, 'Machine retrieved successfully');
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
        $machine = $this->posMachineRepository->find($id);

        if (!$machine) {
            return $this->sendError('Machine not found', [], 404, Config::get('app.status_codes')['machine_not_found']);
        }

        $validator = validator($request->all(), [
            'or_counter' => 'required',
            'x_reading_counter' => 'required',
            'z_reading_counter' => 'required',
            'void_counter' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        // update validator data
        $machine->update([
            'or_counter' => $request->or_counter,
            'x_reading_counter' => $request->x_reading_counter,
            'z_reading_counter' => $request->z_reading_counter,
            'void_counter' => $request->void_counter,
            'or_reset_counter' => $request->or_reset_counter,
            'gt_counter' => $request->gt_counter,
            'payout_counter' => $request->payout_counter,
            'shift_counter' => $request->shift_counter,
        ]);

        return $this->sendResponse($machine, 'Machine updated successfully');
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
            $device = PosDevice::with('machine.branch')->where('id', $request->device_id)->first();

            if (!$device) {
                return $this->sendError('Invalid device', [], 404, Config::get('app.status_codes')['invalid_device']);
            }

            $devices->where('id', '!=', $request->input('device_id'));
        }

        if ($devices->count() > 0) {
            return $this->sendError('Product key already in use', [], 404, Config::get('app.status_codes')['device_already_in_use']);
        }

        if (!$request->has('device_id')) {
            unset($postData['product_key']);
            unset($postData['device_id']);
            $machine->device()->create($postData);
        }

        $machine = $this->posMachineRepository->getWithActivationData(['product_key' => $validatedData['product_key']])->first();

        return $this->sendResponse($machine, 'Device activated successfully');
    }

    public function posAppVersion(Request $request)
    {
        $versionCode = 2;
        $versionName = '1.3.0';
        $apkPath = 'pos/isyncpos-' . $versionName . '.apk';
        $apkUrl = 'https://isync-bucket.s3.ap-southeast-1.amazonaws.com/' . $apkPath;

        $sha256 = '0B4C78DAF5C69A46CF91BAF0A257513DDC92FD26FF7F773854DAFB25A087B7D9';
        return response()->json([
            'success' => true,
            'message' => 'ok',
            'code' => '200',
            'data' => [
                'version_code' => $versionCode,
                'version_name' => $versionName,
                'apk_url' => $apkUrl,
                'sha256' => $sha256,
                'mandatory' => false,
            ],
        ]);
    }
}