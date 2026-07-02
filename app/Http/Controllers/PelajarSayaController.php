<?php

namespace App\Http\Controllers;

use App\Helpers\SessionUser;
use App\Services\SupabaseService;
use App\Services\MeetingService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Halaman peribadi untuk peranan 'pelajar' sahaja.
 * Setiap method di sini HANYA papar/urus data pelajar itu sendiri
 * (dicari melalui user_id dalam session), bukan data pelajar lain.
 */
class PelajarSayaController extends Controller
{
    protected SupabaseService $db;
    protected MeetingService  $meetingService;

    public function __construct(SupabaseService $db, MeetingService $meetingService)
    {
        $this->db             = $db;
        $this->meetingService = $meetingService;
    }

    // ── Maklumat Pelajar: papar maklumat diri & akademik asas sendiri ────
    public function maklumat(): View
    {
        $pelajar = $this->pelajarSaya();

        return view('pelajar-saya.maklumat', compact('pelajar'));
    }

    /**
     * Cari rekod 'pelajar' yang berpadanan dengan pengguna yang login sekarang.
     */
    private function pelajarSaya(): ?array
    {
        $userId = SessionUser::id();

        $rows = $this->db->select('pelajar', [
            'select'  => '*',
            'user_id' => "eq.{$userId}",
            'limit'   => 1,
        ]);

        return $rows[0] ?? null;
    }

    // ── Akademik: sejarah penuh GPA/CGPA setiap semester ───────────────
    public function akademik(): View
    {
        $pelajar = $this->pelajarSaya();
        $pid     = $pelajar['id_pelajar'] ?? null;

        $prestasi = $pid ? $this->db->select('prestasi', [
            'select'     => '*',
            'id_pelajar' => "eq.{$pid}",
            'order'      => 'id.asc',
        ]) : [];

        $gpas = array_column($prestasi, 'gpa');
        $cgpa = count($gpas) ? round(array_sum($gpas) / count($gpas), 2) : 0;

        return view('pelajar-saya.akademik', compact('pelajar', 'prestasi', 'cgpa'));
    }

    // ── Keluarga Angkat: butiran penuh keluarga angkat yang ditugaskan ──
    public function keluargaAngkat(): View
    {
        $pelajar = $this->pelajarSaya();
        $pid     = $pelajar['id_pelajar'] ?? null;

        $kaRows = $pid ? $this->db->select('keluarga_angkat', [
            'select'     => '*',
            'id_pelajar' => "eq.{$pid}",
            'limit'      => 1,
        ]) : [];

        $keluarga = $kaRows[0] ?? null;

        return view('pelajar-saya.keluarga-angkat', compact('pelajar', 'keluarga'));
    }

    // ── Sumbangan: sejarah penuh sumbangan yang diterima ────────────────
    public function sumbangan(): View
    {
        $pelajar = $this->pelajarSaya();
        $pid     = $pelajar['id_pelajar'] ?? null;

        $sumbangan = $pid ? $this->db->select('sumbangan', [
            'select'     => '*',
            'id_pelajar' => "eq.{$pid}",
            'order'      => 'tarikh_terima.desc',
        ]) : [];

        $jumlahKeseluruhan = array_sum(array_column($sumbangan, 'jumlah'));

        return view('pelajar-saya.sumbangan', compact('pelajar', 'sumbangan', 'jumlahKeseluruhan'));
    }

    // ══════════════════════════════════════════════════════════════════
    // ── Meeting Record: CRUD penuh, terhad kepada rekod diri sendiri ──
    // ══════════════════════════════════════════════════════════════════

    public function meeting(): View
    {
        $pelajar  = $this->pelajarSaya();
        $pid      = $pelajar['id_pelajar'] ?? null;
        $meetings = $pid ? $this->meetingService->getAll(['id_pelajar' => $pid]) : [];

        $stats = [
            'jumlah_pertemuan' => count($meetings),
            'jumlah_sesi'      => collect($meetings)->sum('jumlah_sesi'),
            'bersemuka'        => collect($meetings)->filter(fn($m) => strtolower($m['jenis_pertemuan'] ?? '') === 'bersemuka')->count(),
            'terkini'          => collect($meetings)->max('tarikh_pertemuan'),
        ];

        return view('pelajar-saya.meeting', compact('pelajar', 'meetings', 'stats'));
    }

    public function meetingStore(Request $request): RedirectResponse
    {
        $pelajar = $this->pelajarSaya();
        $pid     = $pelajar['id_pelajar'] ?? null;

        if (!$pid) {
            return back()->with('error', 'Rekod pelajar tidak dijumpai.');
        }

        $request->validate([
            'tarikh_pertemuan' => 'required|date',
            'jenis_pertemuan'  => 'required',
            'jumlah_sesi'      => 'required|integer|min:1',
        ], [
            'tarikh_pertemuan.required' => 'Tarikh pertemuan wajib diisi.',
            'jenis_pertemuan.required'  => 'Jenis pertemuan wajib dipilih.',
            'jumlah_sesi.required'      => 'Jumlah sesi wajib diisi.',
        ]);

        // id_pelajar SENTIASA dipaksa kepada rekod diri sendiri — tidak boleh ditipu dari input.
        $data               = $request->all();
        $data['id_pelajar'] = $pid;

        $result = $this->meetingService->simpan($data);

        if (!$result) {
            return back()->withInput()->with('error', 'Gagal menyimpan rekod pertemuan.');
        }

        return redirect()->route('pelajar.meeting')
            ->with('success', 'Rekod pertemuan berjaya disimpan.');
    }

    public function meetingUpdate(Request $request, string $id): RedirectResponse
    {
        $pelajar = $this->pelajarSaya();
        $pid     = $pelajar['id_pelajar'] ?? null;

        $sedia = $this->meetingService->getById($id);
        if (!$sedia || (int) ($sedia['id_pelajar'] ?? 0) !== (int) $pid) {
            abort(403, 'Anda tidak dibenarkan mengubah rekod ini.');
        }

        $request->validate([
            'tarikh_pertemuan' => 'required|date',
            'jenis_pertemuan'  => 'required',
            'jumlah_sesi'      => 'required|integer|min:1',
        ]);

        // Buang id_pelajar dari input supaya rekod tak boleh dipindah ke pelajar lain.
        $data = $request->except('id_pelajar');

        $result = $this->meetingService->kemaskini($id, $data);

        if (!$result) {
            return back()->withInput()->with('error', 'Gagal mengemaskini rekod.');
        }

        return redirect()->route('pelajar.meeting')
            ->with('success', 'Rekod pertemuan berjaya dikemaskini.');
    }

    public function meetingDestroy(string $id): RedirectResponse
    {
        $pelajar = $this->pelajarSaya();
        $pid     = $pelajar['id_pelajar'] ?? null;

        $sedia = $this->meetingService->getById($id);
        if (!$sedia || (int) ($sedia['id_pelajar'] ?? 0) !== (int) $pid) {
            abort(403, 'Anda tidak dibenarkan memadam rekod ini.');
        }

        $berjaya = $this->meetingService->padam($id);

        return redirect()->route('pelajar.meeting')
            ->with(
                $berjaya ? 'success' : 'error',
                $berjaya ? 'Rekod pertemuan berjaya dipadam.' : 'Gagal memadam rekod.'
            );
    }
}
