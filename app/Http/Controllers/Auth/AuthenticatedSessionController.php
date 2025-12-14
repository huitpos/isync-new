<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

use App\Models\Company;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        addJavascriptFile('assets/js/custom/authentication/sign-in/general.js');

        return view('pages/auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        $user->createToken('app')->plainTextToken;
        session(['_apiToken' => $user->createToken('app')->plainTextToken]);

        $roles = $user->roles;
        $branches = auth()->user()->activeBranches;

        $permissions = collect();
        foreach ($roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }

        $permissions = $permissions->unique('id');

        $permissionNames = $permissions->pluck('name')->toArray();

        $companyLevelPermission = $permissions->where('level', 'company_user');
        $branchLevelPermission = $permissions->where('level', 'branch_user');

        $routes = config('app.permission_routes');

        if ($user->hasRole('super_admin')) {
            return route('admin.dashboard');
        }

        if ($companyLevelPermission->count() > 0) {
            $company =  Company::find($user->company_id);

            $parentPermission = $companyLevelPermission->whereNull('parent_id')->first();
            $childPermission = $companyLevelPermission->where('parent_id', $parentPermission->id)->first();
            $route = $childPermission->route ?? 'company.dashboard';

            return route($route, [
                'companySlug' => $company->slug,
                'companyId' => $company->id,
                'branchSlug' => $branches->first()->slug,
                'branchId' => $branches->first()->id
            ]);
        }

        if ($branchLevelPermission->count() > 0) {
            $company =  Company::find($user->company_id);

            $parentPermission = $branchLevelPermission->whereNull('parent_id')->first();
            $childPermission = $branchLevelPermission->where('parent_id', $parentPermission->id)->first();
            $route = $childPermission->route ?? 'branch.dashboard';

            return route($route, [
                'companySlug' => $company->slug,
                'companyId' => $company->id,
                'branchSlug' => $branches->first()->slug,
                'branchId' => $branches->first()->id
            ]);
        }


        $branch = $user->activeBranches->first();
        return route('branch.users.index', ['companySlug' => $branch->company->slug, 'branchSlug' => $branch->slug]);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}