<?php

namespace App\Http\Controllers;

use App\Services\PrestasiService;
use App\Services\PelajarService;
use Illuminate\Http\Request;

class PrestasiController extends Controller
{
    protected PrestasiService $prestasiService;
    protected PelajarService  $pelajarService;

    public function __construct(PrestasiService $prestasiService, PelajarService $pelajarService)
    {
        $this->prestasiService = $prestasiService;
        $this->pelajarService  = $pelajarService;
    }

    public function index(Request $request)
    {
        $filters = [];
        if ($request->filled('semester')) $filters['semester'] = $request->semester;

        $ringkasan = $this->prestasiService->getRingkasan($filters);

        // Filter nama / matrik
        if ($request->filled('cari')) {
            $cari      = strtolower($request->cari);
            $ringkasan = array_values(array_filter($ringkasan, function ($p) use ($cari) {
                return str_contains(strtolower($p['nama_pelajar']), $cari)
                    || str_contains(strtolower($p['no_matrik']),    $cari);
            }));
        }

        $statistik       = $this->prestasiService->statistik();
        $senarai_pelajar = $this->pelajarService->getAll();

        return view('prestasi.index', compact('ringkasan', 'statistik', 'senarai_pelajar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pelajar' => 'required',
            'semester'   => 'required',
            'gpa'        => 'required|numeric|min:0|max:4',
        ], [
            'id_pelajar.required' => 'Sila pilih pelajar.',
            'semester.required'   => 'Sila pilih semester.',
            'gpa.required'        => 'GPA diperlukan.',
            'gpa.numeric'         => 'GPA mesti nombor.',
            'gpa.min'             => 'GPA minimum 0.00.',
            'gpa.max'             => 'GPA maksimum 4.00.',
        ]);

        // Semak duplikasi
        $sedia = $this->prestasiService->cariRekod($request->id_pelajar, $request->semester);
        if ($sedia) {
            return back()->withInput()
                ->with('error', "Rekod {$request->semester} untuk pelajar ini sudah wujud. Sila kemaskini rekod sedia ada.");
        }

        $rekod = $this->prestasiService->simpan($request->all());

        if (!$rekod) {
            return back()->withInput()->with('error', 'Gagal menyimpan rekod prestasi.');
        }

        return back()->with('success', 'Rekod prestasi berjaya disimpan.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'gpa' => 'required|numeric|min:0|max:4',
        ]);

        $rekod = $this->prestasiService->kemaskini($id, $request->all());

        if (!$rekod) {
            return back()->with('error', 'Gagal mengemaskini rekod.');
        }

        return back()->with('success', 'Rekod prestasi berjaya dikemaskini.');
    }

    public function destroy(string $id)
    {
        $this->prestasiService->padam($id);
        return back()->with('success', 'Rekod prestasi berjaya dipadam.');
    }

    // Untuk modal detail — fetch via AJAX
    public function byPelajar(string $pelajarId)
    {
        $rekod = $this->prestasiService->getByPelajar($pelajarId);
        return response()->json($rekod);
    }
}
