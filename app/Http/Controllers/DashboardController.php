<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Helpers\SessionUser;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        // JANGAN guna Auth::user() — ia null sebab kita guna Supabase session
        // Guna SessionUser::role() yang baca dari session('role')
        $role = SessionUser::role();

        // Redirect ikut peranan — jangan tunjuk admin dashboard kepada pelajar/keluarga
        if ($role === 'pelajar') {
            return $this->dashboardPelajar();
        }

        if ($role === 'keluarga_angkat') {
            return $this->dashboardKeluargaAngkat();
        }

        // Admin atau readonly — tunjuk admin dashboard
        $stats = $this->dashboardService->getStats();

        return view('dashboard.admin', [
            'jumlahPelajar'    => $stats['jumlah_pelajar'],
            'jumlahKeluarga'   => $stats['jumlah_keluarga'],
            'belumBerpasangan' => $stats['belum_berpasangan'],
            'jumlahSumbangan'  => $stats['jumlah_sumbangan'],
            'purataSumbangan'  => $stats['purata_sumbangan'],
            'hampirTamat'      => $stats['hampir_tamat'],
            'recentPelajar'    => $stats['recent_pelajar'],
            'fakultiChart'     => $stats['fakulti_chart'],
            'fakultiList'      => $stats['fakulti_list'],
            'statusChart'      => $stats['status_chart'],
            'sumbanganTrend'   => $stats['sumbangan_trend'],
            'topPelajar'       => $stats['top_pelajar'],
        ]);
    }

    // ── Dashboard untuk pelajar ────────────────────────────────────────
    private function dashboardPelajar()
    {
        // Cari rekod pelajar berdasarkan user_id dari session
        $userId = SessionUser::id();
        $db     = app(\App\Services\SupabaseService::class);

        // Ambil data pelajar sendiri
        $pelajarRows = $db->select('pelajar', [
            'select'  => '*',
            'user_id' => "eq.{$userId}",
            'limit'   => 1,
        ]);
        $pelajar = $pelajarRows[0] ?? null;
        $pid     = $pelajar['id_pelajar'] ?? null;

        // Prestasi
        $prestasi = $pid ? $db->select('prestasi', [
            'select'     => '*',
            'id_pelajar' => "eq.{$pid}",
            'order'      => 'id.asc',
        ]) : [];

        // Keluarga angkat
        $kaRows   = $pid ? $db->select('keluarga_angkat', [
            'select'     => 'nama_keluarga_angkat, no_telefon, alamat, status_tajaan, tarikh_tamat_tajaan',
            'id_pelajar' => "eq.{$pid}",
            'limit'      => 1,
        ]) : [];
        $keluarga = $kaRows[0] ?? null;

        // Sumbangan terkini
        $sumbangan = $pid ? $db->select('sumbangan', [
            'select'     => '*',
            'id_pelajar' => "eq.{$pid}",
            'order'      => 'tarikh_terima.desc',
            'limit'      => 6,
        ]) : [];

        return view('dashboard.pelajar', compact('pelajar', 'prestasi', 'keluarga', 'sumbangan'));
    }

    // ── Dashboard untuk keluarga angkat ───────────────────────────────
    private function dashboardKeluargaAngkat()
    {
        $userId = SessionUser::id();
        $db     = app(\App\Services\SupabaseService::class);

        // Cari rekod keluarga angkat berdasarkan user_id
        $kaRows  = $db->select('keluarga_angkat', [
            'select'  => '*',
            'user_id' => "eq.{$userId}",
            'limit'   => 1,
        ]);
        $keluarga = $kaRows[0] ?? null;
        $pid      = $keluarga['id_pelajar'] ?? null;

        // Pelajar yang ditanggung
        $pelajar = $pid ? $db->find('pelajar', $pid) : null;

        // Prestasi pelajar
        $prestasi = $pid ? $db->select('prestasi', [
            'select'     => '*',
            'id_pelajar' => "eq.{$pid}",
            'order'      => 'id.asc',
        ]) : [];

        // Sumbangan keluarga ini
        $sumbangan = $pid ? $db->select('sumbangan', [
            'select'     => '*',
            'id_pelajar' => "eq.{$pid}",
            'order'      => 'tarikh_terima.desc',
            'limit'      => 6,
        ]) : [];

        return view('dashboard.keluarga', compact('keluarga', 'pelajar', 'prestasi', 'sumbangan'));
    }
}
