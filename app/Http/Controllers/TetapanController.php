<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TetapanController extends Controller
{
    protected SupabaseService $db;

    public function __construct(SupabaseService $db)
    {
        $this->db = $db;
    }

    // ──────────────────────────────────────────
    // TETAPAN & AKSES (Pengguna)
    // ──────────────────────────────────────────

    public function index()
    {
        $pengguna = $this->db->select('profiles', [
            'select' => 'id,nama,email,no_matrik,role',
            'order'  => 'nama.asc',
        ]);

        $kebenaran = [
            ['peranan' => 'admin',    'label' => 'Admin Penuh',  'hurai' => 'Semua modul — tambah, edit, padam, laporan'],
            ['peranan' => 'readonly', 'label' => 'Baca Sahaja',  'hurai' => 'Lihat rekod, laporan, notifikasi sahaja'],
        ];

        return view('tetapan.index', compact('pengguna', 'kebenaran'));
    }

    public function tambahUser(Request $request)
    {
        $request->validate([
            'nama'      => 'required|string|max:255',
            'email'     => 'required|email',
            'no_matrik' => 'nullable|string|max:50',
            'role'      => 'required|in:admin,readonly',
            'password'  => 'required|min:8',
        ]);

        $sedia = $this->db->select('profiles', [
            'email'  => 'eq.' . $request->email,
            'select' => 'id',
            'limit'  => 1,
        ]);

        if (!empty($sedia)) {
            return back()->withInput()->withErrors(['email' => 'Email ini sudah didaftarkan.']);
        }

        $data = [
            'nama'      => $request->nama,
            'email'     => $request->email,
            'no_matrik' => $request->no_matrik,
            'role'      => $request->role,
            'password'  => Hash::make($request->password),
        ];

        $result = $this->db->insert('profiles', $data);

        if (!$result) {
            return back()->withInput()->with('error', 'Gagal mendaftarkan pengguna.');
        }

        return back()->with('success', "Pengguna {$request->nama} berjaya ditambah.");
    }

    public function updatePeranan(Request $request, string $id)
    {
        $request->validate([
            'role' => 'required|in:admin,readonly',
        ]);

        if (session('user_id') === $id) {
            return back()->with('error', 'Anda tidak boleh menukar peranan anda sendiri.');
        }

        $result = $this->db->update('profiles', $id, ['role' => $request->role]);

        if (!$result) {
            return back()->with('error', 'Gagal mengemaskini peranan.');
        }

        return back()->with('success', 'Peranan pengguna berjaya dikemaskini.');
    }

    public function padamUser(string $id)
    {
        if (session('user_id') === $id) {
            return back()->with('error', 'Anda tidak boleh memadam akaun anda sendiri.');
        }

        $ok = $this->db->delete('profiles', $id);

        if (!$ok) {
            return back()->with('error', 'Gagal memadam pengguna.');
        }

        return back()->with('success', 'Pengguna berjaya dipadam.');
    }

    // ──────────────────────────────────────────
    // MAKLUM BALAS
    // ──────────────────────────────────────────

    public function maklumBalas()
    {
        // Admin: tengok semua maklum balas
        // User biasa: tengok maklum balas sendiri sahaja
        $isAdmin = session('peranan') === 'admin';

        $params = [
            'select' => 'id,jenis,mesej,created_at,user_id',
            'order'  => 'created_at.desc',
        ];

        if (!$isAdmin) {
            $params['user_id'] = 'eq.' . session('user_id');
        }

        $senarai = $this->db->select('maklum_balas', $params);

        // Kira statistik
        $stat = [
            'jumlah'    => count($senarai),
            'cadangan'  => collect($senarai)->where('jenis', 'cadangan')->count(),
            'aduan'     => collect($senarai)->where('jenis', 'aduan')->count(),
            'pujian'    => collect($senarai)->where('jenis', 'pujian')->count(),
            'lain'      => collect($senarai)->where('jenis', 'lain')->count(),
        ];

        return view('tetapan.maklum-balas', compact('senarai', 'stat', 'isAdmin'));
    }

    public function hantarMaklumBalas(Request $request)
    {
        $request->validate([
            'jenis' => 'required|in:cadangan,aduan,pujian,lain',
            'mesej' => 'required|string|min:10|max:1000',
        ]);

        $data = [
            'user_id' => session('user_id'),
            'jenis'   => $request->jenis,
            'mesej'   => $request->mesej,
        ];

        $result = $this->db->insert('maklum_balas', $data);

        if (!$result) {
            return back()->withInput()->with('error', 'Gagal menghantar maklum balas. Sila cuba lagi.');
        }

        return back()->with('success', 'Maklum balas berjaya dihantar. Terima kasih!');
    }

    public function padamMaklumBalas(string $id)
    {
        // Hanya admin boleh padam
        if (session('peranan') !== 'admin') {
            return back()->with('error', 'Anda tidak mempunyai kebenaran untuk tindakan ini.');
        }

        $ok = $this->db->delete('maklum_balas', $id);

        if (!$ok) {
            return back()->with('error', 'Gagal memadam maklum balas.');
        }

        return back()->with('success', 'Maklum balas berjaya dipadam.');
    }
}
