<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Company;
use App\Models\Branch;
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

        $user = auth()->user();

        $branches = $user->activeBranches->pluck('id')->toArray();

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
                    'slug' => $branchSlug,
                    'id' => $branches
                ])->first();

                if (!$branch) {
                    return abort(404, 'Branch not found');
                }

                $request->attributes->add(['branch' => $branch]);

                return $next($request);
            }

            if (!$user->hasRole('company_admin')) {
                abort(403, 'Unauthorized action.');
            }
        }

        return $next($request);
    }
}