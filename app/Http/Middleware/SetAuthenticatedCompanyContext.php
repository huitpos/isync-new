<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAuthenticatedCompanyContext
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        $permissions = collect();
        foreach ($user->roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }
        $permissions = $permissions->unique('id');

        $request->attributes->add([
            'permissionNames' => $permissions->pluck('name')->toArray(),
            'companyPermissionCount' => $permissions->where('level', 'company_user')->count(),
            'branchPermissionCount' => $permissions->where('level', 'branch_user')->count(),
            'companyFirstRoute' => $permissions
                ->where('route', '!=', null)
                ->where('level', 'company_user')
                ->pluck('route')
                ->first(),
        ]);

        if ($user->company_id) {
            $company = Company::where('id', $user->company_id)
                ->where('status', 'active')
                ->first();

            if ($company) {
                $request->attributes->add(['company' => $company]);
            }
        }

        return $next($request);
    }
}
