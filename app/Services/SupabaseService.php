<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseService
{
    protected ?string $url;
    protected ?string $key;
    protected ?string $secret;

    public function __construct()
    {
        $this->url    = rtrim(config('services.supabase.url', ''), '/') ?: null;
        $this->key    = config('services.supabase.key', null);
        $this->secret = config('services.supabase.secret', null);
    }

    protected function headers(bool $useSecret = false): array
    {
        $token = ($useSecret && $this->secret) ? $this->secret : $this->key;
        return [
            'apikey'        => $this->key ?? '',
            'Authorization' => 'Bearer ' . ($token ?? ''),
            'Content-Type'  => 'application/json',
        ];
    }

    protected function baseUrl(string $table): string
    {
        return "{$this->url}/rest/v1/{$table}";
    }

    // ── pk(): SATU return sahaja — ini punca utama error edit ──────────
    protected function pk(string $table): string
    {
        return match($table) {
            'pelajar'         => 'id_pelajar',
            'keluarga_angkat' => 'id_keluarga_angkat',
            default           => 'id',
        };
    }

    public function select(string $table, array $params = []): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get($this->baseUrl($table), $params);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::error("Supabase SELECT error [{$table}]", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        } catch (\Throwable $e) {
            Log::error("Supabase SELECT exception [{$table}]: " . $e->getMessage());
            return [];
        }
    }

    public function find(string $table, string $id, string $select = '*'): ?array
    {
        $pk     = $this->pk($table);
        $result = $this->select($table, [
            'select' => $select,
            $pk      => "eq.{$id}",
            'limit'  => 1,
        ]);
        return $result[0] ?? null;
    }

    public function count(string $table, array $filters = []): int
    {
        try {
            $response = Http::withHeaders(array_merge($this->headers(), [
                'Prefer' => 'count=exact',
            ]))->get($this->baseUrl($table), array_merge($filters, [
                'select' => $this->pk($table),
            ]));

            $range = $response->header('Content-Range');
            if ($range && str_contains($range, '/')) {
                return (int) explode('/', $range)[1];
            }
            return count($response->json() ?? []);
        } catch (\Throwable $e) {
            Log::error("Supabase COUNT exception [{$table}]: " . $e->getMessage());
            return 0;
        }
    }

    public function sum(string $table, string $column, array $filters = []): float
    {
        $rows = $this->select($table, array_merge($filters, ['select' => $column]));
        return collect($rows)->sum($column);
    }

    public function insert(string $table, array $data): ?array
    {
        try {
            $response = Http::withHeaders(array_merge($this->headers(true), [
                'Prefer' => 'return=representation',
            ]))->post($this->baseUrl($table), $data);

            // HTTP 2xx = berjaya, walaupun body kosong atau []
            if ($response->successful()) {
                $result = $response->json();

                // Kalau Supabase return data, guna itu
                if (is_array($result) && count($result) > 0) {
                    return $result[0];
                }

                // Kalau body kosong/[] — masih berjaya, return sentinel
                return ['inserted' => true];
            }

            Log::error("Supabase INSERT error [{$table}]", [
                'status' => $response->status(),
                'body'   => $response->body(),
                'data'   => $data,
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::error("Supabase INSERT exception [{$table}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * UPDATE rekod dalam Supabase.
     *
     * PENTING: Supabase PATCH dengan 'return=representation' kadang-kadang
     * return array kosong [] walaupun berjaya (contoh: bila data sama).
     * Jadi kita semak HTTP status, BUKAN kandungan body.
     * Kembalikan ['updated' => true] supaya caller tahu berjaya.
     */
    public function update(string $table, string $id, array $data): ?array
    {
        try {
            $pk       = $this->pk($table);
            $response = Http::withHeaders(array_merge($this->headers(true), [
                'Prefer' => 'return=representation',
            ]))->patch($this->baseUrl($table) . "?{$pk}=eq.{$id}", $data);

            // HTTP 2xx = berjaya, walaupun body kosong
            if ($response->successful()) {
                $result = $response->json();

                // Kalau Supabase return data, guna itu
                if (is_array($result) && count($result) > 0) {
                    return $result[0];
                }

                // Kalau body kosong/null — masih berjaya, return sentinel
                return ['updated' => true, 'id' => $id];
            }

            Log::error("Supabase UPDATE error [{$table}]", [
                'status' => $response->status(),
                'body'   => $response->body(),
                'pk'     => $pk,
                'id'     => $id,
                'data'   => $data,
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::error("Supabase UPDATE exception [{$table}]: " . $e->getMessage());
            return null;
        }
    }

    public function delete(string $table, string $id): bool
    {
        try {
            $pk       = $this->pk($table);
            $response = Http::withHeaders($this->headers(true))
                ->delete($this->baseUrl($table) . "?{$pk}=eq.{$id}");
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("Supabase DELETE exception [{$table}]: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Panggil Supabase RPC (PostgreSQL function).
     * Guna service_role key supaya bypass RLS.
     */
    public function rpc(string $function, array $params = []): ?array
    {
        try {
            $url      = "{$this->url}/rest/v1/rpc/{$function}";
            $response = Http::withHeaders($this->headers(true))
                ->post($url, $params);

            if ($response->successful()) {
                $result = $response->json();
                if (is_array($result))  return $result;
                if (is_null($result))   return null;
                if (is_string($result)) {
                    $decoded = json_decode($result, true);
                    return is_array($decoded) ? $decoded : ['result' => $result];
                }
                return ['result' => $result];
            }

            Log::error("Supabase RPC error [{$function}]", [
                'status' => $response->status(),
                'body'   => $response->body(),
                'params' => $params,
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::error("Supabase RPC exception [{$function}]: " . $e->getMessage());
            return null;
        }
    }
}
