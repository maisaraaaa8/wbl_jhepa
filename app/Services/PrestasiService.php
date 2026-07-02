<?php

namespace App\Services;

class PrestasiService
{
    protected SupabaseService $db;

    const SENARAI_SEMESTER = [
        'Semester 1', 'Semester 2', 'Semester 3',
        'Semester 4', 'Semester 5', 'Semester 6',
    ];

    public function __construct(SupabaseService $db)
    {
        $this->db = $db;
    }

    // ── Ambil ringkasan CGPA setiap pelajar ───────────────────────────
    // Gabungkan data prestasi + nama pelajar, kira CGPA dari purata GPA
    public function getRingkasan(array $filters = []): array
    {
        $params = [
            'select' => '*, pelajar(nama_pelajar, no_matrik)',
            'order'  => 'id_pelajar.asc',
        ];

        if (!empty($filters['semester'])) {
            $params['semester'] = 'eq.' . $filters['semester'];
        }

        $rekod = $this->db->select('prestasi', $params) ?? [];

        // Kumpulkan mengikut id_pelajar
        $grouped = [];
        foreach ($rekod as $r) {
            $pid = $r['id_pelajar'];
            if (!isset($grouped[$pid])) {
                $grouped[$pid] = [
                    'id_pelajar'   => $pid,
                    'nama_pelajar' => $r['pelajar']['nama_pelajar'] ?? '—',
                    'no_matrik'    => $r['pelajar']['no_matrik']    ?? '—',
                    'semester'     => [],   // ['Semester 1' => 3.45, ...]
                    'gpa_list'     => [],
                ];
            }
            $grouped[$pid]['semester'][$r['semester']] = (float) $r['gpa'];
            $grouped[$pid]['gpa_list'][]               = (float) $r['gpa'];
        }

        // Kira CGPA & status
        $result = [];
        foreach ($grouped as $data) {
            $cgpa = count($data['gpa_list'])
                ? round(array_sum($data['gpa_list']) / count($data['gpa_list']), 2)
                : null;

            $result[] = [
                'id_pelajar'   => $data['id_pelajar'],
                'nama_pelajar' => $data['nama_pelajar'],
                'no_matrik'    => $data['no_matrik'],
                'semester'     => $data['semester'],
                'cgpa'         => $cgpa,
                'status'       => $cgpa !== null ? $this->statusDariGpa($cgpa) : '—',
            ];
        }

        usort($result, fn($a, $b) => strcmp($a['nama_pelajar'], $b['nama_pelajar']));

        return $result;
    }

    // ── Ambil semua rekod satu pelajar ────────────────────────────────
    public function getByPelajar(string $pelajarId): array
    {
        return $this->db->select('prestasi', [
            'select'     => '*',
            'id_pelajar' => "eq.{$pelajarId}",
            'order'      => 'semester.asc',
        ]) ?? [];
    }

    // ── Semak sama ada rekod semester sudah wujud ─────────────────────
    public function cariRekod(string $pelajarId, string $semester): ?array
    {
        $result = $this->db->select('prestasi', [
            'select'     => '*',
            'id_pelajar' => "eq.{$pelajarId}",
            'semester'   => "eq.{$semester}",
            'limit'      => 1,
        ]);
        return $result[0] ?? null;
    }

    // ── Simpan rekod baru ──────────────────────────────────────────────
    // DB anda ada column cgpa — kita simpan sama dengan gpa (akan dikira semula lepas semua sem)
    public function simpan(array $data): ?array
    {
        $gpa = (float) $data['gpa'];

        // Kira CGPA baru: purata semua GPA pelajar ini + yang baru
        $sedia   = $this->getByPelajar($data['id_pelajar']);
        $gpaList = array_column($sedia, 'gpa');
        $gpaList[] = $gpa;
        $cgpa    = round(array_sum($gpaList) / count($gpaList), 2);

        return $this->db->insert('prestasi', [
            'id_pelajar' => $data['id_pelajar'],
            'semester'   => $data['semester'],
            'gpa'        => $gpa,
            'cgpa'       => $cgpa,
        ]);
    }

    // ── Kemaskini GPA dan kira semula CGPA ───────────────────────────
    public function kemaskini(string $id, array $data): ?array
    {
        $gpa = (float) $data['gpa'];

        // Dapatkan id_pelajar untuk rekod ini
        $rekodSedia = $this->db->select('prestasi', [
            'select' => '*',
            'id'     => "eq.{$id}",
            'limit'  => 1,
        ]);
        $rekod = $rekodSedia[0] ?? null;

        if ($rekod) {
            // Kira semula CGPA (gantikan GPA rekod lama dengan yang baru)
            $semua   = $this->getByPelajar($rekod['id_pelajar']);
            $gpaList = [];
            foreach ($semua as $r) {
                $gpaList[] = ($r['id'] == $id) ? $gpa : (float) $r['gpa'];
            }
            $cgpa = round(array_sum($gpaList) / count($gpaList), 2);
        } else {
            $cgpa = $gpa;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey'        => config('services.supabase.key'),
                'Authorization' => 'Bearer ' . config('services.supabase.key'),
                'Content-Type'  => 'application/json',
                'Prefer'        => 'return=representation',
            ])->patch(
                rtrim(config('services.supabase.url'), '/') . "/rest/v1/prestasi?id=eq.{$id}",
                ['gpa' => $gpa, 'cgpa' => $cgpa]
            );

            if ($response->successful()) {
                // Kemaskini CGPA pada semua rekod pelajar yang sama
                if ($rekod) $this->kemaskiniSemuaCgpa($rekod['id_pelajar'], $cgpa);
                $result = $response->json();
                return is_array($result) ? ($result[0] ?? $result) : null;
            }
            return null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Prestasi UPDATE: " . $e->getMessage());
            return null;
        }
    }

    // ── Update CGPA pada semua rekod pelajar (supaya konsisten) ───────
    protected function kemaskiniSemuaCgpa(string $pelajarId, float $cgpa): void
    {
        try {
            \Illuminate\Support\Facades\Http::withHeaders([
                'apikey'        => config('services.supabase.key'),
                'Authorization' => 'Bearer ' . config('services.supabase.key'),
                'Content-Type'  => 'application/json',
            ])->patch(
                rtrim(config('services.supabase.url'), '/') . "/rest/v1/prestasi?id_pelajar=eq.{$pelajarId}",
                ['cgpa' => $cgpa]
            );
        } catch (\Throwable $e) {
            // Silent fail — CGPA akan dikira semula pada paparan
        }
    }

    // ── Padam rekod ────────────────────────────────────────────────────
    public function padam(string $id): bool
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey'        => config('services.supabase.key'),
                'Authorization' => 'Bearer ' . config('services.supabase.key'),
            ])->delete(
                rtrim(config('services.supabase.url'), '/') . "/rest/v1/prestasi?id=eq.{$id}"
            );
            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ── Statistik untuk kad atas ───────────────────────────────────────
    public function statistik(): array
    {
        $semua = $this->db->select('prestasi', ['select' => 'gpa']) ?? [];

        $gpas = array_column($semua, 'gpa');
        $purata = count($gpas) ? round(array_sum($gpas) / count($gpas), 2) : 0;

        $cemerlang = 0; $memuaskan = 0; $perlu = 0;
        foreach ($gpas as $g) {
            $g = (float) $g;
            if ($g >= 3.50)       $cemerlang++;
            elseif ($g >= 3.00)   $memuaskan++;
            else                   $perlu++;
        }

        return [
            'jumlah'          => count($semua),
            'cemerlang'       => $cemerlang,
            'memuaskan'       => $memuaskan,
            'perlu_perhatian' => $perlu,
            'purata_gpa'      => $purata,
        ];
    }

    // ── Helper status ─────────────────────────────────────────────────
    public function statusDariGpa(float $gpa): string
    {
        if ($gpa >= 3.50) return 'Cemerlang';
        if ($gpa >= 3.00) return 'Memuaskan';
        return 'Perlu Perhatian';
    }
}
