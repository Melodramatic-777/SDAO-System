<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CheckAdminSession
{
    public function handle(Request $request, Closure $next)
    {
        if (! Session::has('admin')) {
            return redirect('/admin/login');
        }

        return $next($request);
    }
}
