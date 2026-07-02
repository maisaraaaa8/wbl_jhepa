@extends('layouts.app')

@section('title', 'Tetapan & Akses')
@section('page-title', 'Tetapan & Akses')

@section('content')

<div style="margin-bottom:24px">
    <h2 style="font-size:20px;font-weight:500;margin-bottom:4px">Tetapan & Kawalan Akses</h2>
    <p style="font-size:13px;color:var(--text-secondary)">Urus pengguna sistem dan tahap akses</p>
</div>

<div class="two-col" style="align-items:start;gap:20px">

    {{-- ── Kiri: Senarai Pengguna ── --}}
    <div>
        <div class="card" style="margin-bottom:0">
            <h3><i class="ti ti-users"></i> Pengguna Sistem</h3>

            @forelse($pengguna as $p)
            <div class="access-row">
                <div style="flex:1;min-width:0">
                    <div style="font-weight:500;font-size:13px">{{ $p['nama'] ?? '—' }}</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px">
                        {{ $p['no_matrik'] ?? '' }}
                        @if(!empty($p['no_matrik']) && !empty($p['email'])) · @endif
                        {{ $p['email'] ?? '' }}
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                    {{-- Badge Peranan --}}
                    <span class="user-role-badge {{ ($p['role'] ?? '') === 'admin' ? 'admin' : 'readonly' }}">
                        {{ ($p['role'] ?? '') === 'admin' ? 'Admin Penuh' : 'Baca Sahaja' }}
                    </span>

                    {{-- Toggle Peranan --}}
                    @if(session('user_id') !== $p['id'])
                    <form method="POST" action="{{ route('tetapan.update-peranan', $p['id']) }}" style="display:inline">
                        @csrf @method('PATCH')
                        <input type="hidden" name="role" value="{{ ($p['role'] ?? '') === 'admin' ? 'readonly' : 'admin' }}">
                        <button type="submit"
                            class="toggle {{ ($p['role'] ?? '') === 'admin' ? 'on' : '' }}"
                            title="Tukar peranan"
                            onclick="return confirm('Tukar peranan {{ addslashes($p['nama'] ?? '') }}?')"
                        ></button>
                    </form>

                    {{-- Padam --}}
                    <button
                        onclick="confirmPadamUser('{{ $p['id'] }}','{{ addslashes($p['nama'] ?? '') }}')"
                        class="btn danger" style="padding:3px 8px;font-size:11px" title="Padam">
                        <i class="ti ti-trash"></i>
                    </button>
                    @else
                    <span style="font-size:11px;color:var(--text-muted)">(anda)</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-state" style="padding:24px">
                <i class="ti ti-users-off"></i>
                <p>Tiada pengguna didaftarkan.</p>
            </div>
            @endforelse

            {{-- Butang Tambah Pengguna --}}
            <div style="margin-top:16px">
                <button onclick="openModal('modal-tambah')" class="btn primary">
                    <i class="ti ti-user-plus"></i> Tambah Pengguna
                </button>
            </div>
        </div>
    </div>

    {{-- ── Kanan: Kebenaran Akses ── --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Kebenaran Akses --}}
        <div class="card" style="margin-bottom:0">
            <h3><i class="ti ti-shield-check"></i> Kebenaran Akses</h3>
            @foreach($kebenaran as $k)
            <div class="access-row">
                <span class="user-role-badge {{ $k['peranan'] === 'admin' ? 'admin' : 'readonly' }}">
                    {{ $k['label'] }}
                </span>
                <span style="font-size:13px;color:var(--text-secondary);text-align:right">{{ $k['hurai'] }}</span>
            </div>
            @endforeach
        </div>

        {{-- Ringkasan --}}
        <div class="card" style="margin-bottom:0">
            <h3><i class="ti ti-chart-pie"></i> Ringkasan Pengguna</h3>
            @php
                $jumlahAdmin    = collect($pengguna)->where('role','admin')->count();
                $jumlahReadonly = collect($pengguna)->where('role','readonly')->count();
                $jumlahTotal    = count($pengguna);
            @endphp
            <div class="info-row">
                <span class="info-label">Jumlah Pengguna</span>
                <span class="info-val">{{ $jumlahTotal }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Admin Penuh</span>
                <span class="info-val">{{ $jumlahAdmin }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Baca Sahaja</span>
                <span class="info-val">{{ $jumlahReadonly }}</span>
            </div>
        </div>

    </div>
</div>

{{-- ── Modal Tambah Pengguna ── --}}
<div class="modal-overlay" id="modal-tambah">
    <div class="modal" style="max-width:480px">
        <div class="modal-head">
            <h3>Tambah Pengguna Baru</h3>
            <button class="modal-close" onclick="closeModal('modal-tambah')"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" action="{{ route('tetapan.tambah-user') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:12px">
                    <label>Nama Penuh <span class="req">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" placeholder="cth: Ahmad bin Ali" required>
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group" style="margin-bottom:12px">
                    <label>No. Matrik</label>
                    <input type="text" name="no_matrik" value="{{ old('no_matrik') }}" placeholder="cth: D20231106448">
                </div>
                <div class="form-group" style="margin-bottom:12px">
                    <label>Emel <span class="req">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="cth: ahmad@upsi.edu.my" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group" style="margin-bottom:12px">
                    <label>Kata Laluan <span class="req">*</span></label>
                    <input type="password" name="password" placeholder="Minimum 8 aksara" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label>Peranan <span class="req">*</span></label>
                    <select name="role" required>
                        <option value="readonly" {{ old('role') == 'readonly' ? 'selected' : '' }}>Baca Sahaja</option>
                        <option value="admin"    {{ old('role') == 'admin'    ? 'selected' : '' }}>Admin Penuh</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeModal('modal-tambah')">Batal</button>
                <button type="submit" class="btn primary"><i class="ti ti-user-plus"></i> Daftar</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal Confirm Padam ── --}}
<div class="modal-overlay" id="modal-padam-user">
    <div class="modal" style="max-width:400px">
        <div class="modal-head">
            <h3>Sahkan Pemadaman</h3>
            <button class="modal-close" onclick="closeModal('modal-padam-user')"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body">
            <p style="font-size:13px;margin-bottom:16px">
                Anda pasti mahu memadam akaun pengguna <strong id="padam-user-nama"></strong>?
                Tindakan ini tidak boleh dibatalkan.
            </p>
            <form method="POST" id="form-padam-user">
                @csrf @method('DELETE')
                <div class="btn-row" style="display:flex;gap:8px;justify-content:flex-end">
                    <button type="button" class="btn" onclick="closeModal('modal-padam-user')">Batal</button>
                    <button type="submit" class="btn danger"><i class="ti ti-trash"></i> Ya, Padam</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function confirmPadamUser(id, nama) {
    document.getElementById('padam-user-nama').textContent = nama;
    document.getElementById('form-padam-user').action = '/tetapan/pengguna/' + id;
    openModal('modal-padam-user');
}

// Buka modal jika ada validation error masa tambah user
@if($errors->any())
    openModal('modal-tambah');
@endif
</script>
@endpush
