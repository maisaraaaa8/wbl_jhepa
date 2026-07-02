<?php

namespace App\Http\Controllers;

use App\Services\LaporanService;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    protected LaporanService $laporanService;

    public function __construct(LaporanService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    public function index()
    {
        $stats        = $this->laporanService->getStats();
        $semesterList = $this->laporanService->getSemesterList();

        return view('laporan.index', compact('stats', 'semesterList'));
    }

    public function cetak(Request $request)
    {
        $jenis   = $request->input('jenis', 'pelajar');
        $filters = $request->only(['status', 'semester', 'bulan', 'jenis_pertemuan']);

        $data  = $this->laporanService->getData($jenis, $filters);
        $stats = $this->laporanService->getStats();

        return view('laporan.cetak', compact('data', 'jenis', 'filters', 'stats'));
    }
}
