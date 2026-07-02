<?php

namespace App\Services;

use Carbon\Carbon;

class LaporanService
{
    protected SupabaseService $db;

    public function __construct(SupabaseService $db)
    {
        $this->db = $db;
    }

    public function getStats(): array
    {
        $pelajar   = $this->db->select('pelajar',  ['select' => 'id_pelajar,status_pengajian,semester,tarikh_tamat_tajaan']) ?? [];
        $keluarga  = $this->db->select('keluarga_angkat', ['select' => 'id_keluarga_angkat']) ?? [];
        $sumbangan = $this->db->select('sumbangan', ['select' => 'jumlah,tarikh_terima']) ?? [];
        $meeting   = $this->db->select('meeting_record', ['select' => 'id']) ?? [];

        $aktif = collect($pelajar)->filter(fn($p) => strtolower($p['status_pengajian'] ?? '') === 'aktif');

        $semesterCounts  = collect($pelajar)->groupBy('semester')->map->count()->sortDesc();
        $semesterTeratas = $semesterCounts->keys()->first() ?? '—';

        $hampirTamat = collect($pelajar)->filter(function ($p) {
            if (empty($p['tarikh_tamat_tajaan'])) return false;
            $diff = now()->diffInDays(Carbon::parse($p['tarikh_tamat_tajaan']), false);
            return $diff >= 0 && $diff <= 60;
        })->count();

        return [
            'jumlah_pelajar'   => count($pelajar),
            'pelajar_aktif'    => $aktif->count(),
            'jumlah_keluarga'  => count($keluarga),
            'jumlah_sumbangan' => collect($sumbangan)->sum('jumlah'),
            'jumlah_meeting'   => count($meeting),
            'semester_teratas' => $semesterTeratas,
            'hampir_tamat'     => $hampirTamat,
        ];
    }

    public function getData(string $jenis, array $filters = []): array
    {
        return match($jenis) {
            'sumbangan' => $this->dataSumbangan($filters),
            'keluarga'  => $this->dataKeluarga($filters),
            'meeting'   => $this->dataMeeting($filters),
            'prestasi'  => $this->dataPrestasi($filters),
            default     => $this->dataPelajar($filters),
        };
    }

    private function dataPelajar(array $f): array
    {
        $p = ['select' => '*', 'order' => 'nama_pelajar.asc'];
        if (!empty($f['status']))   $p['status_pengajian'] = 'eq.' . $f['status'];
        if (!empty($f['semester'])) $p['semester']         = 'eq.' . $f['semester'];
        return $this->db->select('pelajar', $p) ?? [];
    }

    private function dataSumbangan(array $f): array
    {
        $p = ['select' => '*', 'order' => 'tarikh_terima.desc'];
        if (!empty($f['bulan']))  $p['bulan']  = 'eq.' . $f['bulan'];
        if (!empty($f['status'])) $p['status'] = 'eq.' . $f['status'];

        $rows    = $this->db->select('sumbangan', $p) ?? [];
        $pelajar = collect($this->db->select('pelajar', ['select' => 'id_pelajar,nama_pelajar']) ?? [])->keyBy('id_pelajar');

        return array_map(fn($s) => array_merge($s, [
            'nama_pelajar' => $pelajar[$s['id_pelajar']]['nama_pelajar'] ?? '—',
        ]), $rows);
    }

    private function dataKeluarga(array $f): array
    {
        $p = ['select' => '*', 'order' => 'nama_keluarga_angkat.asc'];
        if (!empty($f['status'])) $p['status_tajaan'] = 'eq.' . $f['status'];

        $rows    = $this->db->select('keluarga_angkat', $p) ?? [];
        $pelajar = collect($this->db->select('pelajar', ['select' => 'id_pelajar,nama_pelajar']) ?? [])->keyBy('id_pelajar');

        return array_map(fn($k) => array_merge($k, [
            'nama_pelajar' => $pelajar[$k['id_pelajar'] ?? '']['nama_pelajar'] ?? '—',
        ]), $rows);
    }

    private function dataMeeting(array $f): array
    {
        $p = ['select' => '*', 'order' => 'tarikh_pertemuan.desc'];
        if (!empty($f['jenis'])) $p['jenis_pertemuan'] = 'eq.' . $f['jenis'];

        $rows    = $this->db->select('meeting_record', $p) ?? [];
        $pelajar = collect($this->db->select('pelajar', ['select' => 'id_pelajar,nama_pelajar']) ?? [])->keyBy('id_pelajar');
        $keluarga = collect($this->db->select('keluarga_angkat', ['select' => 'id_pelajar,nama_keluarga_angkat']) ?? [])->keyBy('id_pelajar');

        return array_map(fn($m) => array_merge($m, [
            'nama_pelajar' => $pelajar[$m['id_pelajar']]['nama_pelajar'] ?? '—',
            'nama_keluarga' => $keluarga[$m['id_pelajar']]['nama_keluarga_angkat'] ?? '—',
        ]), $rows);
    }

    private function dataPrestasi(array $f): array
    {
        $p = ['select' => '*', 'order' => 'semester.desc'];
        if (!empty($f['semester'])) $p['semester'] = 'eq.' . $f['semester'];

        $rows    = $this->db->select('prestasi', $p) ?? [];
        $pelajar = collect($this->db->select('pelajar', ['select' => 'id_pelajar,nama_pelajar,no_matrik']) ?? [])->keyBy('id_pelajar');

        return array_map(fn($r) => array_merge($r, [
            'nama_pelajar' => $pelajar[$r['id_pelajar']]['nama_pelajar'] ?? '—',
            'no_matrik'    => $pelajar[$r['id_pelajar']]['no_matrik'] ?? '—',
        ]), $rows);
    }

    public function getSemesterList(): array
    {
        $rows = $this->db->select('pelajar', ['select' => 'semester']) ?? [];
        return collect($rows)->pluck('semester')->filter()->unique()->sort()->values()->toArray();
    }
}
