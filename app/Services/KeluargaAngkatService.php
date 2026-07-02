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
     * Ambil semua rekod keluarga angkat + nama pelajar.
     */
    public function getAll(array $filters = []): array
    {
        $params = [
            'select' => '*',
            'order'  => 'nama_keluarga_angkat.asc',
        ];

        if (!empty($filters['status'])) {
            $params['status_tajaan'] = 'eq.' . $filters['status'];
        }

        $keluarga = $this->db->select('keluarga_angkat', $params);
        if (empty($keluarga)) return [];

        // Ambil semua pelajar untuk lookup
        $pelajarList = $this->db->select('pelajar', [
            'select' => 'id_pelajar,nama_pelajar,no_matrik,tarikh_tamat_tajaan',
        ]);

        $pelajarMap = collect($pelajarList)->keyBy('id_pelajar')->toArray();

        return array_map(function ($k) use ($pelajarMap) {
            $p = isset($k['id_pelajar']) ? ($pelajarMap[$k['id_pelajar']] ?? null) : null;

            $k['pelajar_nama']   = $p['nama_pelajar'] ?? null;
            $k['pelajar_matrik'] = $p['no_matrik'] ?? null;

            // ── Tarikh tamat: ambil dari pelajar jika keluarga kosong ──
            if (empty($k['tarikh_tamat_tajaan']) && !empty($p['tarikh_tamat_tajaan'])) {
                $k['tarikh_tamat_tajaan'] = $p['tarikh_tamat_tajaan'];
            }

            $k['status_display'] = $this->kiraSatus($k);
            return $k;
        }, $keluarga);
    }

    /**
     * Ambil satu rekod berdasarkan ID.
     */
    public function getById(string $id): ?array
    {
        $rows = $this->db->select('keluarga_angkat', [
            'select'             => '*',
            'id_keluarga_angkat' => "eq.{$id}",
            'limit'              => 1,
        ]);
        $k = $rows[0] ?? null;
        if (!$k) return null;

        // Enrich dengan maklumat pelajar
        if (!empty($k['id_pelajar'])) {
            $pRows = $this->db->select('pelajar', [
                'select'     => '*',
                'id_pelajar' => "eq.{$k['id_pelajar']}",
                'limit'      => 1,
            ]);
            $p = $pRows[0] ?? null;
            if ($p) {
                $k['pelajar_nama']          = $p['nama_pelajar'];
                $k['pelajar_matrik']        = $p['no_matrik'];
                $k['pelajar_semester']      = $p['semester'];
                $k['pelajar_fakulti']       = $p['fakulti'] ?? null;
                $k['pelajar_program']       = $p['program'] ?? null;
                $k['pelajar_status']        = $p['status_pengajian'];

                // Sync tarikh dari pelajar jika keluarga tiada nilai
                if (empty($k['tarikh_tamat_tajaan']) && !empty($p['tarikh_tamat_tajaan'])) {
                    $k['tarikh_tamat_tajaan'] = $p['tarikh_tamat_tajaan'];
                }
            }
        }

        $k['status_display'] = $this->kiraSatus($k);
        return $k;
    }

    /**
     * Simpan keluarga angkat baharu.
     * Guna RPC admin_tambah_keluarga_angkat jika email disertakan,
     * supaya akaun auth terus dicipta.
     * Jika tiada email, insert terus ke jadual (tanpa akaun).
     */
    public function simpan(array $data): ?array
    {
        $email = trim($data['email'] ?? '');

        if ($email !== '') {
            // ── Guna RPC untuk cipta akaun + rekod sekaligus ──────────
            $result = $this->db->rpc('admin_tambah_keluarga_angkat', [
                'p_email'      => $email,
                'p_password'   => $data['password'] ?? 'Protege@' . now()->format('Y'),
                'p_nama'       => $data['nama_keluarga_angkat'],
                'p_no_telefon' => $data['no_telefon']   ?? null,
                'p_alamat'     => $data['alamat']        ?? null,
                'p_status'     => $data['status_tajaan'] ?? 'Aktif',
                'p_id_pelajar' => !empty($data['id_pelajar']) ? (int) $data['id_pelajar'] : null,
            ]);

            if (!$result || empty($result['berjaya'])) {
                \Illuminate\Support\Facades\Log::error('RPC admin_tambah_keluarga_angkat gagal', $result ?? []);
                return null;
            }

            // Sync medan yang RPC tak sokong (tarikh_tamat_tajaan, jabatan, no_ic, hide_identity)
            $syncPayload = [];
            if (!empty($data['tarikh_tamat_tajaan'])) {
                $syncPayload['tarikh_tamat_tajaan'] = $data['tarikh_tamat_tajaan'];
            }
            if (array_key_exists('jabatan', $data) && $data['jabatan'] !== '') {
                $syncPayload['jabatan'] = $data['jabatan'];
            }
            if (array_key_exists('no_ic', $data) && $data['no_ic'] !== '') {
                $syncPayload['no_ic'] = $data['no_ic'];
            }
            if (array_key_exists('hide_identity', $data)) {
                $syncPayload['hide_identity'] = (bool) $data['hide_identity'];
            }
            if (!empty($syncPayload) && !empty($result['id_keluarga_angkat'])) {
                $this->db->update('keluarga_angkat', (string) $result['id_keluarga_angkat'], $syncPayload);
            }

            return [
                'id_keluarga_angkat'   => $result['id_keluarga_angkat'],
                'nama_keluarga_angkat' => $data['nama_keluarga_angkat'],
            ];
        }

        // ── Tiada email: insert terus tanpa cipta akaun ───────────────
        $payload = [
            'nama_keluarga_angkat' => $data['nama_keluarga_angkat'],
            'no_telefon'           => $data['no_telefon']        ?? null,
            'alamat'               => $data['alamat']            ?? null,
            'status_tajaan'        => $data['status_tajaan']     ?? 'Aktif',
            'jabatan'              => $data['jabatan']            ?? null,
            'no_ic'                => $data['no_ic']              ?? null,
            'hide_identity'        => (bool) ($data['hide_identity'] ?? false),
        ];

        if (!empty($data['id_pelajar'])) {
            $payload['id_pelajar'] = (int) $data['id_pelajar'];
        }

        if (!empty($data['tarikh_tamat_tajaan'])) {
            $payload['tarikh_tamat_tajaan'] = $data['tarikh_tamat_tajaan'];
        }

        return $this->db->insert('keluarga_angkat', $payload);
    }

    /**
     * Kemaskini rekod sedia ada.
     */
    public function kemaskini(string $id, array $data): ?array
    {
        $payload = [];

        if (isset($data['nama_keluarga_angkat'])) $payload['nama_keluarga_angkat'] = $data['nama_keluarga_angkat'];
        if (isset($data['no_telefon']))            $payload['no_telefon']           = $data['no_telefon'];
        if (isset($data['alamat']))                $payload['alamat']               = $data['alamat'];
        if (isset($data['status_tajaan']))         $payload['status_tajaan']        = $data['status_tajaan'];
        if (isset($data['jabatan']))                $payload['jabatan']              = $data['jabatan'] ?: null;
        if (isset($data['no_ic']))                  $payload['no_ic']                = $data['no_ic'] ?: null;
        if (array_key_exists('hide_identity', $data)) $payload['hide_identity']      = (bool) $data['hide_identity'];

        // Tarikh tamat — kena eksplisit hantar null jika kosong
        if (array_key_exists('tarikh_tamat_tajaan', $data)) {
            $payload['tarikh_tamat_tajaan'] = $data['tarikh_tamat_tajaan'] ?: null;
        }

        // id_pelajar — jangan hantar string kosong
        if (array_key_exists('id_pelajar', $data)) {
            $payload['id_pelajar'] = $data['id_pelajar'] !== '' ? (int) $data['id_pelajar'] : null;
        }

        $result = $this->db->update('keluarga_angkat', $id, $payload);

        // Sync tarikh ke jadual pelajar juga jika pelajar ada
        if ($result && !empty($payload['tarikh_tamat_tajaan']) && !empty($payload['id_pelajar'])) {
            $this->db->update('pelajar', (string) $payload['id_pelajar'], [
                'tarikh_tamat_tajaan' => $payload['tarikh_tamat_tajaan'],
            ]);
        }

        return $result;
    }

    /**
     * Tugaskan pelajar kepada keluarga angkat.
     */
    public function tugaskan(string $keluargaId, string $pelajarId, ?string $tarikhTamat = null): ?array
    {
        // Ambil tarikh_tamat dari pelajar kalau tak dihantar
        if (!$tarikhTamat) {
            $pRows = $this->db->select('pelajar', [
                'select'     => 'tarikh_tamat_tajaan',
                'id_pelajar' => "eq.{$pelajarId}",
                'limit'      => 1,
            ]);
            $tarikhTamat = $pRows[0]['tarikh_tamat_tajaan'] ?? null;
        }

        $payload = [
            'id_pelajar'    => (int) $pelajarId,
            'status_tajaan' => 'Aktif',
        ];
        if ($tarikhTamat) {
            $payload['tarikh_tamat_tajaan'] = $tarikhTamat;
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
            'select'               => '*',
            'nama_keluarga_angkat' => "ilike.*{$query}*",
        ]);

        if (empty($rows)) return [];

        $pelajarList = $this->db->select('pelajar', [
            'select' => 'id_pelajar,nama_pelajar,no_matrik,tarikh_tamat_tajaan',
        ]);
        $pelajarMap = collect($pelajarList)->keyBy('id_pelajar')->toArray();

        return array_map(function ($k) use ($pelajarMap) {
            $p = $pelajarMap[$k['id_pelajar'] ?? null] ?? null;
            $k['pelajar_nama']   = $p['nama_pelajar'] ?? null;
            $k['pelajar_matrik'] = $p['no_matrik'] ?? null;
            if (empty($k['tarikh_tamat_tajaan']) && !empty($p['tarikh_tamat_tajaan'])) {
                $k['tarikh_tamat_tajaan'] = $p['tarikh_tamat_tajaan'];
            }
            $k['status_display'] = $this->kiraSatus($k);
            return $k;
        }, $rows);
    }

    /**
     * Kira status paparan automatik.
     */
    private function kiraSatus(array $k): string
    {
        if (!($k['id_pelajar'] ?? null)) {
            return 'Belum Ditugaskan';
        }

        $tamat = $k['tarikh_tamat_tajaan'] ?? null;
        if (!$tamat) {
            return $k['status_tajaan'] ?? 'Aktif';
        }

        $tarikhTamat = Carbon::parse($tamat);

        if ($tarikhTamat->isPast()) {
            return 'Tamat';
        }

        // Hampir tamat = 2 bulan atau kurang dari sekarang
        if (now()->diffInMonths($tarikhTamat, false) <= 2) {
            return 'Hampir Tamat';
        }

        return 'Aktif';
    }

    /**
     * Statistik untuk header cards.
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
