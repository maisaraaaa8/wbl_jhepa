@extends('layouts.app')
@section('title','Tambah Pelajar')
@section('page-title','Tambah Pelajar')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h2>Tambah Pelajar Baharu</h2>
        <p>Isi maklumat lengkap pelajar program Protege Bitara</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('pelajar.index') }}" class="btn">
            <i class="ti ti-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<form method="POST" action="{{ route('pelajar.store') }}">
@csrf

{{-- MAKLUMAT ASAS --}}
<div class="card">
    <h3><i class="ti ti-user"></i> Maklumat Pelajar</h3>
    <div class="form-row">
        <div class="form-group">
            <label>Nama Penuh <span class="req">*</span></label>
            <input type="text" name="nama_pelajar"
                value="{{ old('nama_pelajar') }}"
                placeholder="cth: Ahmad Faris bin Razali" required>
            @error('nama_pelajar')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label>No. Matrik <span class="req">*</span></label>
            <input type="text" name="no_matrik"
                value="{{ old('no_matrik') }}"
                placeholder="cth: D20231012345" required>
            @error('no_matrik')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- NO. IC & ALAMAT (baharu) --}}
    <div class="form-row">
        <div class="form-group">
            <label>No. Kad Pengenalan (IC)</label>
            <input type="text" name="no_ic"
                value="{{ old('no_ic') }}"
                placeholder="cth: 040101-10-1234">
            @error('no_ic')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label>Alamat</label>
            <input type="text" name="alamat"
                value="{{ old('alamat') }}"
                placeholder="cth: No. 12, Jalan Damai, 43000 Kajang, Selangor">
            @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- PROGRAM & FAKULTI (baharu) --}}
    <div class="form-row">
        <div class="form-group">
            <label>Program Pengajian</label>
            <input type="text" name="program"
                value="{{ old('program') }}"
                placeholder="cth: Pendidikan Matematik">
            @error('program')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label>Fakulti</label>
            <input type="text" name="fakulti"
                value="{{ old('fakulti') }}"
                placeholder="cth: Fakulti Sains dan Matematik">
            @error('fakulti')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Semester <span class="req">*</span></label>
            <select name="semester">
                <option value="">— Pilih Semester —</option>
                @foreach(['Semester 1','Semester 2','Semester 3','Semester 4','Semester 5','Semester 6','Semester 7','Semester 8'] as $sem)
                <option value="{{ $sem }}" {{ old('semester') == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Status Pengajian</label>
            <select name="status_pengajian">
                <option value="Aktif"    {{ old('status_pengajian','Aktif') == 'Aktif'    ? 'selected' : '' }}>Aktif</option>
                <option value="Tangguh"  {{ old('status_pengajian')         == 'Tangguh'  ? 'selected' : '' }}>Tangguh</option>
                <option value="Tamat"    {{ old('status_pengajian')         == 'Tamat'    ? 'selected' : '' }}>Tamat</option>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Tarikh Tamat Tajaan</label>
            <input type="date" name="tarikh_tamat_tajaan"
                value="{{ old('tarikh_tamat_tajaan') }}">
            <div class="form-hint">Sistem akan beri amaran 2 bulan sebelum tarikh ini.</div>
        </div>
        <div class="form-group">
            <label>Tarikh Mesyuarat Diluluskan</label>
            <input type="date" name="tarikh_mesyuarat_diluluskan"
                value="{{ old('tarikh_mesyuarat_diluluskan') }}">
            <div class="form-hint">Tarikh permohonan pelajar ini diluluskan dalam mesyuarat.</div>
            @error('tarikh_mesyuarat_diluluskan')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

{{-- AKAUN LOGIN (PILIHAN) — supaya pelajar boleh log masuk ke portal sendiri --}}
<div class="card">
    <h3><i class="ti ti-lock"></i> Akaun Login (Pilihan)</h3>
    <p style="font-size:.8rem;color:var(--text-muted);margin:-0.3rem 0 .85rem;">
        Isi emel & kata laluan jika pelajar ini perlu log masuk ke portal pelajar sistem.
        Kosongkan jika belum perlu — anda boleh cipta akaun kemudian.
    </p>
    <div class="form-row">
        <div class="form-group">
            <label>Emel</label>
            <input type="email" name="email" value="{{ old('email') }}"
                placeholder="cth: pelajar@email.com">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label>Kata Laluan</label>
            <input type="text" name="password" placeholder="Min. 8 aksara" autocomplete="new-password">
            <span class="form-hint">Kosongkan untuk guna kata laluan lalai sistem</span>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="btn-right">
    <a href="{{ route('pelajar.index') }}" class="btn">Batal</a>
    <button type="submit" class="btn primary">
        <i class="ti ti-device-floppy"></i> Simpan Pelajar
    </button>
</div>
</form>
@endsection
