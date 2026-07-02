<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSupabaseAuth
{
    /**
     * Middleware pengganti untuk 'auth' middleware biasa.
     * Semak sama ada sesi Supabase aktif (logged_in = true).
     * Digunakan kerana kita tidak guna Auth::login() — login disimpan
     * dalam session secara manual selepas Supabase Auth API berjaya.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('logged_in')) {
            return redirect('/login');
        }

        return $next($request);
    }
}
