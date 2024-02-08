<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\PosDevice;

class MachineValidationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $machineId = $request->input('device_id');

        $device = PosDevice::with('machine.branch')->where('id', $machineId)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid device'
            ], 422);
        }

        if ($device->machine->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Product Key is not active'
            ], 422);
        }

        if ($device->machine->branch->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Branch is not active'
            ], 422);
        }

        return $next($request);
    }
}
