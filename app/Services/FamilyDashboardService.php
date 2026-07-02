<?php

namespace App\Services;

use Carbon\Carbon;

class FamilyDashboardService
{
    protected SupabaseService $db;

    public function __construct(SupabaseService $db)
    {
        $this->db = $db;
    }

    public function getDashboardForUser($user): array
    {
        $rekodKeluarga = $this->cariRekodKeluarga($user);

        if (empty($rekodKeluarga)) {
            return $this->kosong();
        }

        // NOTA: id_pelajar dipakai sebagai kunci utama untuk semua query
        // sokongan (sumbangan, meeting, prestasi) sebab lajur ini disahkan
        // wujud pada semua jadual berkaitan. id_keluarga_angkat tidak
        // dipakai untuk filter supaya kod ini tahan walaupun struktur
        // jadual sumbangan berbeza sedikit di persekitaran admin.
        $idPelajarList = array_values(array_unique(array_filter(
            array_column($rekodKeluarga, 'id_pelajar')
        )));

        // ── Maklumat pelajar ───────────────────────────────────
        $pelajarList = [];
        if (!empty($idPelajarList)) {
            $pelajarList = $this->db->select('pelajar', [
                'select'     => '*',
                'id_pelajar' => 'in.(' . implode(',', $idPelajarList) . ')',
            ]) ?? [];
        }
        $pelajarMap = collect($pelajarList)->keyBy('id_pelajar')->toArray();

        // ── GPA terkini setiap pelajar ──────────────────────────
        $gpaByPelajar = [];
        if (!empty($idPelajarList)) {
            $prestasi = $this->db->select('prestasi', [
                'select'     => 'id_pelajar,gpa,semester',
                'id_pelajar' => 'in.(' . implode(',', $idPelajarList) . ')',
                'order'      => 'id_pelajar.asc,semester.desc',
            ]) ?? [];

            foreach ($prestasi as $row) {
                $pid = $row['id_pelajar'] ?? null;
                if ($pid !== null && !isset($gpaByPelajar[$pid])) {
                    $gpaByPelajar[$pid] = floatval($row['gpa'] ?? 0);
                }
            }
        }

        // ── Sumbangan bulan ini (ikut id_pelajar — lajur dijamin wujud) ──
        $bulanIni  = now()->format('Y-m');
        $sumbangan = [];
        if (!empty($idPelajarList)) {
            $sumbangan = $this->db->select('sumbangan', [
                'select'     => '*',
                'id_pelajar' => 'in.(' . implode(',', $idPelajarList) . ')',
            ]) ?? [];
        }
        $jumlahSumbanganBulanIni = collect($sumbangan)
            ->filter(fn ($s) => str_starts_with($s['tarikh_terima'] ?? '', $bulanIni))
            ->sum('jumlah');

        $jumlahSumbanganKeseluruhan = collect($sumbangan)->sum('jumlah');

        // ── Sesi temu janji (meeting_record) ────────────────────
        $today    = now()->format('Y-m-d');
        $meetings = [];
        if (!empty($idPelajarList)) {
            $meetings = $this->db->select('meeting_record', [
                'select'     => '*',
                'id_pelajar' => 'in.(' . implode(',', $idPelajarList) . ')',
                'order'      => 'tarikh_pertemuan.asc',
            ]) ?? [];
        }

        $akanDatang = collect($meetings)
            ->filter(fn ($m) => ($m['tarikh_pertemuan'] ?? '') >= $today)
            ->map(function ($m) use ($pelajarMap) {
                $p = $pelajarMap[$m['id_pelajar'] ?? null] ?? null;
                $m['pelajar_nama'] = $p['nama_pelajar'] ?? '—';
                return $m;
            })
            ->sortBy('tarikh_pertemuan')
            ->values();

        // ── Susun senarai pelajar di bawah jagaan ───────────────
        $batas = now()->addDays(60)->format('Y-m-d');
        $warnaKitaran = ['c1', 'c2', 'c3'];
        $senaraiPelajar = [];
        $i = 0;

        foreach ($rekodKeluarga as $k) {
            $pid = $k['id_pelajar'] ?? null;
            if (!$pid || !isset($pelajarMap[$pid])) {
                continue;
            }
            $p = $pelajarMap[$pid];

            $tamat = $k['tarikh_tamat_tajaan'] ?? $p['tarikh_tamat_tajaan'] ?? null;

            $statusLabel = 'Aktif';
            $statusBadge = 'green';
            $hariBerbaki = null;

            if ($tamat) {
                if ($tamat < $today) {
                    $statusLabel = 'Tajaan Tamat';
                    $statusBadge = 'red';
                } elseif ($tamat <= $batas) {
                    $statusLabel = 'Tajaan tamat tidak lama';
                    $statusBadge = 'warn';
                    $hariBerbaki = (int) now()->diffInDays(Carbon::parse($tamat), false);
                }
            }

            $senaraiPelajar[] = [
                'id_pelajar'   => $pid,
                'nama_pelajar' => $p['nama_pelajar'] ?? '—',
                'no_matrik'    => $p['no_matrik'] ?? '',
                'program'      => $p['program'] ?? $p['program_pengajian'] ?? '—',
                'semester'     => $p['semester'] ?? '—',
                'gpa'          => $gpaByPelajar[$pid] ?? 0,
                'tarikh_tamat' => $tamat,
                'hari_berbaki' => $hariBerbaki,
                'status_label' => $statusLabel,
                'status_badge' => $statusBadge,
                'inisial'      => $this->inisial($p['nama_pelajar'] ?? '?'),
                'warna'        => $warnaKitaran[$i % 3],
            ];
            $i++;
        }

        $hampirTamat = array_values(array_filter(
            $senaraiPelajar,
            fn ($p) => $p['status_badge'] === 'warn'
        ));

        return [
            'ada_data'                  => true,
            'jumlah_pelajar'            => count($senaraiPelajar),
            'jumlah_sumbangan'          => $jumlahSumbanganBulanIni,
            'jumlah_sumbangan_keseluruhan' => $jumlahSumbanganKeseluruhan,
            'jumlah_temujanji'          => $akanDatang->count(),
            'senarai_pelajar'           => $senaraiPelajar,
            'hampir_tamat'              => $hampirTamat,
            'temujanji_terdekat'        => $akanDatang->take(5)->values()->toArray(),
        ];
    }

    /**
     * Cari rekod keluarga_angkat milik pengguna yang sedang log masuk,
     * ikut lajur user_id (lihat nota migration di atas class ini).
     */
    private function cariRekodKeluarga($user): array
    {
        if (empty($user->id)) {
            return [];
        }

        $rows = $this->db->select('keluarga_angkat', [
            'select'  => '*',
            'user_id' => 'eq.' . $user->id,
        ]);

        return $rows ?? [];
    }

    private function inisial(string $nama): string
    {
        $bahagian = preg_split('/\s+/', trim($nama));
        $huruf    = '';
        foreach (array_slice($bahagian, 0, 2) as $b) {
            $huruf .= mb_strtoupper(mb_substr($b, 0, 1));
        }
        return $huruf ?: '?';
    }

    private function kosong(): array
    {
        return [
            'ada_data'                     => false,
            'jumlah_pelajar'               => 0,
            'jumlah_sumbangan'             => 0,
            'jumlah_sumbangan_keseluruhan' => 0,
            'jumlah_temujanji'             => 0,
            'senarai_pelajar'              => [],
            'hampir_tamat'                 => [],
            'temujanji_terdekat'           => [],
        ];
    }
}
