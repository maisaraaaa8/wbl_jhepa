<?php

namespace App\Services;

use Carbon\Carbon;

class NotifikasiService
{
    protected SupabaseService $db;

    public function __construct(SupabaseService $db)
    {
        $this->db = $db;
    }

    /**
     * Tajaan hampir tamat (dalam 90 hari).
     */
    public function getHampirTamat(): array
    {
        $pelajar  = $this->db->select('pelajar', ['select' => 'id_pelajar,nama_pelajar,tarikh_tamat_tajaan']) ?? [];
        $keluarga = $this->db->select('keluarga_angkat', [
            'select' => 'id_pelajar,nama_keluarga_angkat,tarikh_tamat_tajaan,status_tajaan'
        ]) ?? [];

        $keluargaByPelajar = collect($keluarga)->keyBy('id_pelajar')->toArray();
        $result = [];

        foreach ($pelajar as $p) {
            $k = $keluargaByPelajar[$p['id_pelajar']] ?? null;
            $tarikh = $k['tarikh_tamat_tajaan'] ?? $p['tarikh_tamat_tajaan'] ?? null;
            if (!$tarikh) continue;

            try {
                $tamat = Carbon::parse($tarikh);
                $diff  = now()->diffInDays($tamat, false);
                if ($diff < 0 || $diff > 90) continue;

                $result[] = [
                    'pelajar_nama'  => $p['nama_pelajar'] ?? '—',
                    'keluarga_nama' => $k['nama_keluarga_angkat'] ?? 'Tiada keluarga angkat',
                    'tarikh_tamat'  => $tarikh,
                    'hari_berbaki'  => (int) $diff,
                    'teruk'         => $diff <= 30,
                ];
            } catch (\Throwable) { continue; }
        }

        usort($result, fn($a, $b) => $a['hari_berbaki'] <=> $b['hari_berbaki']);
        return $result;
    }

    /**
     * Sumbangan tertunggak.
     */
    public function getSumbTertunggak(): array
    {
        $rows = $this->db->select('sumbangan', [
            'select' => '*',
            'order'  => 'tarikh_terima.desc',
        ]) ?? [];

        $tertunggak = array_filter($rows, fn($s) =>
            strtolower($s['status'] ?? '') === 'tertunggak'
        );

        if (empty($tertunggak)) return [];

        $pelajar  = collect($this->db->select('pelajar', ['select' => 'id_pelajar,nama_pelajar']) ?? [])->keyBy('id_pelajar');
        $keluarga = collect($this->db->select('keluarga_angkat', ['select' => 'id_pelajar,nama_keluarga_angkat']) ?? [])->keyBy('id_pelajar');

        return array_values(array_map(fn($s) => [
            'pelajar_nama'  => $pelajar[$s['id_pelajar']]['nama_pelajar'] ?? '—',
            'keluarga_nama' => $keluarga[$s['id_pelajar']]['nama_keluarga_angkat'] ?? '—',
            'bulan'         => $s['bulan'] ?? $s['tarikh_terima'] ?? now()->format('Y-m-d'),
            'jumlah'        => $s['jumlah'] ?? 0,
        ], $tertunggak));
    }

    /**
     * Notifikasi sumbangan bulan ini.
     */
    public function getNotifSumbangan(): array
    {
        $bulanIni   = now()->format('Y-m');
        $bulanLabel = now()->format('F Y');

        $sumbangan = $this->db->select('sumbangan', ['select' => 'status,jumlah,tarikh_terima,bulan']) ?? [];

        $bulanRows = array_filter($sumbangan, fn($s) =>
            str_starts_with($s['bulan'] ?? $s['tarikh_terima'] ?? '', $bulanIni)
        );

        $total    = count($bulanRows);
        $diterima = collect($bulanRows)->filter(fn($s) => strtolower($s['status'] ?? '') === 'diterima')->count();

        if ($total === 0) return [];

        return [[
            'tajuk'  => "Sumbangan {$bulanLabel} dikemaskini",
            'mesej'  => "{$diterima} daripada {$total} sumbangan bulanan telah diterima.",
            'jenis'  => $diterima === $total ? 'success' : 'info',
            'masa'   => now()->startOfMonth()->format('d M Y'),
            'ikon'   => 'cash',
        ]];
    }

    /**
     * Notifikasi GPA rendah (< 2.50).
     */
    public function getNotifGpaRendah(): array
    {
        $prestasi = $this->db->select('prestasi', ['select' => 'id_pelajar,gpa,cgpa,semester']) ?? [];
        $rendah   = array_filter($prestasi, fn($p) => (float)($p['gpa'] ?? 99) < 2.50);

        if (empty($rendah)) return [];

        $pelajar = collect($this->db->select('pelajar', ['select' => 'id_pelajar,nama_pelajar']) ?? [])->keyBy('id_pelajar');

        $notif = [];
        foreach ($rendah as $p) {
            $nama = $pelajar[$p['id_pelajar']]['nama_pelajar'] ?? '—';
            $notif[] = [
                'tajuk' => "GPA rendah — {$nama}",
                'mesej' => "GPA " . number_format($p['gpa'], 2) . " pada {$p['semester']}. Perlu pemantauan segera.",
                'jenis' => 'warning',
                'masa'  => now()->format('d M Y'),
                'ikon'  => 'chart-bar',
            ];
        }

        return $notif;
    }

    /**
     * Stats untuk header cards.
     */
    public function getStats(): array
    {
        $hampirTamat = $this->getHampirTamat();
        $tertunggak  = $this->getSumbTertunggak();
        $gpaRendah   = $this->getNotifGpaRendah();

        return [
            'jumlah_notif'  => count($hampirTamat) + count($tertunggak) + count($gpaRendah),
            'hampir_tamat'  => count($hampirTamat),
            'tertunggak'    => count($tertunggak),
            'gpa_rendah'    => count($gpaRendah),
        ];
    }
}
