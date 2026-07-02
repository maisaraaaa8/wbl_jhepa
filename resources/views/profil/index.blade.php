@php
    $roleSemasa = session('role', session('peranan'));
    $layoutIkutPeranan = match($roleSemasa) {
        'pelajar'         => 'layouts.app-pelajar',
        'keluarga_angkat' => 'layouts.app-keluarga',
        default           => 'layouts.app',
    };
@endphp
@extends($layoutIkutPeranan)

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')

<div style="margin-bottom:24px">
    <h2 style="font-size:20px;font-weight:500;margin-bottom:4px">Profil Saya</h2>
    <p style="font-size:13px;color:var(--text-secondary)">Urus maklumat akaun dan kata laluan anda</p>
</div>

<div class="two-col" style="align-items:start;gap:20px">

    {{-- ── Kiri: Maklumat Profil ── --}}
    <div>
        <div class="card" style="margin-bottom:0">
            <h3><i class="ti ti-user-circle"></i> Maklumat Akaun</h3>

            {{-- Avatar --}}
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;padding-bottom:16px;border-bottom:0.5px solid var(--border)">
                <div style="width:56px;height:56px;border-radius:50%;background:#f0f4f8;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1.5px solid #dde3ea">
                    <i class="ti ti-user" style="font-size:26px;color:#8896a4"></i>
                </div>
                <div>
                    <div style="font-size:16px;font-weight:500">{{ $user->nama ?? $user->email ?? '—' }}</div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                        <span class="badge {{ $user->role === 'admin' ? 'blue' : 'gray' }}" style="font-size:11px">
                            {{ $user->role === 'admin' ? 'Admin Penuh' : 'Baca Sahaja' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Form kemaskini --}}
            <form method="POST" action="{{ route('profile.kemaskini') }}">
                @csrf

                <div class="form-group" style="margin-bottom:12px">
                    <label>Nama Penuh</label>
                    <input type="text" name="nama" value="{{ old('nama', $user->nama ?? '') }}" placeholder="Nama penuh anda">
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-bottom:12px">
                    <label>No. Matrik / ID Pekerja</label>
                    <input type="text" name="no_matrik" value="{{ old('no_matrik', $user->no_matrik ?? '') }}" placeholder="cth: D20231106448">
                </div>

                <div class="form-group" style="margin-bottom:16px">
                    <label>Emel</label>
                    <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" placeholder="emel@upsi.edu.my">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn primary">
                    <i class="ti ti-device-floppy"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    {{-- ── Kanan: Tukar Kata Laluan ── --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        <div class="card" style="margin-bottom:0">
            <h3><i class="ti ti-lock"></i> Tukar Kata Laluan</h3>

            <form method="POST" action="{{ route('profile.tukar-password') }}">
                @csrf

                <div class="form-group" style="margin-bottom:12px">
                    <label>Kata Laluan Semasa</label>
                    <input type="password" name="current_password" placeholder="Kata laluan semasa">
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-bottom:12px">
                    <label>Kata Laluan Baru</label>
                    <input type="password" name="password" placeholder="Minimum 8 aksara">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-bottom:16px">
                    <label>Sahkan Kata Laluan Baru</label>
                    <input type="password" name="password_confirmation" placeholder="Taip semula kata laluan baru">
                </div>

                <button type="submit" name="action" value="tukar_password" class="btn primary">
                    <i class="ti ti-key"></i> Tukar Kata Laluan
                </button>
            </form>
        </div>

        {{-- Maklumat Sesi --}}
        <div class="card" style="margin-bottom:0">
            <h3><i class="ti ti-info-circle"></i> Maklumat Sesi</h3>
            <div class="info-row">
                <span class="info-label">Log masuk sebagai</span>
                <span class="info-val">{{ $user->email ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Peranan</span>
                <span class="info-val">
                    <span class="badge {{ $user->role === 'admin' ? 'blue' : 'gray' }}">
                        {{ $user->role === 'admin' ? 'Admin Penuh' : 'Baca Sahaja' }}
                    </span>
                </span>
            </div>
            <div style="margin-top:14px;padding-top:14px;border-top:0.5px solid var(--border)">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn danger" style="width:100%;justify-content:center">
                        <i class="ti ti-logout"></i> Log Keluar
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection
