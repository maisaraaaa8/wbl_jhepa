<?php

namespace App\Http\Controllers;

use App\Helpers\SessionUser;
use App\Services\SupabaseService;
use App\Services\MeetingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Portal layan-diri untuk role 'keluarga_angkat'.
 * Semua data di sini di-SCOPE ketat kepada user_id sendiri sahaja —
 * tiada cara untuk keluarga angkat A nampak data keluarga angkat B.
 */
class KeluargaPortalController extends Controller
{
    protected SupabaseService $db;
    protected MeetingService $meetingService;

    public function __construct(SupabaseService $db, MeetingService $meetingService)
    {
        $this->db             = $db;
        $this->meetingService = $meetingService;
    }

    /**
     * Ambil semua rekod keluarga_angkat kepunyaan user semasa.
     * Satu akaun boleh menaja > 1 pelajar (> 1 baris keluarga_angkat
     * dengan user_id yang sama tapi id_pelajar berbeza).
     */
    private function rekodSendiri(): array
    {
        $userId = SessionUser::id();
        if (!$userId) return [];

        return $this->db->select('keluarga_angkat', [
            'select'  => '*',
            'user_id' => "eq.{$userId}",
            'order'   => 'id_keluarga_angkat.asc',
        ]) ?? [];
    }

    /**
     * Senarai ID pelajar yang sah di bawah jagaan user semasa sahaja.
     * Semua operasi tulis (create/update/delete) WAJIB semak senarai ini
     * dahulu — supaya keluarga angkat A tak boleh sentuh rekod pelajar
     * yang bukan di bawah jagaan dia.
     */
    private function idPelajarSendiri(): array
    {
        return collect($this->rekodSendiri())
            ->pluck('id_pelajar')
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * GET /keluarga-portal/pelajar — senarai pelajar di bawah jagaan.
     */
    public function pelajar(Request $request)
    {
        $kaRows = $this->rekodSendiri();
        $idList = collect($kaRows)->pluck('id_pelajar')->filter()->values();

        $pelajarList = [];

        if ($idList->isNotEmpty()) {
            $inFilter = 'in.(' . $idList->implode(',') . ')';

            $pelajarRows = $this->db->select('pelajar', [
                'select'     => '*',
                'id_pelajar' => $inFilter,
                'order'      => 'nama_pelajar.asc',
            ]) ?? [];

            // GPA terkini setiap pelajar
            $gpaRows = $this->db->select('prestasi', [
                'select'     => 'id_pelajar, gpa, semester',
                'id_pelajar' => $inFilter,
                'order'      => 'id_pelajar.asc,id.desc',
            ]) ?? [];
            $gpaByPelajar = [];
            foreach ($gpaRows as $g) {
                $pid = $g['id_pelajar'];
                if (!isset($gpaByPelajar[$pid])) {
                    $gpaByPelajar[$pid] = floatval($g['gpa'] ?? 0);
                }
            }

            // Index status_tajaan / tarikh_tamat ikut id_pelajar
            $kaByPelajar = collect($kaRows)->keyBy('id_pelajar');

            $cari = trim((string) $request->get('cari', ''));

            foreach ($pelajarRows as $p) {
                $pid = $p['id_pelajar'];
                $ka  = $kaByPelajar->get($pid);

                $p['latest_gpa']    = $gpaByPelajar[$pid] ?? 0;
                $p['status_tajaan'] = $ka['status_tajaan']        ?? 'Aktif';
                $p['tarikh_tamat']  = $ka['tarikh_tamat_tajaan']  ?? null;

                $pelajarList[] = $p;

                if ($cari !== '') {
                    $hay = strtolower($p['nama_pelajar'] . ' ' . ($p['no_matrik'] ?? ''));
                    if (!str_contains($hay, strtolower($cari))) {
                        array_pop($pelajarList);
                    }
                }
            }
        }

        // ── Ringkasan untuk kad statistik atas halaman ──────────────────
        $jumlahPelajar = count($idList);
        $gpaSenarai    = collect($pelajarList)->pluck('latest_gpa')->filter(fn($g) => $g > 0);
        $purataGpa     = $gpaSenarai->isNotEmpty() ? round($gpaSenarai->avg(), 2) : 0;
        $jumlahAktif   = collect($pelajarList)->filter(function ($p) {
            $tamat = !empty($p['tarikh_tamat']) ? Carbon::parse($p['tarikh_tamat']) : null;
            return !$tamat || now()->diffInMonths($tamat, false) > 2;
        })->count();

        return view('keluarga-portal.pelajar', [
            'pelajarList'   => $pelajarList,
            'cari'          => $request->get('cari', ''),
            'jumlahPelajar' => $jumlahPelajar,
            'purataGpa'     => $purataGpa,
            'jumlahAktif'   => $jumlahAktif,
        ]);
    }

    /**
     * GET /keluarga-portal/prestasi — prestasi akademik penuh pelajar di bawah jagaan.
     * Sokong > 1 pelajar seorang penaja — boleh tukar pelajar via ?pelajar=id_pelajar.
     */
    public function prestasi(Request $request)
    {
        $kaRows = $this->rekodSendiri();
        $idList = collect($kaRows)->pluck('id_pelajar')->filter()->values();

        $pelajarList = [];

        if ($idList->isNotEmpty()) {
            $inFilter = 'in.(' . $idList->implode(',') . ')';

            $pelajarRows = $this->db->select('pelajar', [
                'select'     => 'id_pelajar, nama_pelajar, no_matrik, program, semester, status_pengajian',
                'id_pelajar' => $inFilter,
                'order'      => 'nama_pelajar.asc',
            ]) ?? [];

            // Semua rekod prestasi untuk pelajar-pelajar ini, tersusun ikut semester
            $prestasiRows = $this->db->select('prestasi', [
                'select'     => '*',
                'id_pelajar' => $inFilter,
                'order'      => 'id_pelajar.asc,id.asc',
            ]) ?? [];

            $prestasiByPelajar = [];
            foreach ($prestasiRows as $r) {
                $prestasiByPelajar[$r['id_pelajar']][] = $r;
            }

            foreach ($pelajarRows as $p) {
                $pid   = $p['id_pelajar'];
                $rekod = $prestasiByPelajar[$pid] ?? [];

                $gpas = array_map('floatval', array_column($rekod, 'gpa'));
                $cgpa = count($gpas) ? round(array_sum($gpas) / count($gpas), 2) : 0;

                $trend = null; // naik / turun / stabil berbanding semester sebelumnya
                if (count($gpas) >= 2) {
                    $diff  = end($gpas) - $gpas[count($gpas) - 2];
                    $trend = $diff > 0.05 ? 'naik' : ($diff < -0.05 ? 'turun' : 'stabil');
                }

                $pelajarList[] = [
                    'id_pelajar'      => $pid,
                    'nama_pelajar'    => $p['nama_pelajar'] ?? '—',
                    'no_matrik'       => $p['no_matrik'] ?? '—',
                    'program'         => $p['program'] ?? '—',
                    'semester_kini'   => $p['semester'] ?? '—',
                    'status_kini'     => $p['status_pengajian'] ?? 'Aktif',
                    'prestasi'        => $rekod,
                    'cgpa'            => $cgpa,
                    'trend'           => $trend,
                    'status_prestasi' => $cgpa >= 3.50 ? 'Cemerlang'
                                        : ($cgpa >= 3.00 ? 'Memuaskan'
                                        : ($cgpa > 0 ? 'Perlu Perhatian' : '—')),
                ];
            }
        }

        // Pelajar yang sedang dipaparkan (tab aktif) — dari query string, default pelajar pertama
        $pelajarDipilihId = $request->get('pelajar');
        $pelajarAktif     = collect($pelajarList)->firstWhere('id_pelajar', $pelajarDipilihId)
                            ?? ($pelajarList[0] ?? null);

        // Purata CGPA merentas semua pelajar yang ditaja (untuk kad ringkasan)
        $cgpaSenarai            = collect($pelajarList)->pluck('cgpa')->filter(fn($c) => $c > 0);
        $purataCgpaKeseluruhan  = $cgpaSenarai->isNotEmpty() ? round($cgpaSenarai->avg(), 2) : 0;

        return view('keluarga-portal.prestasi', [
            'pelajarList'           => $pelajarList,
            'pelajarAktif'          => $pelajarAktif,
            'purataCgpaKeseluruhan' => $purataCgpaKeseluruhan,
        ]);
    }

    /**
     * GET /keluarga-portal/sumbangan — ringkasan & sejarah sumbangan sendiri.
     */
    public function sumbangan(Request $request)
    {
        $kaRows = $this->rekodSendiri();
        $idList = collect($kaRows)->pluck('id_pelajar')->filter()->values();

        $sumbanganList = [];
        $pelajarNama   = [];

        if ($idList->isNotEmpty()) {
            $inFilter = 'in.(' . $idList->implode(',') . ')';

            $pelajarRows = $this->db->select('pelajar', [
                'select'     => 'id_pelajar, nama_pelajar',
                'id_pelajar' => $inFilter,
            ]) ?? [];
            foreach ($pelajarRows as $p) {
                $pelajarNama[$p['id_pelajar']] = $p['nama_pelajar'];
            }

            $sumbanganList = $this->db->select('sumbangan', [
                'select'     => '*',
                'id_pelajar' => $inFilter,
                'order'      => 'tarikh_terima.desc',
            ]) ?? [];

            foreach ($sumbanganList as &$s) {
                $s['nama_pelajar'] = $pelajarNama[$s['id_pelajar']] ?? '—';
            }
            unset($s);
        }

        // ── Kira ringkasan ──────────────────────────────────────────
        $bulanIni = now()->format('Y-m');
        $tahunIni = now()->format('Y');

        $jumlahBulanIni = 0;
        $pelajarBulanIni = [];
        $jumlahTahunIni = 0;

        foreach ($sumbanganList as $s) {
            $tarikh = !empty($s['tarikh_terima']) ? Carbon::parse($s['tarikh_terima']) : null;
            if (!$tarikh) continue;

            if ($tarikh->format('Y-m') === $bulanIni) {
                $jumlahBulanIni += floatval($s['jumlah'] ?? 0);
                $pelajarBulanIni[$s['id_pelajar']] = true;
            }
            if ($tarikh->format('Y') === $tahunIni) {
                $jumlahTahunIni += floatval($s['jumlah'] ?? 0);
            }
        }

        return view('keluarga-portal.sumbangan', [
            'sumbanganList'   => $sumbanganList,
            'jumlahBulanIni'  => $jumlahBulanIni,
            'bilPelajarBulan' => count($pelajarBulanIni),
            'jumlahTahunIni'  => $jumlahTahunIni,
        ]);
    }

    /**
     * GET /keluarga-portal/meeting — senarai pertemuan/perbincangan
     * yang direkodkan bagi pelajar di bawah jagaan.
     */
    public function meeting(Request $request)
    {
        $kaRows = $this->rekodSendiri();
        $idList = collect($kaRows)->pluck('id_pelajar')->filter()->values();

        $meetingList = [];
        $pelajarNama = [];

        if ($idList->isNotEmpty()) {
            $inFilter = 'in.(' . $idList->implode(',') . ')';

            $pelajarRows = $this->db->select('pelajar', [
                'select'     => 'id_pelajar, nama_pelajar',
                'id_pelajar' => $inFilter,
            ]) ?? [];
            foreach ($pelajarRows as $p) {
                $pelajarNama[$p['id_pelajar']] = $p['nama_pelajar'];
            }

            $meetingList = $this->db->select('meeting_record', [
                'select'     => '*',
                'id_pelajar' => $inFilter,
                'order'      => 'tarikh_pertemuan.desc',
            ]) ?? [];

            foreach ($meetingList as &$m) {
                $m['nama_pelajar']    = $pelajarNama[$m['id_pelajar']] ?? '—';
                $m['jenis_pertemuan'] = $m['jenis_pertemuan'] ?? 'Bersemuka';
                $m['jumlah_sesi']     = $m['jumlah_sesi'] ?? 1;
            }
            unset($m);
        }

        // ── Ringkasan ────────────────────────────────────────────────
        $bulanIni      = now()->format('Y-m');
        $jumlahSesi    = collect($meetingList)->sum('jumlah_sesi');
        $bulanIniRows  = collect($meetingList)->filter(
            fn($m) => !empty($m['tarikh_pertemuan']) && str_starts_with($m['tarikh_pertemuan'], $bulanIni)
        );

        return view('keluarga-portal.meeting', [
            'meetingList'      => $meetingList,
            'jumlahPertemuan'  => count($meetingList),
            'jumlahSesi'       => $jumlahSesi,
            'bulanIniCount'    => $bulanIniRows->count(),
            'pelajarSaya'      => $pelajarRows ?? [],
        ]);
    }

    /**
     * POST /keluarga-portal/meeting — tambah rekod pertemuan baharu.
     * Hanya boleh untuk pelajar di bawah jagaan sendiri.
     */
    public function storeMeeting(Request $request)
    {
        $request->validate([
            'id_pelajar'       => 'required',
            'tarikh_pertemuan' => 'required|date',
            'jenis_pertemuan'  => 'required|string',
            'jumlah_sesi'      => 'required|integer|min:1',
            'catatan'          => 'nullable|string',
        ], [
            'id_pelajar.required'       => 'Sila pilih pelajar.',
            'tarikh_pertemuan.required' => 'Tarikh pertemuan wajib diisi.',
            'jenis_pertemuan.required'  => 'Jenis pertemuan wajib dipilih.',
            'jumlah_sesi.required'      => 'Jumlah sesi wajib diisi.',
        ]);

        // ── Semak ketat: pelajar ni memang di bawah jagaan user semasa? ──
        if (!in_array((int) $request->id_pelajar, $this->idPelajarSendiri(), true)) {
            return back()->withInput()->with('error', 'Pelajar tidak sah / bukan di bawah jagaan anda.');
        }

        $result = $this->meetingService->simpan($request->all());

        if (!$result) {
            return back()->withInput()->with('error', 'Gagal menyimpan rekod pertemuan.');
        }

        return redirect()->route('keluarga-portal.meeting')
            ->with('success', 'Rekod pertemuan berjaya disimpan.');
    }

    /**
     * PUT /keluarga-portal/meeting/{id} — kemaskini rekod pertemuan sendiri.
     */
    public function updateMeeting(Request $request, string $id)
    {
        $request->validate([
            'tarikh_pertemuan' => 'required|date',
            'jenis_pertemuan'  => 'required|string',
            'jumlah_sesi'      => 'required|integer|min:1',
            'catatan'          => 'nullable|string',
        ]);

        // ── Semak ketat: rekod ni memang milik pelajar di bawah jagaan user semasa? ──
        $rekod = $this->meetingService->getById($id);
        if (!$rekod || !in_array((int) ($rekod['id_pelajar'] ?? 0), $this->idPelajarSendiri(), true)) {
            abort(403, 'Anda tidak mempunyai akses untuk mengemaskini rekod ini.');
        }

        $result = $this->meetingService->kemaskini($id, $request->except('id_pelajar'));

        if (!$result) {
            return back()->withInput()->with('error', 'Gagal mengemaskini rekod pertemuan.');
        }

        return redirect()->route('keluarga-portal.meeting')
            ->with('success', 'Rekod pertemuan berjaya dikemaskini.');
    }

    /**
     * DELETE /keluarga-portal/meeting/{id} — padam rekod pertemuan sendiri.
     */
    public function destroyMeeting(string $id)
    {
        // ── Semak ketat: rekod ni memang milik pelajar di bawah jagaan user semasa? ──
        $rekod = $this->meetingService->getById($id);
        if (!$rekod || !in_array((int) ($rekod['id_pelajar'] ?? 0), $this->idPelajarSendiri(), true)) {
            abort(403, 'Anda tidak mempunyai akses untuk memadam rekod ini.');
        }

        $berjaya = $this->meetingService->padam($id);

        return redirect()->route('keluarga-portal.meeting')
            ->with(
                $berjaya ? 'success' : 'error',
                $berjaya ? 'Rekod pertemuan berjaya dipadam.' : 'Gagal memadam rekod pertemuan.'
            );
    }
}
