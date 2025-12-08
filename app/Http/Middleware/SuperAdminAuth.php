<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
public function handle($request, Closure $next)
{
    // Allow Super Admin full access
    if (session()->has('admin')) {
        return $next($request);
    }

    // Allow normal office accounts
    if (session()->has('volunteers_office')) {
        return $next($request);
    }

    abort(403, 'No logged in user email found.');
}


}
