<?php

namespace App\Http\Controllers;

use App\Services\KeluargaAngkatService;
use App\Services\PelajarService;
use Illuminate\Http\Request;

class KeluargaAngkatController extends Controller
{
    protected KeluargaAngkatService $keluargaService;
    protected PelajarService        $pelajarService;

    public function __construct(
        KeluargaAngkatService $keluargaService,
        PelajarService        $pelajarService
    ) {
        $this->keluargaService = $keluargaService;
        $this->pelajarService  = $pelajarService;
    }

    /**
     * Senarai keluarga angkat.
     */
    public function index(Request $request)
    {
        $pelajarList = $this->pelajarService->getAll();

        if ($request->filled('cari')) {
            $keluarga = $this->keluargaService->cari($request->cari);
        } else {
            $filters = [];
            if ($request->filled('status')) $filters['status'] = $request->status;
            $keluarga = $this->keluargaService->getAll($filters);
        }

        $stats = $this->keluargaService->getStats();

        return view('keluarga-angkat.index', compact('keluarga', 'pelajarList', 'stats'));
    }

    /**
     * Paparkan detail satu keluarga angkat (modal View).
     * Boleh return JSON (untuk modal AJAX) atau view penuh.
     */
    public function show(Request $request, string $id)
    {
        $keluarga = $this->keluargaService->getById($id);

        if (!$keluarga) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Rekod tidak dijumpai.'], 404);
            }
            return redirect()->route('keluarga.index')
                ->with('error', 'Rekod keluarga angkat tidak dijumpai.');
        }

        // Kalau AJAX/modal — return JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($keluarga);
        }

        return view('keluarga-angkat.show', compact('keluarga'));
    }

    /**
     * Simpan keluarga angkat baharu.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_keluarga_angkat' => 'required|string|max:255',
            'no_telefon'           => 'nullable|string|max:20',
            'jabatan'              => 'nullable|string|max:255',
            'no_ic'                => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:255',
            'password'             => 'nullable|string|min:8',
        ], [
            'nama_keluarga_angkat.required' => 'Nama keluarga angkat wajib diisi.',
            'email.email'                   => 'Format emel tidak sah.',
            'password.min'                  => 'Kata laluan sekurang-kurangnya 8 aksara.',
        ]);

        $data                  = $request->all();
        $data['hide_identity'] = $request->boolean('hide_identity');

        $result = $this->keluargaService->simpan($data);

        if (!$result) {
            return back()->withInput()
                ->with('error', 'Gagal menyimpan rekod. Sila semak semua maklumat dan cuba lagi.');
        }

        return redirect()->route('keluarga.index')
            ->with('success', "Keluarga angkat \"{$result['nama_keluarga_angkat']}\" berjaya ditambah.");
    }

    /**
     * Kemaskini rekod keluarga angkat.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_keluarga_angkat' => 'required|string|max:255',
            'no_telefon'           => 'nullable|string|max:20',
            'jabatan'              => 'nullable|string|max:255',
            'no_ic'                => 'nullable|string|max:20',
        ], [
            'nama_keluarga_angkat.required' => 'Nama keluarga angkat wajib diisi.',
        ]);

        $data                  = $request->all();
        $data['hide_identity'] = $request->boolean('hide_identity');

        $result = $this->keluargaService->kemaskini($id, $data);

        if (!$result) {
            return back()->withInput()
                ->with('error', 'Gagal mengemaskini rekod. Sila cuba lagi.');
        }

        return redirect()->route('keluarga.index')
            ->with('success', 'Maklumat keluarga angkat berjaya dikemaskini.');
    }

    /**
     * Padam rekod.
     */
    public function destroy(string $id)
    {
        $keluarga = $this->keluargaService->getById($id);
        $nama     = $keluarga['nama_keluarga_angkat'] ?? 'Rekod';

        $berjaya  = $this->keluargaService->padam($id);

        return redirect()->route('keluarga.index')
            ->with(
                $berjaya ? 'success' : 'error',
                $berjaya ? "\"{$nama}\" berjaya dipadam." : 'Gagal memadam rekod.'
            );
    }

    /**
     * Tugaskan pelajar kepada keluarga angkat.
     */
    public function tugaskan(Request $request, string $id)
    {
        $request->validate([
            'pelajar_id' => 'required',
        ], [
            'pelajar_id.required' => 'Sila pilih pelajar untuk ditugaskan.',
        ]);

        $result = $this->keluargaService->tugaskan(
            $id,
            $request->pelajar_id,
            $request->tarikh_tamat
        );

        return redirect()->route('keluarga.index')
            ->with(
                $result ? 'success' : 'error',
                $result ? 'Pelajar berjaya ditugaskan kepada keluarga angkat.'
                        : 'Gagal menugaskan pelajar.'
            );
    }
}
