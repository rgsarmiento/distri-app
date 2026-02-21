<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Solo permite acceso al Admin (role_id = 1).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userRole = Auth::user()->role_id;

        if ($userRole != 1) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

        return $next($request);
    }
}
