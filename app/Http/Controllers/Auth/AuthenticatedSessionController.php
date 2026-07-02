<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SupabaseAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    protected SupabaseAuthService $supabaseAuth;

    public function __construct(SupabaseAuthService $supabaseAuth)
    {
        $this->supabaseAuth = $supabaseAuth;
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'E-mel diperlukan.',
            'email.email'       => 'Format e-mel tidak sah.',
            'password.required' => 'Kata laluan diperlukan.',
        ]);

        // Step 1: Login via Supabase Auth API
        $authData = $this->supabaseAuth->login($request->email, $request->password);

        if (!$authData || empty($authData['user'])) {
            throw ValidationException::withMessages([
                'email' => 'E-mel atau kata laluan tidak betul.',
            ]);
        }

        $supabaseUser = $authData['user'];
        $userId       = $supabaseUser['id'];
        $email        = $supabaseUser['email'];

        // Step 2: Ambil profil dari table profiles
        $profile = $this->supabaseAuth->ensureProfile($userId, $email);

        // PENTING: role mesti diambil dari profiles, bukan metadata
        // Roles yang sah: 'admin', 'readonly', 'pelajar', 'keluarga_angkat'
        $role = $profile['role'] ?? 'readonly';
        $nama = $profile['nama'] ?? explode('@', $email)[0];

        // Step 3: Simpan dalam session — KEDUA-DUA 'role' DAN 'peranan'
        // supaya semua view dan controller boleh guna mana-mana satu
        $request->session()->regenerate();

        Session::put([
            'logged_in'             => true,
            'user_id'               => $userId,
            'email'                 => $email,
            'nama'                  => $nama,
            'role'                  => $role,      // ← kunci utama untuk role check
            'peranan'               => $role,      // ← alias untuk keserasian view lama
            'supabase_access_token' => $authData['access_token']   ?? null,
            'supabase_refresh_token'=> $authData['refresh_token']  ?? null,
        ]);

        // Step 4: Redirect — semua role pergi ke /dashboard
        // DashboardController akan redirect ikut role
        return redirect()->intended('/dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Session::flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
