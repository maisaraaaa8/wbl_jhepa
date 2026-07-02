<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SumbanganService
{
    protected SupabaseService $db;

    public function __construct(SupabaseService $db)
    {
        $this->db = $db;
    }

    // ─── Ambil semua rekod sumbangan ──────────────────────────────
    public function getAll(array $filters = []): array
    {
        $params = [
            'select' => '*',
            'order'  => 'tarikh_terima.desc',
        ];

        // Filter status
        if (!empty($filters['status'])) {
            $params['status'] = 'eq.' . $filters['status'];
        }

        // Filter bulan — guna tarikh_terima jika kolum bulan belum ada nilai
        if (!empty($filters['bulan'])) {
            // Cuba filter ikut kolum bulan dulu
            $params['bulan'] = 'eq.' . $filters['bulan'];
        }

        if (!empty($filters['id_pelajar'])) {
            $params['id_pelajar'] = 'eq.' . $filters['id_pelajar'];
        }

        $rows = $this->db->select('sumbangan', $params);

        if (empty($rows)) {
            // Fallback: kalau filter bulan tak jumpa (mungkin kolum kosong),
            // ambil semua dan tapis dalam PHP
            if (!empty($filters['bulan'])) {
                unset($params['bulan']);
                $rows = $this->db->select('sumbangan', $params);
                $rows = array_filter($rows, function ($r) use ($filters) {
                    $bulanRow = $r['bulan'] ?? null;
                    if (!$bulanRow && !empty($r['tarikh_terima'])) {
                        $bulanRow = Carbon::parse($r['tarikh_terima'])->format('Y-m');
                    }
                    return $bulanRow === $filters['bulan'];
                });
                $rows = array_values($rows);
            }
        }

        if (empty($rows)) return [];

        // Lookup pelajar & keluarga dalam PHP (lebih reliable dari nested JOIN)
        $pelajarList  = $this->db->select('pelajar', [
            'select' => 'id_pelajar,nama_pelajar,no_matrik',
        ]) ?? [];
        $keluargaList = $this->db->select('keluarga_angkat', [
            'select' => 'id_keluarga_angkat,id_pelajar,nama_keluarga_angkat',
        ]) ?? [];

        $pelajarMap        = collect($pelajarList)->keyBy('id_pelajar')->toArray();
        $keluargaById      = collect($keluargaList)->keyBy('id_keluarga_angkat')->toArray();
        $keluargaByPelajar = [];
        foreach ($keluargaList as $k) {
            if (!empty($k['id_pelajar'])) {
                $keluargaByPelajar[$k['id_pelajar']] = $k;
            }
        }

        return array_map(function ($s) use ($pelajarMap, $keluargaById, $keluargaByPelajar) {
            // Lookup pelajar
            $p = $pelajarMap[$s['id_pelajar'] ?? null] ?? null;
            $s['pelajar_nama']   = $p['nama_pelajar'] ?? '—';
            $s['pelajar_matrik'] = $p['no_matrik'] ?? '';

            // Lookup keluarga — ikut id_keluarga_angkat dulu, kemudian ikut id_pelajar
            if (!empty($s['id_keluarga_angkat']) && isset($keluargaById[$s['id_keluarga_angkat']])) {
                $s['keluarga_nama'] = $keluargaById[$s['id_keluarga_angkat']]['nama_keluarga_angkat'] ?? '—';
            } elseif (!empty($s['id_pelajar']) && isset($keluargaByPelajar[$s['id_pelajar']])) {
                $s['keluarga_nama'] = $keluargaByPelajar[$s['id_pelajar']]['nama_keluarga_angkat'] ?? '—';
            } else {
                $s['keluarga_nama'] = '—';
            }

            // Normalise bulan dari tarikh_terima jika kolum bulan kosong
            if (empty($s['bulan']) && !empty($s['tarikh_terima'])) {
                $s['bulan'] = Carbon::parse($s['tarikh_terima'])->format('Y-m');
            }

            // Default status
            if (empty($s['status'])) {
                $s['status'] = 'Diterima';
            }

            return $s;
        }, $rows);
    }

    // ─── Ambil satu rekod ─────────────────────────────────────────
    public function getById(string $id): ?array
    {
        $rows = $this->db->select('sumbangan', [
            'select' => '*',
            'id'     => "eq.{$id}",
            'limit'  => 1,
        ]);
        return $rows[0] ?? null;
    }

    // ─── Simpan rekod baru ────────────────────────────────────────
    public function simpan(array $data): ?array
    {
        $idKeluarga = $data['id_keluarga_angkat'] ?? null;
        $idPelajar  = null;

        // Dapatkan id_pelajar dari keluarga_angkat
        if ($idKeluarga) {
            $rows = $this->db->select('keluarga_angkat', [
                'select'             => 'id_pelajar',
                'id_keluarga_angkat' => "eq.{$idKeluarga}",
                'limit'              => 1,
            ]);
            $idPelajar = $rows[0]['id_pelajar'] ?? null;
        }

        $bulan = $data['bulan'] ?? null;

        // Derive bulan dari tarikh_terima jika kosong
        if (!$bulan && !empty($data['tarikh_terima'])) {
            $bulan = Carbon::parse($data['tarikh_terima'])->format('Y-m');
        }
        if (!$bulan) {
            $bulan = now()->format('Y-m');
        }

        $payload = [
            'id_pelajar'    => $idPelajar,
            'jumlah'        => (float) ($data['jumlah'] ?? 0),
            'tarikh_terima' => !empty($data['tarikh_terima']) ? $data['tarikh_terima'] : now()->format('Y-m-d'),
            'keterangan'    => $data['keterangan'] ?? null,
            'status'        => $data['status'] ?? 'Diterima',
            'bulan'         => $bulan,
        ];

        // Tambah id_keluarga_angkat kalau ada
        if ($idKeluarga) {
            $payload['id_keluarga_angkat'] = (int) $idKeluarga;
        }

        return $this->db->insert('sumbangan', $payload);
    }

    // ─── Kemaskini rekod ──────────────────────────────────────────
    public function kemaskini(string $id, array $data): ?array
    {
        $payload = [];

        if (isset($data['jumlah']))        $payload['jumlah']        = (float) $data['jumlah'];
        if (isset($data['status']))        $payload['status']        = $data['status'];
        if (isset($data['keterangan']))    $payload['keterangan']    = $data['keterangan'];

        if (!empty($data['tarikh_terima'])) {
            $payload['tarikh_terima'] = $data['tarikh_terima'];
            // Auto-update bulan dari tarikh baru
            $payload['bulan'] = Carbon::parse($data['tarikh_terima'])->format('Y-m');
        }

        if (!empty($data['bulan'])) {
            $payload['bulan'] = $data['bulan'];
        }

        if (!empty($data['id_keluarga_angkat'])) {
            $payload['id_keluarga_angkat'] = (int) $data['id_keluarga_angkat'];

            // Update id_pelajar sekali
            $rows = $this->db->select('keluarga_angkat', [
                'select'             => 'id_pelajar',
                'id_keluarga_angkat' => "eq.{$data['id_keluarga_angkat']}",
                'limit'              => 1,
            ]);
            if (!empty($rows[0]['id_pelajar'])) {
                $payload['id_pelajar'] = $rows[0]['id_pelajar'];
            }
        }

        return $this->db->update('sumbangan', $id, $payload);
    }

    // ─── Padam rekod ──────────────────────────────────────────────
    public function padam(string $id): bool
    {
        return $this->db->delete('sumbangan', $id);
    }

    // ─── Senarai keluarga angkat untuk dropdown ───────────────────
    public function getKeluargaList(): array
    {
        return $this->db->select('keluarga_angkat', [
            'select' => 'id_keluarga_angkat,nama_keluarga_angkat,id_pelajar',
            'order'  => 'nama_keluarga_angkat.asc',
        ]) ?? [];
    }

    // ─── Stats ringkasan ──────────────────────────────────────────
    public function getStats(): array
    {
        // Ambil SEMUA rekod dengan kolum yang diperlukan
        $semuaRows = $this->db->select('sumbangan', [
            'select' => 'jumlah,status,bulan,tarikh_terima',
        ]) ?? [];

        // Normalise status untuk setiap rekod
        $semuaRows = array_map(function ($r) {
            if (empty($r['status'])) {
                $r['status'] = 'Diterima';
            }
            return $r;
        }, $semuaRows);

        // Nota: kad-kad statistik memaparkan jumlah KESELURUHAN (semua masa),
        // bukan ditapis ikut bulan semasa — supaya rekod bulan-bulan lepas
        // (cth. tertunggak dari bulan sebelumnya) tetap kelihatan.
        $jumlahKeseluruhan  = collect($semuaRows)->sum('jumlah');
        $bilanganDiterima   = collect($semuaRows)->filter(fn($r) => strtolower($r['status'] ?? '') === 'diterima')->count();
        $bilanganTertunggak = collect($semuaRows)->filter(fn($r) => strtolower($r['status'] ?? '') === 'tertunggak')->count();

        return [
            'jumlah_bulan_ini'    => $jumlahKeseluruhan,
            'bilangan_diterima'   => $bilanganDiterima,
            'bilangan_tertunggak' => $bilanganTertunggak,
            'jumlah_keseluruhan'  => $jumlahKeseluruhan,
        ];
    }

    // ─── Trend bulanan 12 bulan ───────────────────────────────────
    public function getTrendBulanan(): array
    {
        // Ambil semua rekod
        $rows = $this->db->select('sumbangan', [
            'select' => 'bulan,tarikh_terima,jumlah,status',
            'order'  => 'tarikh_terima.asc',
        ]) ?? [];

        // Bina skeleton 12 bulan
        $trend = [];
        for ($i = 11; $i >= 0; $i--) {
            $bulan = now()->subMonths($i)->format('Y-m');
            $trend[$bulan] = [
                'bulan'      => $bulan,
                'label'      => now()->subMonths($i)->format('M'),
                'diterima'   => 0,
                'tertunggak' => 0,
                'jumlah'     => 0,
            ];
        }

        foreach ($rows as $r) {
            // Normalise bulan
            $bulan = $r['bulan'] ?? null;
            if (!$bulan && !empty($r['tarikh_terima'])) {
                $bulan = Carbon::parse($r['tarikh_terima'])->format('Y-m');
            }

            if ($bulan && isset($trend[$bulan])) {
                $jumlah = (float) ($r['jumlah'] ?? 0);
                $status = strtolower($r['status'] ?? 'diterima');
                $trend[$bulan]['jumlah'] += $jumlah;
                if ($status === 'diterima') {
                    $trend[$bulan]['diterima'] += $jumlah;
                } elseif ($status === 'tertunggak') {
                    $trend[$bulan]['tertunggak'] += $jumlah;
                }
            }
        }

        return array_values($trend);
    }

    // ─── Sejarah sumbangan untuk satu pelajar/keluarga ─────────────
    public function getSejarahPelajar(string $idPelajar): array
    {
        // Guna getAll() supaya logik lookup pelajar/keluarga & normalise
        // bulan/status konsisten dengan senarai utama.
        $rekod = $this->getAll(['id_pelajar' => $idPelajar]);

        // Susun terbaru dahulu ikut tarikh_terima
        usort($rekod, function ($a, $b) {
            return strcmp($b['tarikh_terima'] ?? '', $a['tarikh_terima'] ?? '');
        });

        $jumlahKeseluruhan  = collect($rekod)->sum('jumlah');
        $jumlahDiterima     = collect($rekod)->filter(fn($r) => strtolower($r['status'] ?? '') === 'diterima')->sum('jumlah');
        $jumlahTertunggak   = collect($rekod)->filter(fn($r) => strtolower($r['status'] ?? '') === 'tertunggak')->sum('jumlah');

        return [
            'rekod' => $rekod,
            'ringkasan' => [
                'pelajar_nama'       => $rekod[0]['pelajar_nama']  ?? '—',
                'pelajar_matrik'     => $rekod[0]['pelajar_matrik'] ?? '',
                'keluarga_nama'      => $rekod[0]['keluarga_nama'] ?? '—',
                'jumlah_keseluruhan' => $jumlahKeseluruhan,
                'jumlah_diterima'    => $jumlahDiterima,
                'jumlah_tertunggak'  => $jumlahTertunggak,
                'bilangan_rekod'     => count($rekod),
            ],
        ];
    }

    // ─── Cari ─────────────────────────────────────────────────────
    public function cari(string $query): array
    {
        $semua = $this->getAll();
        $q     = strtolower($query);

        return array_values(array_filter($semua, function ($s) use ($q) {
            return str_contains(strtolower($s['keluarga_nama'] ?? ''), $q)
                || str_contains(strtolower($s['pelajar_nama'] ?? ''), $q)
                || str_contains(strtolower($s['pelajar_matrik'] ?? ''), $q);
        }));
    }
}
