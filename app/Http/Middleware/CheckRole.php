<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Role-based access control middleware.
 *
 * Usage in routes:
 *   ->middleware('role:admin')
 *   ->middleware('role:admin,doctor')   // allows either role
 *
 * Roles: admin, doctor, receptionist
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = Auth::user()->role?->name ?? Auth::user()->role;

        foreach ($roles as $role) {
            if (strtolower($userRole) === strtolower($role)) {
                return $next($request);
            }
        }

        abort(403, 'Access denied. You do not have permission to view this page.');
    }
}
