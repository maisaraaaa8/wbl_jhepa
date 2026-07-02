<?php

namespace App\Http\Controllers;

use App\Helpers\SessionUser;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\SupabaseAuthService;
use App\Services\SupabaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    protected SupabaseService $db;

    public function __construct(SupabaseService $db)
    {
        $this->db = $db;
    }

    // ─── BAHAGIAN BARU: Profil dalam sistem ───────────────────────
    // PENTING: Auth::user() SENTIASA null dalam sistem ini kerana login
    // dibuat melalui Supabase Auth API (bukan Auth::login() Laravel).
    // Maklumat pengguna disimpan dalam session (lihat SessionUser) dan
    // dalam table 'profiles' di Supabase — bukan dalam table 'users' Eloquent.

    /**
     * Papar halaman profil sistem
     */
    public function index(): View
    {
        $userId  = SessionUser::id();
        $profile = $userId ? $this->db->find('profiles', $userId, 'id,nama,email,no_matrik,role') : null;

        // Guna data dari Supabase jika ada, kalau tidak jatuh balik ke session
        $user = (object) [
            'nama'      => $profile['nama']      ?? SessionUser::nama(),
            'email'     => $profile['email']     ?? SessionUser::email(),
            'no_matrik' => $profile['no_matrik'] ?? null,
            'role'      => $profile['role']      ?? SessionUser::role(),
        ];

        return view('profil.index', compact('user'));
    }

    /**
     * Kemaskini nama, email, no_matrik
     */
    public function kemaskini(Request $request): RedirectResponse
    {
        $userId = SessionUser::id();

        if (!$userId) {
            return redirect('/login');
        }

        $request->validate([
            'nama'      => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'no_matrik' => 'nullable|string|max:50',
        ]);

        $data = [
            'nama'      => $request->nama,
            'email'     => $request->email,
            'no_matrik' => $request->no_matrik,
        ];

        $result = $this->db->update('profiles', $userId, $data);

        if (!$result) {
            return back()->withInput()->with('error', 'Gagal mengemaskini profil.');
        }

        // Kemaskini session supaya paparan lain (topbar, avatar) turut segerak
        session([
            'nama'      => $data['nama'],
            'email'     => $data['email'],
            'no_matrik' => $data['no_matrik'],
        ]);

        return redirect()->route('profile.index')->with('success', 'Profil berjaya dikemaskini.');
    }

    /**
     * Tukar kata laluan sahaja.
     * Kata laluan sebenar disimpan dalam Supabase Auth (auth.users), bukan
     * dalam table 'profiles'. Jadi pengesahan & penukaran kata laluan
     * WAJIB melalui Supabase Auth API, bukan Hash::check() tempatan.
     */
    public function tukarPassword(Request $request, SupabaseAuthService $supabaseAuth): RedirectResponse
    {
        $request->validate([
            'current_password'  => 'required',
            'password'          => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Kata laluan semasa wajib diisi.',
            'password.required'         => 'Kata laluan baru wajib diisi.',
            'password.min'              => 'Kata laluan baru minimum 8 aksara.',
            'password.confirmed'        => 'Pengesahan kata laluan tidak sepadan.',
        ]);

        $userId = SessionUser::id();
        $email  = SessionUser::email();

        if (!$userId || !$email) {
            return redirect('/login');
        }

        // Sahkan kata laluan semasa dengan cuba log masuk ke Supabase Auth
        $check = $supabaseAuth->login($email, $request->current_password);
        if (!$check) {
            return back()->withErrors(['current_password' => 'Kata laluan semasa tidak betul.']);
        }

        $ok = $supabaseAuth->updatePassword($userId, $request->password);

        if (!$ok) {
            return back()->with('error', 'Gagal menukar kata laluan. Sila cuba lagi.');
        }

        return redirect()->route('profile.index')->with('success', 'Kata laluan berjaya ditukar.');
    }

    // ─── BAHAGIAN ASAL: Jangan ubah ───────────────────────────────

    /**
     * Display the user's profile form (Laravel Breeze default).
     */
    public function edit(Request $request): View
    {
       return view('profil.edit', [
        'user' => $request->user(),
]);
    }

    /**
     * Update the user's profile information (Laravel Breeze default).
     */
    // SELEPAS
public function update(ProfileUpdateRequest $request): RedirectResponse
{
    $user = $request->user();
    
    $validated = $request->validated();
    
    // Hanya update email sahaja (field yang ada dalam profiles)
    if (isset($validated['email'])) {
        $user->email = $validated['email'];
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
    }

    $user->save();

    return Redirect::route('profile.edit')->with('status', 'profile-updated');
}

    /**
     * Delete the user's account (Laravel Breeze default).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
