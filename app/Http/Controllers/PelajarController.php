<?php

namespace App\Http\Controllers;

use App\Services\PelajarService;
use App\Services\SupabaseService;
use Illuminate\Http\Request;

class PelajarController extends Controller
{
    protected PelajarService  $pelajarService;
    protected SupabaseService $db;

    public function __construct(PelajarService $pelajarService, SupabaseService $db)
    {
        $this->pelajarService = $pelajarService;
        $this->db             = $db;
    }

    // ── INDEX ──────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $filters = [];
        if ($request->filled('semester'))  $filters['semester']  = $request->semester;
        if ($request->filled('status'))    $filters['status']    = $request->status;
        if ($request->filled('kelulusan')) $filters['kelulusan'] = $request->kelulusan;

        $pelajar = $request->filled('cari')
            ? $this->pelajarService->cari($request->cari)
            : $this->pelajarService->getAll($filters);

        return view('pelajar.index', compact('pelajar'));
    }

    // ── CREATE ─────────────────────────────────────────────────────
    public function create()
    {
        return view('pelajar.create');
    }

    // ── STORE ──────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'nama_pelajar'                 => 'required|string|max:255',
            'no_matrik'                    => 'required|string|max:50',
            'program'                      => 'nullable|string|max:255',  // ← baharu
            'fakulti'                      => 'nullable|string|max:255',  // ← baharu
            'semester'                     => 'nullable|string|max:20',
            'no_ic'                        => 'nullable|string|max:20|regex:/^[0-9\-]+$/', // ← baharu
            'alamat'                       => 'nullable|string|max:500',                   // ← baharu
            'tarikh_mesyuarat_diluluskan'  => 'nullable|date',                              // ← baharu
            'email'                        => 'nullable|email|max:255',
            'password'                     => 'nullable|string|min:8',
        ], [
            'nama_pelajar.required' => 'Nama pelajar wajib diisi.',
            'no_matrik.required'    => 'No. matrik wajib diisi.',
            'no_ic.regex'           => 'No. IC hanya boleh mengandungi nombor dan tanda sengkang (-).',
            'email.email'           => 'Format emel tidak sah.',
            'password.min'          => 'Kata laluan sekurang-kurangnya 8 aksara.',
        ]);

        // Semak duplikat no matrik dalam Supabase
        $existing = $this->db->select('pelajar', [
            'no_matrik' => 'eq.' . $request->no_matrik,
            'select'    => 'id_pelajar',
            'limit'     => 1,
        ]);

        if (!empty($existing)) {
            return back()->withInput()
                ->withErrors(['no_matrik' => 'No. matrik ini sudah wujud dalam sistem.']);
        }

        $pelajar = $this->pelajarService->simpan($request->all());

        if (!$pelajar) {
            return back()->withInput()->with('error', 'Gagal menyimpan data. Sila cuba lagi.');
        }

        return redirect()->route('pelajar.show', $pelajar['id_pelajar'])
            ->with('success', "Pelajar {$pelajar['nama_pelajar']} berjaya ditambah.");
    }

    // ── SHOW ───────────────────────────────────────────────────────
    public function show(string $id)
    {
        $pelajar = $this->pelajarService->getById($id);

        if (!$pelajar) {
            return redirect()->route('pelajar.index')
                ->with('error', 'Rekod pelajar tidak dijumpai.');
        }

        return view('pelajar.show', compact('pelajar'));
    }

    // ── EDIT ───────────────────────────────────────────────────────
    public function edit(string $id)
    {
        $pelajar = $this->pelajarService->getById($id);

        if (!$pelajar) {
            return redirect()->route('pelajar.index')
                ->with('error', 'Rekod pelajar tidak dijumpai.');
        }

        return view('pelajar.edit', compact('pelajar'));
    }

    // ── UPDATE ─────────────────────────────────────────────────────
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_pelajar'                 => 'required|string|max:255',
            'no_matrik'                    => 'required|string|max:50',
            'program'                      => 'nullable|string|max:255',  // ← baharu
            'fakulti'                      => 'nullable|string|max:255',  // ← baharu
            'no_ic'                        => 'nullable|string|max:20|regex:/^[0-9\-]+$/', // ← baharu
            'alamat'                       => 'nullable|string|max:500',                   // ← baharu
            'tarikh_mesyuarat_diluluskan'  => 'nullable|date',                              // ← baharu
        ], [
            'nama_pelajar.required' => 'Nama pelajar wajib diisi.',
            'no_matrik.required'    => 'No. matrik wajib diisi.',
            'no_ic.regex'           => 'No. IC hanya boleh mengandungi nombor dan tanda sengkang (-).',
        ]);

        // Semak duplikat (kecuali rekod sendiri)
        $existing = $this->db->select('pelajar', [
            'no_matrik'  => 'eq.' . $request->no_matrik,
            'id_pelajar' => 'neq.' . $id,
            'select'     => 'id_pelajar',
            'limit'      => 1,
        ]);

        if (!empty($existing)) {
            return back()->withInput()
                ->withErrors(['no_matrik' => 'No. matrik ini sudah digunakan pelajar lain.']);
        }

        $pelajar = $this->pelajarService->kemaskini($id, $request->all());

        if (!$pelajar) {
            return back()->withInput()->with('error', 'Gagal mengemaskini data. Sila cuba lagi.');
        }

        return redirect()->route('pelajar.show', $id)
            ->with('success', 'Maklumat pelajar berjaya dikemaskini.');
    }

    // ── KELULUSAN MESYUARAT (luluskan / batalkan terus dari Profil) ──
    public function kemaskiniKelulusan(Request $request, string $id)
    {
        $pelajar = $this->pelajarService->getById($id);

        if (!$pelajar) {
            return redirect()->route('pelajar.index')
                ->with('error', 'Rekod pelajar tidak dijumpai.');
        }

        // Admin sahaja dibenarkan luluskan/batalkan (readonly hanya boleh lihat)
        if (session('role', session('peranan')) !== 'admin') {
            abort(403, 'Anda tidak dibenarkan melakukan tindakan ini.');
        }

        $aksi = $request->input('aksi');

        if ($aksi === 'batal') {
            $tarikh = null;
        } else {
            $request->validate([
                'tarikh_mesyuarat_diluluskan' => 'required|date',
            ], [
                'tarikh_mesyuarat_diluluskan.required' => 'Sila pilih tarikh mesyuarat.',
            ]);
            $tarikh = $request->tarikh_mesyuarat_diluluskan;
        }

        $result = $this->pelajarService->kemaskini($id, [
            'tarikh_mesyuarat_diluluskan' => $tarikh,
        ]);

        if (!$result) {
            return back()->with('error', 'Gagal mengemaskini status kelulusan.');
        }

        return redirect()->route('pelajar.show', $id)->with(
            'success',
            $aksi === 'batal'
                ? 'Kelulusan mesyuarat pelajar ini telah dibatalkan.'
                : 'Pelajar ini berjaya diluluskan dalam mesyuarat.'
        );
    }

    // ── DESTROY ────────────────────────────────────────────────────
    public function destroy(string $id)
    {
        $pelajar = $this->pelajarService->getById($id);

        if (!$pelajar) {
            return redirect()->route('pelajar.index')
                ->with('error', 'Rekod tidak dijumpai.');
        }

        $this->pelajarService->padam($id);

        return redirect()->route('pelajar.index')
            ->with('success', "Pelajar {$pelajar['nama_pelajar']} berjaya dipadam.");
    }
}
