@extends('layouts.app')
@section('title', 'Edit Pelajar')
@section('page-title', 'Edit Pelajar')

@section('content')

@php $pid = $pelajar['id_pelajar'] ?? null; @endphp

@if(!$pid)
    <div class="card" style="text-align:center;padding:40px">
        <i class="ti ti-alert-circle" style="font-size:40px;color:#c53030;display:block;margin-bottom:12px"></i>
        <p>Rekod pelajar tidak dijumpai.</p>
        <a href="{{ route('pelajar.index') }}" class="btn" style="margin-top:12px">Kembali</a>
    </div>
@else

<div class="page-header">
    <div class="page-header-left">
        <h2>Edit Maklumat Pelajar</h2>
        <p>{{ $pelajar['nama_pelajar'] ?? '' }} · {{ $pelajar['no_matrik'] ?? '' }}</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('pelajar.show', $pid) }}" class="btn">
            <i class="ti ti-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<form method="POST" action="{{ route('pelajar.update', $pid) }}">
@csrf
@method('PUT')

<div class="card">
    <h3><i class="ti ti-user"></i> Maklumat Pelajar</h3>

    {{-- Baris 1: Nama & No Matrik --}}
    <div class="form-row">
        <div class="form-group">
            <label>Nama Penuh <span class="req">*</span></label>
            <input type="text" name="nama_pelajar"
                value="{{ old('nama_pelajar', $pelajar['nama_pelajar'] ?? '') }}"
                required class="{{ $errors->has('nama_pelajar') ? 'is-invalid' : '' }}">
            @error('nama_pelajar')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label>No. Matrik <span class="req">*</span></label>
            <input type="text" name="no_matrik"
                value="{{ old('no_matrik', $pelajar['no_matrik'] ?? '') }}"
                required class="{{ $errors->has('no_matrik') ? 'is-invalid' : '' }}">
            @error('no_matrik')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- Baris 1b: No. IC & Alamat (BAHARU) --}}
    <div class="form-row">
        <div class="form-group">
            <label>No. Kad Pengenalan (IC)</label>
            <input type="text" name="no_ic"
                value="{{ old('no_ic', $pelajar['no_ic'] ?? '') }}"
                placeholder="cth: 040101-10-1234"
                class="{{ $errors->has('no_ic') ? 'is-invalid' : '' }}">
            @error('no_ic')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label>Alamat</label>
            <input type="text" name="alamat"
                value="{{ old('alamat', $pelajar['alamat'] ?? '') }}"
                placeholder="cth: No. 12, Jalan Damai, 43000 Kajang, Selangor">
            @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- Baris 2: Program & Fakulti (BAHARU) --}}
    <div class="form-row">
        <div class="form-group">
            <label>Program Pengajian</label>
            <input type="text" name="program"
                value="{{ old('program', $pelajar['program'] ?? '') }}"
                placeholder="cth: Pendidikan Matematik">
        </div>
        <div class="form-group">
            <label>Fakulti</label>
            <input type="text" name="fakulti"
                value="{{ old('fakulti', $pelajar['fakulti'] ?? '') }}"
                placeholder="cth: Fakulti Sains dan Matematik">
        </div>
    </div>

    {{-- Baris 3: Semester & Status --}}
    <div class="form-row">
        <div class="form-group">
            <label>Semester</label>
            <select name="semester">
                <option value="">— Pilih Semester —</option>
                @foreach(['Semester 1','Semester 2','Semester 3','Semester 4','Semester 5','Semester 6','Semester 7','Semester 8'] as $sem)
                <option value="{{ $sem }}"
                    {{ old('semester', $pelajar['semester'] ?? '') == $sem ? 'selected' : '' }}>
                    {{ $sem }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Status Pengajian</label>
            <select name="status_pengajian">
                <option value="Aktif"      {{ old('status_pengajian', $pelajar['status_pengajian'] ?? '') === 'Aktif'      ? 'selected' : '' }}>Aktif</option>
                <option value="Tangguh"    {{ old('status_pengajian', $pelajar['status_pengajian'] ?? '') === 'Tangguh'    ? 'selected' : '' }}>Tangguh</option>
                <option value="Tidak Aktif"{{ old('status_pengajian', $pelajar['status_pengajian'] ?? '') === 'Tidak Aktif'? 'selected' : '' }}>Tidak Aktif</option>
                <option value="Tamat"      {{ old('status_pengajian', $pelajar['status_pengajian'] ?? '') === 'Tamat'      ? 'selected' : '' }}>Tamat</option>
            </select>
        </div>
    </div>

    {{-- Baris 4: Tarikh Tamat Tajaan & Tarikh Mesyuarat Diluluskan --}}
    <div class="form-row">
        <div class="form-group">
            <label>Tarikh Tamat Tajaan</label>
            <input type="date" name="tarikh_tamat_tajaan"
                value="{{ old('tarikh_tamat_tajaan', isset($pelajar['tarikh_tamat_tajaan'])
                    ? \Carbon\Carbon::parse($pelajar['tarikh_tamat_tajaan'])->format('Y-m-d')
                    : '') }}">
            <div class="form-hint">Sistem akan beri amaran 2 bulan sebelum tarikh ini.</div>
        </div>
        <div class="form-group">
            <label>Tarikh Mesyuarat Diluluskan</label>
            <input type="date" name="tarikh_mesyuarat_diluluskan"
                value="{{ old('tarikh_mesyuarat_diluluskan', isset($pelajar['tarikh_mesyuarat_diluluskan'])
                    ? \Carbon\Carbon::parse($pelajar['tarikh_mesyuarat_diluluskan'])->format('Y-m-d')
                    : '') }}">
            <div class="form-hint">Tarikh permohonan pelajar ini diluluskan dalam mesyuarat.</div>
            @error('tarikh_mesyuarat_diluluskan')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="btn-right">
    <a href="{{ route('pelajar.show', $pid) }}" class="btn">Batal</a>
    <button type="submit" class="btn primary">
        <i class="ti ti-device-floppy"></i> Simpan Perubahan
    </button>
</div>
</form>

@endif
@endsection
