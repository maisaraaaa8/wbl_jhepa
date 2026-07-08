<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PelajarController;
use App\Http\Controllers\PrestasiController;
use App\Http\Controllers\KeluargaAngkatController;
use App\Http\Controllers\SumbanganController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\ImportExcelController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\TetapanController;
use App\Http\Controllers\PelajarSayaController;
use App\Http\Controllers\KeluargaPortalController;

Route::get('/', function () {
    if (session('logged_in')) return redirect('/dashboard');
    return view('welcome');
});

// ── SEMENTARA: debug session — PADAM lepas siap test ───────────────────
Route::get('/debug-session', function () {
    return response()->json([
        'logged_in' => session('logged_in'),
        'user_id'   => session('user_id'),
        'email'     => session('email'),
        'role'      => session('role'),
        'peranan'   => session('peranan'),
    ]);
})->middleware('supabase.auth');

// ── Dashboard ──────────────────────────────────────────────────────────
// Route dalam group ini boleh diakses oleh SEMUA peranan yang log masuk:
// admin, readonly, pelajar, keluarga_angkat.
Route::middleware(['supabase.auth'])->group(function () {

    Route::get('/dashboard', [\\App\\Http\\Controllers\\DashboardController::class, 'index'])->name('dashboard');

    // Profil — semua peranan urus profil sendiri
    Route::get('/profil',                 [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profil/kemaskini',      [ProfileController::class, 'kemaskini'])->name('profile.kemaskini');
    Route::post('/profil/tukar-password', [ProfileController::class, 'tukarPassword'])->name('profile.tukar-password');

    // Maklum Balas — semua peranan boleh hantar maklum balas
    Route::get('/tetapan/maklum-balas',         [TetapanController::class, 'maklumBalas'])->name('tetapan.maklum-balas');
    Route::post('/tetapan/maklum-balas',        [TetapanController::class, 'hantarMaklumBalas'])->name('tetapan.hantar-maklum-balas');

    // ── Laluan khusus 'pelajar' — halaman peribadi diri sendiri sahaja ──
    Route::middleware(['role:pelajar'])->group(function () {
        Route::get('/saya/akademik',        [PelajarSayaController::class, 'akademik'])->name('pelajar.akademik');
        Route::get('/saya/keluarga-angkat', [PelajarSayaController::class, 'keluargaAngkat'])->name('pelajar.keluarga-angkat');
        Route::get('/saya/sumbangan',       [PelajarSayaController::class, 'sumbangan'])->name('pelajar.sumbangan');
        Route::get('/saya/maklumat',        [PelajarSayaController::class, 'maklumat'])->name('pelajar.maklumat');

        // Meeting Record — CRUD penuh, terhad kepada rekod diri sendiri
        Route::get('/saya/meeting',         [PelajarSayaController::class, 'meeting'])->name('pelajar.meeting');
        Route::post('/saya/meeting',        [PelajarSayaController::class, 'meetingStore'])->name('pelajar.meeting.store');
        Route::put('/saya/meeting/{id}',    [PelajarSayaController::class, 'meetingUpdate'])->name('pelajar.meeting.update');
        Route::patch('/saya/meeting/{id}',  [PelajarSayaController::class, 'meetingUpdate']);
        Route::delete('/saya/meeting/{id}', [PelajarSayaController::class, 'meetingDestroy'])->name('pelajar.meeting.destroy');
    });

    // ── Laluan khusus 'keluarga_angkat' — portal layan-diri keluarga angkat ──
    Route::middleware(['role:keluarga_angkat'])->group(function () {
        Route::get('/keluarga-portal/pelajar',   [KeluargaPortalController::class, 'pelajar'])->name('keluarga-portal.pelajar');
        Route::get('/keluarga-portal/prestasi',  [KeluargaPortalController::class, 'prestasi'])->name('keluarga-portal.prestasi');
        Route::get('/keluarga-portal/sumbangan', [KeluargaPortalController::class, 'sumbangan'])->name('keluarga-portal.sumbangan');

        // Meeting — keluarga angkat boleh lihat & urus rekod pertemuan
        // pelajar di bawah jagaan mereka sendiri sahaja (di-scope dalam controller).
        Route::get('/keluarga-portal/meeting',            [KeluargaPortalController::class, 'meeting'])->name('keluarga-portal.meeting');
        Route::post('/keluarga-portal/meeting',            [KeluargaPortalController::class, 'storeMeeting'])->name('keluarga-portal.meeting.store');
        Route::put('/keluarga-portal/meeting/{id}',        [KeluargaPortalController::class, 'updateMeeting'])->name('keluarga-portal.meeting.update');
        Route::delete('/keluarga-portal/meeting/{id}',     [KeluargaPortalController::class, 'destroyMeeting'])->name('keluarga-portal.meeting.destroy');
    });

    // Profile lama (kekal untuk compatibility — Laravel Breeze default)
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Modul Pentadbiran — HANYA untuk 'admin' & 'readonly' ────────────
    // Pelajar & keluarga angkat TIDAK boleh akses laluan-laluan di bawah ini
    // walaupun mereka tahu URL sekalipun (disekat oleh middleware 'role').
    Route::middleware(['role:admin,readonly'])->group(function () {

        // Pelajar
        Route::get('/pelajar',           [PelajarController::class, 'index'])->name('pelajar.index');
        Route::get('/pelajar/create',    [PelajarController::class, 'create'])->name('pelajar.create');
        Route::post('/pelajar',          [PelajarController::class, 'store'])->name('pelajar.store');
        Route::get('/pelajar/{id}',      [PelajarController::class, 'show'])->name('pelajar.show');
        Route::get('/pelajar/{id}/edit', [PelajarController::class, 'edit'])->name('pelajar.edit');
        Route::put('/pelajar/{id}',      [PelajarController::class, 'update'])->name('pelajar.update');
        Route::patch('/pelajar/{id}',    [PelajarController::class, 'update']);
        Route::patch('/pelajar/{id}/kelulusan-mesyuarat', [PelajarController::class, 'kemaskiniKelulusan'])->name('pelajar.kelulusan-mesyuarat');
        Route::delete('/pelajar/{id}',   [PelajarController::class, 'destroy'])->name('pelajar.destroy');

        // Prestasi
        Route::get('/prestasi',                     [PrestasiController::class, 'index'])->name('prestasi.index');
        Route::post('/prestasi',                    [PrestasiController::class, 'store'])->name('prestasi.store');
        Route::put('/prestasi/{id}',                [PrestasiController::class, 'update'])->name('prestasi.update');
        Route::delete('/prestasi/{id}',             [PrestasiController::class, 'destroy'])->name('prestasi.destroy');
        Route::get('/prestasi/pelajar/{pelajarId}', [PrestasiController::class, 'byPelajar'])->name('prestasi.by-pelajar');

        // Keluarga Angkat
        Route::get('/keluarga',                [KeluargaAngkatController::class, 'index'])->name('keluarga.index');
        Route::get('/keluarga/{id}',           [KeluargaAngkatController::class, 'show'])->name('keluarga.show');
        Route::post('/keluarga',               [KeluargaAngkatController::class, 'store'])->name('keluarga.store');
        Route::put('/keluarga/{id}',           [KeluargaAngkatController::class, 'update'])->name('keluarga.update');
        Route::delete('/keluarga/{id}',        [KeluargaAngkatController::class, 'destroy'])->name('keluarga.destroy');
        Route::post('/keluarga/{id}/tugaskan', [KeluargaAngkatController::class, 'tugaskan'])->name('keluarga.tugaskan');

        // Sumbangan
        Route::get('/sumbangan',                      [SumbanganController::class, 'index'])->name('sumbangan.index');
        Route::post('/sumbangan',                     [SumbanganController::class, 'store'])->name('sumbangan.store');
        Route::put('/sumbangan/{id}',                 [SumbanganController::class, 'update'])->name('sumbangan.update');
        Route::delete('/sumbangan/{id}',              [SumbanganController::class, 'destroy'])->name('sumbangan.destroy');
        Route::get('/sumbangan/sejarah/{id_pelajar}', [SumbanganController::class, 'sejarah'])->name('sumbangan.sejarah');

        // Meeting
        Route::get('/meeting',           [MeetingController::class, 'index'])->name('meeting.index');
        Route::post('/meeting',          [MeetingController::class, 'store'])->name('meeting.store');
        Route::put('/meeting/{id}',      [MeetingController::class, 'update'])->name('meeting.update');
        Route::delete('/meeting/{id}',   [MeetingController::class, 'destroy'])->name('meeting.destroy');

        // Laporan
        Route::get('/laporan',        [LaporanController::class, 'index'])->name('laporan.index');
        Route::post('/laporan/cetak', [LaporanController::class, 'cetak'])->name('laporan.cetak');

        // Notifikasi — data merentas SEMUA pelajar (GPA, tajaan, sumbangan tertunggak),
        // jadi TIDAK sesuai dibuka kepada pelajar/keluarga_angkat (risiko kebocoran data).
        Route::get('/notifikasi',              [NotifikasiController::class, 'index'])->name('notifikasi.index');
        Route::post('/notifikasi/{id}/baca',   [NotifikasiController::class, 'markRead'])->name('notifikasi.baca');

        // Import Excel
        Route::get('/import',          [ImportExcelController::class, 'index'])->name('import.index');
        Route::post('/import/proses',  [ImportExcelController::class, 'import'])->name('import.process');
        Route::get('/import/templat',  [ImportExcelController::class, 'template'])->name('import.template');

        // Tetapan & Akses (senarai pengguna) — 'admin' sahaja yang patut buat
        // perubahan sebenar, tapi kita benarkan 'readonly' lihat sahaja buat masa ini.
        Route::get('/tetapan',                      [TetapanController::class, 'index'])->name('tetapan.index');
        Route::post('/tetapan/tambah-user',         [TetapanController::class, 'tambahUser'])->name('tetapan.tambah-user');
        Route::patch('/tetapan/pengguna/{id}/peranan', [TetapanController::class, 'updatePeranan'])->name('tetapan.update-peranan');
        Route::delete('/tetapan/pengguna/{id}',     [TetapanController::class, 'padamUser'])->name('tetapan.padam-user');
        Route::delete('/tetapan/maklum-balas/{id}', [TetapanController::class, 'padamMaklumBalas'])->name('tetapan.padam-maklum-balas');
    });
});

require __DIR__.'/auth.php';

