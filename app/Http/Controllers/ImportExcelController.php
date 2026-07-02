<?php

namespace App\Http\Controllers;

use App\Services\ImportExcelService;
use Illuminate\Http\Request;

class ImportExcelController extends Controller
{
    protected ImportExcelService $importService;

    public function __construct(ImportExcelService $importService)
    {
        $this->importService = $importService;
    }

    public function index()
    {
        $history = $this->importService->getHistory();
        return view('import.index', compact('history'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'fail_excel' => 'required|file|max:5120',
        ], [
            'fail_excel.required' => 'Sila pilih fail.',
            'fail_excel.max'      => 'Saiz fail tidak boleh melebihi 5MB.',
        ]);

        $fail = $request->file('fail_excel');
        $ext  = strtolower($fail->getClientOriginalExtension());

        if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
            return back()->with('error', 'Format fail tidak disokong. Sila gunakan .csv, .xlsx atau .xls.');
        }

        $result = $this->importService->proses($fail);

        if ($result['status'] === 'error') {
            return back()->with('error', $result['mesej']);
        }

        $berjaya = $result['berjaya'] ?? 0;
        $gagal   = $result['gagal'] ?? 0;
        $mesej   = "Import selesai: {$berjaya} rekod berjaya diimport.";
        if ($gagal > 0) {
            $mesej .= " {$gagal} rekod gagal (data tidak lengkap atau pendua).";
        }

        return redirect()->route('import.index')->with('success', $mesej);
    }

    public function template()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="templat_pelajar.csv"',
        ];

        $callback = function () {
            $f = fopen('php://output', 'w');
            fputs($f, "\xEF\xBB\xBF");
            fputcsv($f, [
                'Nama Penuh',
                'No. Matrik',
                'Program',
                'Fakulti',
                'Semester Semasa',
                'Status Pengajian',
                'Tarikh Tamat Tajaan',
            ]);
            fputcsv($f, [
                'Ahmad Faris bin Razali',
                'D20231234',
                'Sarjana Muda Pendidikan',
                'Fakulti Sains dan Matematik',
                'Semester 3',
                'Aktif',
                '2026-12-31',
            ]);
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }
}
