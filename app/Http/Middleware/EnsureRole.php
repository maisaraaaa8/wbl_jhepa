<?php

namespace App\Http\Middleware;

use App\Helpers\SessionUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk sekat akses ikut peranan (role).
 *
 * Guna: ->middleware('role:admin,readonly')
 *
 * Sebab kita tak guna Auth::user() (lihat CheckSupabaseAuth), semakan
 * peranan di sini baca dari session melalui SessionUser::role(), BUKAN
 * dari request()->user()->role.
 *
 * Kalau peranan pengguna tidak dibenarkan, mereka akan dihantar balik
 * ke /dashboard mereka sendiri (bukan 403 kosong) supaya pengalaman
 * lebih mesra — DashboardController akan auto-route ikut peranan betul.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!SessionUser::loggedIn()) {
            return redirect('/login');
        }

        if (!in_array(SessionUser::role(), $roles, true)) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak mempunyai akses ke bahagian ini.');
        }

        return $next($request);
    }
}
