<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseAuthService
{
    protected string $url;
    protected string $key;       // anon/public key untuk auth endpoint
    protected string $serviceKey; // service_role key untuk ambil profile

    public function __construct()
    {
        $this->url        = rtrim(config('services.supabase.url', ''), '/');
        // Auth endpoint guna anon key (bukan service_role)
        // Kalau takde anon key berasingan, guna service_role key — ia juga boleh
        $this->key        = config('services.supabase.anon_key',
                            config('services.supabase.key', ''));
        $this->serviceKey = config('services.supabase.key', '');
    }

    /**
     * Login via Supabase Auth — ini yang betul untuk Supabase
     * Supabase simpan password dalam auth.users, BUKAN dalam table profiles
     * Jadi WAJIB call API ini, tidak boleh guna Auth::attempt() biasa
     */
    public function login(string $email, string $password): ?array
    {
        if (!$this->url || !$this->key) {
            Log::error('SupabaseAuthService: URL atau KEY tidak dikonfigurasi');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'apikey'       => $this->key,
                'Content-Type' => 'application/json',
            ])->post("{$this->url}/auth/v1/token?grant_type=password", [
                'email'    => $email,
                'password' => $password,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                // Response mengandungi: access_token, refresh_token, user{}
                return $data;
            }

            // Log ralat sebenar dari Supabase
            Log::warning('SupabaseAuthService login gagal', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'email'  => $email,
            ]);
            return null;

        } catch (\Throwable $e) {
            Log::error('SupabaseAuthService exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Ambil profil pengguna dari table profiles menggunakan user_id
     * Guna service_role key untuk bypass RLS
     */
    public function getProfile(string $userId): ?array
    {
        try {
            $response = Http::withHeaders([
                'apikey'        => $this->serviceKey,
                'Authorization' => 'Bearer ' . $this->serviceKey,
                'Content-Type'  => 'application/json',
            ])->get("{$this->url}/rest/v1/profiles", [
                'select' => 'id, email, nama, role',
                'id'     => "eq.{$userId}",
                'limit'  => 1,
            ]);

            if ($response->successful()) {
                $rows = $response->json();
                return $rows[0] ?? null;
            }

            Log::warning('SupabaseAuthService getProfile gagal', [
                'status'  => $response->status(),
                'body'    => $response->body(),
                'user_id' => $userId,
            ]);
            return null;

        } catch (\Throwable $e) {
            Log::error('SupabaseAuthService getProfile exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Tukar kata laluan pengguna melalui Supabase Auth Admin API.
     * Guna service_role key kerana endpoint admin ini WAJIB akses peringkat server.
     */
    public function updatePassword(string $userId, string $newPassword): bool
    {
        if (!$this->url || !$this->serviceKey) {
            Log::error('SupabaseAuthService: URL atau SERVICE KEY tidak dikonfigurasi untuk updatePassword');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'apikey'        => $this->serviceKey,
                'Authorization' => 'Bearer ' . $this->serviceKey,
                'Content-Type'  => 'application/json',
            ])->put("{$this->url}/auth/v1/admin/users/{$userId}", [
                'password' => $newPassword,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('SupabaseAuthService updatePassword gagal', [
                'status'  => $response->status(),
                'body'    => $response->body(),
                'user_id' => $userId,
            ]);
            return false;

        } catch (\Throwable $e) {
            Log::error('SupabaseAuthService updatePassword exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kalau profile belum wujud dalam table profiles,
     * buat satu rekod baru (auto-create on first login)
     */
    public function ensureProfile(string $userId, string $email, string $role = 'readonly'): ?array
    {
        // Cuba ambil dulu
        $profile = $this->getProfile($userId);
        if ($profile) return $profile;

        // Belum ada — buat rekod baru
        try {
            $response = Http::withHeaders([
                'apikey'        => $this->serviceKey,
                'Authorization' => 'Bearer ' . $this->serviceKey,
                'Content-Type'  => 'application/json',
                'Prefer'        => 'return=representation',
            ])->post("{$this->url}/rest/v1/profiles", [
                'id'    => $userId,
                'email' => $email,
                'nama'  => explode('@', $email)[0],
                'role'  => $role,
            ]);

            if ($response->successful()) {
                $rows = $response->json();
                return is_array($rows) ? ($rows[0] ?? null) : null;
            }

            // Mungkin dah wujud (race condition) — cuba ambil semula
            return $this->getProfile($userId);

        } catch (\Throwable $e) {
            Log::error('SupabaseAuthService ensureProfile exception: ' . $e->getMessage());
            return null;
        }
    }
}
