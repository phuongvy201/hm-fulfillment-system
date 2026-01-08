<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCustomer
{
    /**
     * Handle an incoming request.
     * Only allow non-admin users (customers).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is admin (super-admin or it-admin)
        if ($user->role && in_array($user->role->slug, ['super-admin', 'it-admin'])) {
            abort(403, 'Admin users cannot access customer pages.');
        }

        return $next($request);
    }
}
