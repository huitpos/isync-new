<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::user();
                $user->createToken('app')->plainTextToken;
                session(['_apiToken' => $user->createToken('app')->plainTextToken]);

                if ($user->hasRole('super_admin')) {
                    return redirect()->route('admin.dashboard');
                }

                if ($user->client_id) {
                    $slug = $user->client->companies()->first()->slug;

                    return redirect()->route('company.dashboard', ['companySlug' => $slug]);
                }
            }
        }

        return $next($request);
    }
}
