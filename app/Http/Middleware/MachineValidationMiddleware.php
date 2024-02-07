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

        $device = PosDevice::with('machine')->where('id', $machineId)->first();

        if (!$device || $device->machine->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid machine'
            ], 422);
        }

        return $next($request);
    }
}
