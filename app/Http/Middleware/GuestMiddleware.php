<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuestMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('auth_token') && session()->has('auth_user')) {
            return redirect()->route('dashboard.index');
        }

        return $next($request);
    }
}