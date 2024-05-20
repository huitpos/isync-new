<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Company;
use App\Models\Branch;
use App\Models\Permission;
class ValidateCompanySlug
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $companySlug = $request->route('companySlug');
        $route = $request->route()->getName();
        $sitePermissions = Permission::whereNot('route', null)->pluck('route')->toArray();

        /** @var \App\Models\User */
        $user = auth()->user();

        $roles = $user->roles;

        $permissions = collect();
        foreach ($roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }

        $permissions = $permissions->unique('id');

        $companyUserPermissions = $permissions->where('level', 'company_user');

        $permissionNames = $permissions->pluck('name')->toArray();
        $permissionRoutes = $permissions->pluck('route')->toArray();
        $request->attributes->add(['permissionNames' => $permissionNames]);

        $branches = $user->activeBranches->pluck('id')->toArray();

        if ( in_array($route, $sitePermissions) && !in_array($route, $permissionRoutes)) {
            abort(403, 'Unauthorized action.');
        }

        if ($companySlug) {
            // Check if the company with this slug exists
            $company = Company::where('slug', $companySlug)->first();

            if (!$company) {
                return abort(404, 'Company not found');
            }

            if ($company->status !== 'active') {
                return abort(403, 'Access denied! You no longer have access to your account. For further assistance, please contact iSync support.');
            }

            // Pass the company data to the request for later use if needed
            $request->attributes->add(['company' => $company]);

            $branchSlug = $request->route('branchSlug');

            if ($branchSlug) {
                $branch  = Branch::where([
                    'slug' => $branchSlug
                ])
                ->whereIn('id', $branches)
                ->first();

                if (!$branch) {
                    return abort(404, 'Branch not found');
                }

                $request->attributes->add(['branch' => $branch]);

                return $next($request);
            }
        }

        return $next($request);
    }
}