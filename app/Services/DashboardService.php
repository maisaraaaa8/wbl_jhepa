<?php

namespace App\Services;

use Carbon\Carbon;

class DashboardService
{
    protected SupabaseService $db;

    public function __construct(SupabaseService $db)
    {
        $this->db = $db;
    }

    public function getStats(): array
    {
        // 1. Semua pelajar
        $semuaPelajar  = $this->db->select('pelajar', [
            'select' => 'id_pelajar,nama_pelajar,no_matrik,semester,program,fakulti,status_pengajian,tarikh_tamat_tajaan',
            'order'  => 'id_pelajar.desc',
        ]) ?? [];
        $jumlahPelajar = count($semuaPelajar);

        // 2. Keluarga angkat — pakai id_keluarga_angkat (bukan id_keluarga)
        $keluarga = $this->db->select('keluarga_angkat', [
            'select' => 'id_keluarga_angkat,id_pelajar,nama_keluarga_angkat,tarikh_tamat_tajaan',
        ]) ?? [];
        $jumlahKeluarga = count($keluarga);

        // 3. Pelajar belum berpasangan (tiada rekod dalam keluarga_angkat)
        $idBerpasangan    = array_filter(array_unique(array_column($keluarga, 'id_pelajar')));
        $belumBerpasangan = max(0, $jumlahPelajar - count($idBerpasangan));

        // 4. Sumbangan bulan ini — table sumbangan guna tarikh_terima (bukan bulan)
        $bulanIni   = Carbon::now()->format('Y-m');
        $sumbangan  = $this->db->select('sumbangan', [
            'select' => 'jumlah,tarikh_terima,keterangan',
        ]) ?? [];

        $sumBulanIni = collect($sumbangan)->filter(function ($s) use ($bulanIni) {
            $tarikh = $s['tarikh_terima'] ?? '';
            return str_starts_with($tarikh, $bulanIni);
        });

        $jumlahSumbangan = $sumBulanIni->sum('jumlah');
        $purataSumbangan = $jumlahPelajar > 0 ? round($jumlahSumbangan / $jumlahPelajar, 2) : 0;

        // 5. Tajaan hampir tamat (dalam 60 hari) — semak tarikh_tamat_tajaan dari keluarga_angkat
        $today = now()->format('Y-m-d');
        $batas = now()->addDays(60)->format('Y-m-d');

        $keluargaMap = collect($keluarga)->keyBy('id_pelajar')->toArray();
        $pelajarMap  = collect($semuaPelajar)->keyBy('id_pelajar')->toArray();

        $hampirTamat = [];
        foreach ($keluarga as $k) {
            $tamat = $k['tarikh_tamat_tajaan'] ?? null;
            if (!$tamat) {
                // Cuba dari pelajar
                $tamat = $pelajarMap[$k['id_pelajar'] ?? '']['tarikh_tamat_tajaan'] ?? null;
            }
            if (!$tamat) continue;

            if ($tamat >= $today && $tamat <= $batas) {
                $p = $pelajarMap[$k['id_pelajar'] ?? ''] ?? null;
                $hampirTamat[] = [
                    'pelajar_nama'  => $p['nama_pelajar'] ?? '—',
                    'pelajar_matrik'=> $p['no_matrik'] ?? '',
                    'keluarga_nama' => $k['nama_keluarga_angkat'] ?? '—',
                    'tarikh_tamat'  => $tamat,
                    'hari_berbaki'  => (int) now()->diffInDays(Carbon::parse($tamat), false),
                ];
            }
        }

        // 6. Ambil semua prestasi dalam satu query
        $semuaPrestasi = $this->db->select('prestasi', [
            'select' => 'id_pelajar,gpa,cgpa,semester',
        ]) ?? [];

        // Group prestasi by id_pelajar — rekod terkini (semester tertinggi) sahaja
        $prestasiByPelajar = collect($semuaPrestasi)
            ->groupBy('id_pelajar')
            ->map(fn($rows) => $rows->sortByDesc('semester')->first())
            ->toArray();

        // 7. Enrich SEMUA pelajar (bukan setakat 10 terkini) supaya carta & tapisan fakulti tepat
        $semuaWithExtra = array_map(function ($p) use ($keluargaMap, $prestasiByPelajar) {
            $pid = $p['id_pelajar'] ?? null;

            $latestPrestasi  = $pid ? ($prestasiByPelajar[$pid] ?? null) : null;
            $p['latest_gpa'] = $latestPrestasi ? floatval($latestPrestasi['gpa'] ?? 0) : 0;

            $ka = $pid ? ($keluargaMap[$pid] ?? null) : null;
            $p['keluarga_nama'] = $ka['nama_keluarga_angkat'] ?? null;

            $p['fakulti'] = trim($p['fakulti'] ?? '') !== '' ? $p['fakulti'] : 'Tidak Dinyatakan';

            return $p;
        }, $semuaPelajar);

        // Pelajar terkini untuk paparan jadual (15 terbaru — cukup untuk tapisan fakulti bermakna)
        $recentWithExtra = array_slice($semuaWithExtra, 0, 15);

        // 8. Carta: taburan pelajar mengikut fakulti
        $fakultiCounts = collect($semuaWithExtra)
            ->groupBy('fakulti')
            ->map->count()
            ->sortDesc();

        $fakultiChart = [
            'labels' => $fakultiCounts->keys()->values()->all(),
            'data'   => $fakultiCounts->values()->all(),
        ];
        $fakultiList = $fakultiCounts->keys()->sort()->values()->all();

        // 9. Carta: taburan status prestasi (berdasarkan GPA terkini)
        $statusCounts = ['Cemerlang' => 0, 'Memuaskan' => 0, 'Perlu Perhatian' => 0, 'Belum Ada Rekod' => 0];
        foreach ($semuaWithExtra as $p) {
            $g = floatval($p['latest_gpa'] ?? 0);
            if ($g <= 0)        $statusCounts['Belum Ada Rekod']++;
            elseif ($g >= 3.50) $statusCounts['Cemerlang']++;
            elseif ($g >= 3.00) $statusCounts['Memuaskan']++;
            else                 $statusCounts['Perlu Perhatian']++;
        }
        $statusChart = [
            'labels' => array_keys($statusCounts),
            'data'   => array_values($statusCounts),
        ];

        // 10. Carta: trend sumbangan 6 bulan terakhir
        $bulanLabel = [];
        $bulanTotal = [];
        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $key   = $bulan->format('Y-m');
            $bulanLabel[$key] = $bulan->translatedFormat('M Y');
            $bulanTotal[$key] = 0;
        }
        foreach ($sumbangan as $s) {
            $key = substr($s['tarikh_terima'] ?? '', 0, 7);
            if (array_key_exists($key, $bulanTotal)) {
                $bulanTotal[$key] += floatval($s['jumlah'] ?? 0);
            }
        }
        $sumbanganTrend = [
            'labels' => array_values($bulanLabel),
            'data'   => array_values($bulanTotal),
        ];

        // 11. Top 3 pelajar cemerlang (GPA terkini tertinggi) — untuk widget leaderboard
        $topPelajar = collect($semuaWithExtra)
            ->filter(fn($p) => floatval($p['latest_gpa'] ?? 0) > 0)
            ->sortByDesc(fn($p) => floatval($p['latest_gpa'] ?? 0))
            ->take(3)
            ->values()
            ->all();

        return [
            'jumlah_pelajar'    => $jumlahPelajar,
            'jumlah_keluarga'   => $jumlahKeluarga,
            'belum_berpasangan' => $belumBerpasangan,
            'jumlah_sumbangan'  => $jumlahSumbangan,
            'purata_sumbangan'  => $purataSumbangan,
            'hampir_tamat'      => count($hampirTamat),
            'recent_pelajar'    => $recentWithExtra,
            'fakulti_chart'     => $fakultiChart,
            'fakulti_list'      => $fakultiList,
            'status_chart'      => $statusChart,
            'sumbangan_trend'   => $sumbanganTrend,
            'top_pelajar'       => $topPelajar,
        ];
    }
}
