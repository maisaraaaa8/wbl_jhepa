<?php

namespace App\Services;

use Carbon\Carbon;

class MeetingService
{
    protected SupabaseService $db;

    public function __construct(SupabaseService $db)
    {
        $this->db = $db;
    }

    /**
     * Ambil semua rekod meeting, join nama pelajar & keluarga.
     */
    public function getAll(array $filters = []): array
    {
        $params = [
            'select' => '*',
            'order'  => 'tarikh_pertemuan.desc',
        ];

        if (!empty($filters['id_pelajar'])) {
            $params['id_pelajar'] = 'eq.' . $filters['id_pelajar'];
        }

        if (!empty($filters['jenis'])) {
            $params['jenis_pertemuan'] = 'eq.' . $filters['jenis'];
        }

        $meetings = $this->db->select('meeting_record', $params);
        if (empty($meetings)) return [];

        // Lookup pelajar & keluarga
        $pelajarList  = $this->db->select('pelajar', ['select' => 'id_pelajar,nama_pelajar,no_matrik']) ?? [];
        $keluargaList = $this->db->select('keluarga_angkat', ['select' => 'id_keluarga_angkat,id_pelajar,nama_keluarga_angkat']) ?? [];

        $pelajarMap = collect($pelajarList)->keyBy('id_pelajar')->toArray();
        $keluargaByPelajar = [];
        foreach ($keluargaList as $k) {
            if ($k['id_pelajar']) {
                $keluargaByPelajar[$k['id_pelajar']] = $k['nama_keluarga_angkat'];
            }
        }

        return array_map(function ($m) use ($pelajarMap, $keluargaByPelajar) {
            $p = $pelajarMap[$m['id_pelajar']] ?? null;
            $m['pelajar_nama']   = $p['nama_pelajar'] ?? '—';
            $m['pelajar_matrik'] = $p['no_matrik'] ?? null;
            $m['keluarga_nama']  = $keluargaByPelajar[$m['id_pelajar']] ?? '—';
            $m['jenis_pertemuan'] = $m['jenis_pertemuan'] ?? 'Bersemuka';
            $m['jumlah_sesi']    = $m['jumlah_sesi'] ?? 1;
            return $m;
        }, $meetings);
    }

    /**
     * Ambil satu rekod.
     */
    public function getById(string $id): ?array
    {
        $rows = $this->db->select('meeting_record', [
            'select' => '*',
            'id'     => "eq.{$id}",
            'limit'  => 1,
        ]);
        return $rows[0] ?? null;
    }

    /**
     * Simpan rekod meeting baharu.
     */
    public function simpan(array $data): ?array
    {
        $payload = [
            'id_pelajar'       => (int) ($data['id_pelajar'] ?? 0),
            'tarikh_pertemuan' => $data['tarikh_pertemuan'] ?? now()->format('Y-m-d'),
            'jenis_pertemuan'  => $data['jenis_pertemuan'] ?? 'Bersemuka',
            'jumlah_sesi'      => (int) ($data['jumlah_sesi'] ?? 1),
            'catatan'          => $data['catatan'] ?? null,
        ];

        return $this->db->insert('meeting_record', $payload);
    }

    /**
     * Kemaskini rekod meeting.
     */
    public function kemaskini(string $id, array $data): ?array
    {
        $payload = [];

        if (isset($data['tarikh_pertemuan'])) $payload['tarikh_pertemuan'] = $data['tarikh_pertemuan'];
        if (isset($data['jenis_pertemuan']))  $payload['jenis_pertemuan']  = $data['jenis_pertemuan'];
        if (isset($data['jumlah_sesi']))      $payload['jumlah_sesi']      = (int) $data['jumlah_sesi'];
        if (isset($data['catatan']))          $payload['catatan']           = $data['catatan'];
        if (isset($data['id_pelajar']))       $payload['id_pelajar']        = (int) $data['id_pelajar'];

        return $this->db->update('meeting_record', $id, $payload);
    }

    /**
     * Padam rekod.
     */
    public function padam(string $id): bool
    {
        return $this->db->delete('meeting_record', $id);
    }

    /**
     * Stats ringkas untuk header cards.
     */
    public function getStats(): array
    {
        $all = $this->db->select('meeting_record', ['select' => 'jenis_pertemuan,jumlah_sesi,tarikh_pertemuan']) ?? [];

        $bulanIni = now()->format('Y-m');
        $bulanIniRows = array_filter($all, fn($r) =>
            isset($r['tarikh_pertemuan']) &&
            str_starts_with($r['tarikh_pertemuan'], $bulanIni)
        );

        return [
            'jumlah_pertemuan'  => count($all),
            'bulan_ini'         => count($bulanIniRows),
            'jumlah_sesi'       => collect($all)->sum('jumlah_sesi'),
            'bersemuka'         => collect($all)->filter(fn($r) => strtolower($r['jenis_pertemuan'] ?? '') === 'bersemuka')->count(),
        ];
    }

    /**
     * Senarai pelajar untuk dropdown.
     */
    public function getPelajarList(): array
    {
        return $this->db->select('pelajar', [
            'select' => 'id_pelajar,nama_pelajar,no_matrik',
            'order'  => 'nama_pelajar.asc',
        ]) ?? [];
    }
}
