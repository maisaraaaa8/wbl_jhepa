<?php

namespace App\Http\Controllers;

use App\Services\NotifikasiService;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    protected NotifikasiService $notifService;

    public function __construct(NotifikasiService $notifService)
    {
        $this->notifService = $notifService;
    }

    public function index()
    {
        $hampirTamat  = $this->notifService->getHampirTamat();
        $sumbTertunggak = $this->notifService->getSumbTertunggak();
        $notifSumbangan = $this->notifService->getNotifSumbangan();
        $notifGpa     = $this->notifService->getNotifGpaRendah();
        $stats        = $this->notifService->getStats();

        // Gabung semua notif lain
        $notifLain = array_merge($notifSumbangan, $notifGpa);

        return view('Notifikasi.index', compact(
            'hampirTamat', 'sumbTertunggak', 'notifLain', 'stats'
        ));
    }

    public function markRead(Request $request, string $id)
    {
        // Untuk future use — simpan dalam session buat masa ini
        $baca = session('notif_baca', []);
        $baca[] = $id;
        session(['notif_baca' => $baca]);

        return back()->with('success', 'Notifikasi telah ditandakan sebagai dibaca.');
    }
}
