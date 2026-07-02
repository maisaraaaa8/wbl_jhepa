<?php

namespace App\Services;

class PelajarService
{
    protected SupabaseService $db;

    public function __construct(SupabaseService $db)
    {
        $this->db = $db;
    }

    // ── Ambil semua pelajar + inject keluarga angkat + GPA terkini ─────
    public function getAll(array $filters = []): array
    {
        $params = [
            'select' => '*',
            'order'  => 'nama_pelajar.asc',
        ];

        if (!empty($filters['status'])) {
            $params['status_pengajian'] = 'eq.' . $filters['status'];
        }
        if (!empty($filters['semester'])) {
            $params['semester'] = 'eq.' . $filters['semester'];
        }
        // ── Tapisan status kelulusan mesyuarat (baharu) ──
        if (!empty($filters['kelulusan'])) {
            $params['tarikh_mesyuarat_diluluskan'] = $filters['kelulusan'] === 'diluluskan'
                ? 'not.is.null'
                : 'is.null';
        }

        $pelajar = $this->db->select('pelajar', $params) ?? [];

        if (empty($pelajar)) return [];

        // Ambil SEMUA keluarga angkat sekali gus (lebih efisien)
        $semuaKa = $this->db->select('keluarga_angkat', [
            'select' => 'id_pelajar, nama_keluarga_angkat, no_telefon, status_tajaan, tarikh_tamat_tajaan',
        ]) ?? [];

        // Index keluarga angkat by id_pelajar
        $kaByPelajar = [];
        foreach ($semuaKa as $ka) {
            if (!empty($ka['id_pelajar'])) {
                $kaByPelajar[$ka['id_pelajar']] = $ka;
            }
        }

        // Ambil SEMUA GPA (cgpa) sekali gus
        $semuaGpa = $this->db->select('prestasi', [
            'select' => 'id_pelajar, gpa, cgpa, semester',
            'order'  => 'id_pelajar.asc,id.desc',
        ]) ?? [];

        // Ambil GPA terkini per pelajar
        $gpaByPelajar = [];
        foreach ($semuaGpa as $g) {
            $pid = $g['id_pelajar'];
            // Ambil yang pertama jumpa (sudah order id.desc = terkini)
            if (!isset($gpaByPelajar[$pid])) {
                $gpaByPelajar[$pid] = floatval($g['gpa'] ?? 0);
            }
        }

        // Inject data ke setiap pelajar
        return array_map(function ($p) use ($kaByPelajar, $gpaByPelajar) {
            $pid = $p['id_pelajar'];

            $ka = $kaByPelajar[$pid] ?? null;

            $p['nama_keluarga']    = $ka['nama_keluarga_angkat'] ?? null;
            $p['no_telefon_ka']    = $ka['no_telefon']           ?? null;
            $p['status_tajaan']    = $ka['status_tajaan']        ?? null;
            $p['tarikh_tamat_ka']  = $ka['tarikh_tamat_tajaan']  ?? null;
            $p['latest_gpa']       = $gpaByPelajar[$pid]         ?? 0;

            return $p;
        }, $pelajar);
    }

    // ── Cari pelajar (dengan inject sama) ─────────────────────────────
    public function cari(string $query): array
    {
        $byNama   = $this->db->select('pelajar', ['select' => '*', 'nama_pelajar' => "ilike.*{$query}*"]) ?? [];
        $byMatrik = $this->db->select('pelajar', ['select' => '*', 'no_matrik'    => "ilike.*{$query}*"]) ?? [];
        $byIc     = $this->db->select('pelajar', ['select' => '*', 'no_ic'        => "ilike.*{$query}*"]) ?? [];

        $all = array_merge($byNama, $byMatrik, $byIc);
        $ids = [];
        $unik = array_values(array_filter($all, function ($p) use (&$ids) {
            if (in_array($p['id_pelajar'], $ids)) return false;
            $ids[] = $p['id_pelajar'];
            return true;
        }));

        if (empty($unik)) return [];

        // Inject keluarga angkat & GPA
        $semuaKa = $this->db->select('keluarga_angkat', [
            'select' => 'id_pelajar, nama_keluarga_angkat, no_telefon, status_tajaan, tarikh_tamat_tajaan',
        ]) ?? [];
        $kaByPelajar = [];
        foreach ($semuaKa as $ka) {
            if (!empty($ka['id_pelajar'])) $kaByPelajar[$ka['id_pelajar']] = $ka;
        }

        $semuaGpa = $this->db->select('prestasi', [
            'select' => 'id_pelajar, gpa',
            'order'  => 'id_pelajar.asc,id.desc',
        ]) ?? [];
        $gpaByPelajar = [];
        foreach ($semuaGpa as $g) {
            if (!isset($gpaByPelajar[$g['id_pelajar']])) {
                $gpaByPelajar[$g['id_pelajar']] = floatval($g['gpa'] ?? 0);
            }
        }

        return array_map(function ($p) use ($kaByPelajar, $gpaByPelajar) {
            $pid = $p['id_pelajar'];
            $ka  = $kaByPelajar[$pid] ?? null;
            $p['nama_keluarga']   = $ka['nama_keluarga_angkat'] ?? null;
            $p['no_telefon_ka']   = $ka['no_telefon']           ?? null;
            $p['status_tajaan']   = $ka['status_tajaan']        ?? null;
            $p['tarikh_tamat_ka'] = $ka['tarikh_tamat_tajaan']  ?? null;
            $p['latest_gpa']      = $gpaByPelajar[$pid]         ?? 0;
            return $p;
        }, $unik);
    }

    public function getById(string $id): ?array
    {
        return $this->db->find('pelajar', $id);
    }

    /**
     * Simpan pelajar baharu.
     * Guna RPC admin_tambah_pelajar jika email disertakan,
     * supaya akaun login (auth) terus dicipta & ditautkan (user_id).
     * Jika tiada email, insert terus ke jadual (tanpa akaun login) —
     * sama macam KeluargaAngkatService::simpan().
     */
    public function simpan(array $data): ?array
    {
        if (empty($data['nama_pelajar'])) return null;

        $email = trim($data['email'] ?? '');

        if ($email !== '') {
            // ── Guna RPC untuk cipta akaun auth + rekod pelajar sekaligus ──
            $result = $this->db->rpc('admin_tambah_pelajar', [
                'p_email'               => $email,
                'p_password'            => $data['password'] ?? 'Protege@' . now()->format('Y'),
                'p_nama_pelajar'        => $data['nama_pelajar'],
                'p_no_matrik'           => $data['no_matrik']           ?? null,
                'p_semester'            => $data['semester']            ?? null,
                'p_status_pengajian'    => $data['status_pengajian']    ?? 'Aktif',
                'p_tarikh_tamat_tajaan' => $data['tarikh_tamat_tajaan'] ?: null,
                'p_fakulti'             => $data['fakulti']             ?? null,
                'p_program'             => $data['program']             ?? null,
            ]);

            if (!$result || empty($result['berjaya'])) {
                \Illuminate\Support\Facades\Log::error('RPC admin_tambah_pelajar gagal', $result ?? []);
                return null;
            }

            // ── RPC admin_tambah_pelajar tidak menyokong medan baharu ──
            // (no_ic, alamat, tarikh_mesyuarat_diluluskan), jadi kemaskini
            // terus selepas rekod & akaun berjaya dicipta.
            $medanTambahan = [];
            foreach (['no_ic', 'alamat', 'tarikh_mesyuarat_diluluskan'] as $key) {
                if (!empty($data[$key])) {
                    $medanTambahan[$key] = $data[$key];
                }
            }
            if (!empty($medanTambahan) && !empty($result['id_pelajar'])) {
                $this->db->update('pelajar', $result['id_pelajar'], $medanTambahan);
            }

            return [
                'id_pelajar'   => $result['id_pelajar'],
                'nama_pelajar' => $data['nama_pelajar'],
            ];
        }

        // ── Tiada email: insert terus tanpa cipta akaun login ─────────
        $allowed = [
            'user_id', 'nama_pelajar', 'no_matrik', 'semester',
            'status_pengajian', 'tarikh_tamat_tajaan', 'fakulti', 'program',
            'no_ic', 'alamat', 'tarikh_mesyuarat_diluluskan',   // ← baharu
        ];
        $pelajarData = [];
        foreach ($allowed as $key) {
            if (isset($data[$key]) && $data[$key] !== '') {
                $pelajarData[$key] = $data[$key];
            }
        }
        $pelajarData['status_pengajian'] ??= 'Aktif';
        return $this->db->insert('pelajar', $pelajarData);
    }

    public function kemaskini(string $id, array $data): ?array
    {
        $allowed = [
            'nama_pelajar', 'no_matrik', 'semester',
            'status_pengajian', 'tarikh_tamat_tajaan', 'fakulti', 'program',
            'no_ic', 'alamat', 'tarikh_mesyuarat_diluluskan',   // ← baharu
        ];
        $pelajarData = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $pelajarData[$key] = ($data[$key] === '') ? null : $data[$key];
            }
        }
        return $this->db->update('pelajar', $id, $pelajarData);
    }

    public function padam(string $id): bool
    {
        return $this->db->delete('pelajar', $id);
    }
}
