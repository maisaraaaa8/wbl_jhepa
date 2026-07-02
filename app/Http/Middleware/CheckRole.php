<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Semak jika user login & role dia sama dengan yang ditetapkan
        if ($request->user() && $request->user()->role === $role) {
            return $next($request);
        }

        // Jika tidak, halang akses (403 Forbidden)
        abort(403, 'Anda tidak mempunyai akses ke bahagian ini.');
    }
}