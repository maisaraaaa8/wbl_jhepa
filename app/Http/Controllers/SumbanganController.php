<?php

namespace App\Http\Controllers;

use App\Services\SumbanganService;
use Illuminate\Http\Request;

class SumbanganController extends Controller
{
    protected SumbanganService $sumbanganService;

    public function __construct(SumbanganService $sumbanganService)
    {
        $this->sumbanganService = $sumbanganService;
    }

    public function index(Request $request)
    {
        $filters = [];
        if ($request->filled('status'))     $filters['status']     = $request->status;
        if ($request->filled('bulan'))      $filters['bulan']      = $request->bulan;
        if ($request->filled('id_pelajar')) $filters['id_pelajar'] = $request->id_pelajar;

        $sumbangan   = $this->sumbanganService->getAll($filters);
        $stats       = $this->sumbanganService->getStats();
        $keluargaList= $this->sumbanganService->getKeluargaList();
        $trendData   = $this->sumbanganService->getTrendBulanan();

        return view('sumbangan.index', compact('sumbangan', 'stats', 'keluargaList', 'trendData'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_keluarga_angkat' => 'required',
            'jumlah'             => 'required|numeric|min:0.01',
            'bulan'              => 'required',
        ], [
            'id_keluarga_angkat.required' => 'Sila pilih keluarga angkat.',
            'jumlah.required'             => 'Jumlah sumbangan wajib diisi.',
            'bulan.required'              => 'Bulan wajib dipilih.',
        ]);

        $result = $this->sumbanganService->simpan($request->all());

        if (!$result) {
            return back()->withInput()->with('error', 'Gagal menyimpan rekod sumbangan.');
        }

        return redirect()->route('sumbangan.index')
            ->with('success', 'Rekod sumbangan berjaya disimpan.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'jumlah' => 'required|numeric|min:0.01',
        ]);

        $result = $this->sumbanganService->kemaskini($id, $request->all());

        if (!$result) {
            return back()->withInput()->with('error', 'Gagal mengemaskini rekod.');
        }

        return redirect()->route('sumbangan.index')
            ->with('success', 'Rekod sumbangan berjaya dikemaskini.');
    }

    public function sejarah(string $id_pelajar)
    {
        $data = $this->sumbanganService->getSejarahPelajar($id_pelajar);

        return response()->json($data);
    }

    public function destroy(string $id)
    {
        $berjaya = $this->sumbanganService->padam($id);

        return redirect()->route('sumbangan.index')
            ->with($berjaya ? 'success' : 'error',
                   $berjaya ? 'Rekod sumbangan berjaya dipadam.' : 'Gagal memadam rekod.');
    }
}
