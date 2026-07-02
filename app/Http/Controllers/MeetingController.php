<?php

namespace App\Http\Controllers;

use App\Services\MeetingService;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    protected MeetingService $meetingService;

    public function __construct(MeetingService $meetingService)
    {
        $this->meetingService = $meetingService;
    }

    public function index(Request $request)
    {
        $filters = [];
        if ($request->filled('id_pelajar')) $filters['id_pelajar'] = $request->id_pelajar;
        if ($request->filled('jenis'))      $filters['jenis']      = $request->jenis;

        $meetings    = $this->meetingService->getAll($filters);
        $stats       = $this->meetingService->getStats();
        $pelajarList = $this->meetingService->getPelajarList();

        return view('meeting.index', compact('meetings', 'stats', 'pelajarList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pelajar'       => 'required',
            'tarikh_pertemuan' => 'required|date',
            'jenis_pertemuan'  => 'required',
            'jumlah_sesi'      => 'required|integer|min:1',
        ], [
            'id_pelajar.required'       => 'Sila pilih pelajar.',
            'tarikh_pertemuan.required' => 'Tarikh pertemuan wajib diisi.',
            'jenis_pertemuan.required'  => 'Jenis pertemuan wajib dipilih.',
            'jumlah_sesi.required'      => 'Jumlah sesi wajib diisi.',
        ]);

        $result = $this->meetingService->simpan($request->all());

        if (!$result) {
            return back()->withInput()->with('error', 'Gagal menyimpan rekod pertemuan.');
        }

        return redirect()->route('meeting.index')
            ->with('success', 'Rekod pertemuan berjaya disimpan.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'tarikh_pertemuan' => 'required|date',
            'jenis_pertemuan'  => 'required',
            'jumlah_sesi'      => 'required|integer|min:1',
        ]);

        $result = $this->meetingService->kemaskini($id, $request->all());

        if (!$result) {
            return back()->withInput()->with('error', 'Gagal mengemaskini rekod.');
        }

        return redirect()->route('meeting.index')
            ->with('success', 'Rekod pertemuan berjaya dikemaskini.');
    }

    public function destroy(string $id)
    {
        $berjaya = $this->meetingService->padam($id);

        return redirect()->route('meeting.index')
            ->with(
                $berjaya ? 'success' : 'error',
                $berjaya ? 'Rekod pertemuan berjaya dipadam.' : 'Gagal memadam rekod.'
            );
    }
}
