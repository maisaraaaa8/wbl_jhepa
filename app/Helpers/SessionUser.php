<?php

namespace App\Helpers;

/**
 * SessionUser — pengganti Auth::user() untuk sistem Supabase
 *
 * Sebab kita login via Supabase API (bukan Auth::login()),
 * Auth::user() akan sentiasa return null.
 * Gunakan SessionUser::get() atau helper global currentUser() sebaliknya.
 *
 * GUNA:
 *   SessionUser::get()         → array penuh ['id', 'nama', 'role', 'email']
 *   SessionUser::role()        → 'admin' | 'readonly' | 'pelajar' | 'keluarga_angkat'
 *   SessionUser::isAdmin()     → true/false
 *   SessionUser::nama()        → nama pengguna
 *   SessionUser::email()       → email pengguna
 *   SessionUser::id()          → UUID pengguna
 */
class SessionUser
{
    public static function get(): array
    {
        return [
            'id'    => session('user_id',  ''),
            'nama'  => session('nama',     session('email', 'Pengguna')),
            'email' => session('email',    ''),
            'role'  => session('role',     session('peranan', 'readonly')),
        ];
    }

    public static function id(): string
    {
        return session('user_id', '');
    }

    public static function role(): string
    {
        return session('role', session('peranan', 'readonly'));
    }

    public static function nama(): string
    {
        return session('nama', session('email', 'Pengguna'));
    }

    public static function email(): string
    {
        return session('email', '');
    }

    public static function isAdmin(): bool
    {
        return static::role() === 'admin';
    }

    public static function isPelajar(): bool
    {
        return static::role() === 'pelajar';
    }

    public static function isKeluargaAngkat(): bool
    {
        return static::role() === 'keluarga_angkat';
    }

    public static function loggedIn(): bool
    {
        return (bool) session('logged_in', false);
    }
}
