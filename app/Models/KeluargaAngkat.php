<?php

namespace App\Services;

use Carbon\Carbon;

class KeluargaAngkatService
{
    protected SupabaseService $db;

    public function __construct(SupabaseService $db)
    {
        $this->db = $db;
    }

    /**
     * Ambil semua rekod keluarga angkat dengan maklumat pelajar sekali.
     * Kerana Supabase REST tidak support JOIN terus, kita fetch dua jadual
     * kemudian gabungkan dalam PHP.
     */
    public function getAll(array $filters = []): array
    {
        // 1. Ambil semua keluarga angkat
        $params = [
            'select' => '*',
            'order'  => 'nama_keluarga_angkat.asc',
        ];

        if (!empty($filters['status'])) {
            $params['status_tajaan'] = 'eq.' . $filters['status'];
        }

        $keluarga = $this->db->select('keluarga_angkat', $params);
        if (empty($keluarga)) return [];

        // 2. Ambil semua pelajar untuk lookup nama
        $pelajarList = $this->db->select('pelajar', [
            'select' => 'id_pelajar,nama_pelajar,no_matrik',
        ]);

        $pelajarMap = [];
        foreach ($pelajarList as $p) {
            $pelajarMap[$p['id_pelajar']] = $p;
        }

        // 3. Gabungkan data + kira status
        return array_map(function ($k) use ($pelajarMap) {
            $pelajar = isset($k['id_pelajar']) ? ($pelajarMap[$k['id_pelajar']] ?? null) : null;
            $k['pelajar_nama']   = $pelajar['nama_pelajar'] ?? null;
            $k['pelajar_matrik'] = $pelajar['no_matrik'] ?? null;
            $k['status_display'] = $this->kiraSatus($k);
            return $k;
        }, $keluarga);
    }

    /**
     * Ambil rekod keluarga angkat yg BELUM ada pelajar (untuk dropdown tugaskan).
     */
    public function getTanpaPelajar(): array
    {
        return $this->db->select('keluarga_angkat', [
            'select'     => '*',
            'id_pelajar' => 'is.null',
            'order'      => 'nama_keluarga_angkat.asc',
        ]) ?? [];
    }

    /**
     * Ambil satu rekod berdasarkan ID.
     */
    public function getById(string $id): ?array
    {
        $rows = $this->db->select('keluarga_angkat', [
            'select'              => '*',
            'id_keluarga_angkat'  => "eq.{$id}",
            'limit'               => 1,
        ]);
        return $rows[0] ?? null;
    }

    /**
     * Simpan keluarga angkat baharu.
     */
    public function simpan(array $data): ?array
    {
        $payload = collect($data)->only([
            'nama_keluarga_angkat',
            'no_telefon',
            'alamat',
            'status_tajaan',
            'tarikh_tamat_tajaan',
            'id_pelajar',
        ])->filter(fn($v) => $v !== null && $v !== '')->toArray();

        // Set status default kalau kosong
        $payload['status_tajaan'] = $payload['status_tajaan'] ?? 'Aktif';

        return $this->db->insert('keluarga_angkat', $payload);
    }

    /**
     * Kemaskini rekod sedia ada.
     */
    public function kemaskini(string $id, array $data): ?array
    {
        $payload = collect($data)->only([
            'nama_keluarga_angkat',
            'no_telefon',
            'alamat',
            'status_tajaan',
            'tarikh_tamat_tajaan',
            'id_pelajar',
        ])->toArray();

        // Buang id_pelajar jika string kosong (supaya tak override dengan '')
        if (isset($payload['id_pelajar']) && $payload['id_pelajar'] === '') {
            $payload['id_pelajar'] = null;
        }

        return $this->db->update('keluarga_angkat', $id, $payload);
    }

    /**
     * Tugaskan pelajar kepada keluarga angkat.
     */
    public function tugaskan(string $keluargaId, string $pelajarId, ?string $tarikhMula = null, ?string $tarikhTamat = null): ?array
    {
        $payload = [
            'id_pelajar'          => (int) $pelajarId,
            'status_tajaan'       => 'Aktif',
        ];
        if ($tarikhTamat) $payload['tarikh_tamat_tajaan'] = $tarikhTamat;

        // Kemaskini pelajar juga dengan tarikh_tamat_tajaan
        if ($tarikhTamat) {
            $this->db->update('pelajar', $pelajarId, [
                'tarikh_tamat_tajaan' => $tarikhTamat,
            ]);
        }

        return $this->db->update('keluarga_angkat', $keluargaId, $payload);
    }

    /**
     * Padam rekod keluarga angkat.
     */
    public function padam(string $id): bool
    {
        return $this->db->delete('keluarga_angkat', $id);
    }

    /**
     * Cari berdasarkan nama.
     */
    public function cari(string $query): array
    {
        $rows = $this->db->select('keluarga_angkat', [
            'select'                => '*',
            'nama_keluarga_angkat'  => "ilike.*{$query}*",
        ]);
        return $rows ?? [];
    }

    /**
     * Kira status paparan berdasarkan tarikh_tamat_tajaan.
     */
    private function kiraSatus(array $k): string
    {
        $tamat = $k['tarikh_tamat_tajaan'] ?? null;

        if (!($k['id_pelajar'] ?? null)) {
            return 'Belum Ditugaskan';
        }

        if (!$tamat) {
            return $k['status_tajaan'] ?? 'Aktif';
        }

        $tarikhTamat = Carbon::parse($tamat);

        if ($tarikhTamat->isPast()) {
            return 'Tamat';
        }

        // Hampir tamat = dalam masa 2 bulan
        if ($tarikhTamat->diffInMonths(now()) <= 2) {
            return 'Hampir Tamat';
        }

        return 'Aktif';
    }

    /**
     * Ambil statistik ringkas untuk dashboard/header.
     */
    public function getStats(): array
    {
        $all = $this->getAll();
        return [
            'jumlah'           => count($all),
            'aktif'            => count(array_filter($all, fn($k) => $k['status_display'] === 'Aktif')),
            'hampir_tamat'     => count(array_filter($all, fn($k) => $k['status_display'] === 'Hampir Tamat')),
            'belum_ditugaskan' => count(array_filter($all, fn($k) => $k['status_display'] === 'Belum Ditugaskan')),
        ];
    }
}
