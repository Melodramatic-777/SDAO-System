<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ActivitiesAdminAuth
{
    /**
     * Handle an incoming request.
     */    public function handle(Request $request, Closure $next)
    {
        $admin = session('admin');

        if (!$admin || ($admin['role'] ?? '') !== 'Activities Admin') {
            return redirect('/admin/login')->withErrors(['error' => 'Unauthorized']);
        }

        return $next($request);
    }
}