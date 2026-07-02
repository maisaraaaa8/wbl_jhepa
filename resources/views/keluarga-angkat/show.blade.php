@extends('layouts.app')

@section('title', 'Detail Keluarga Angkat')
@section('page-title', 'Keluarga Angkat')

@section('topbar-actions')
    <a href="{{ route('keluarga.index') }}" class="topbar-btn">
        <i class="ti ti-arrow-left"></i> Kembali
    </a>
    @if(session('peranan') === 'admin')
    <button class="topbar-btn primary" onclick="openModal('modal-edit')">
        <i class="ti ti-edit"></i> Kemaskini
    </button>
    @endif
@endsection

@section('content')

<div style="margin-bottom:20px">
    <h2 style="font-size:20px;font-weight:500;margin-bottom:4px">
        {{ $keluarga['nama_keluarga_angkat'] }}
    </h2>
    <p style="font-size:13px;color:var(--text-secondary)">
        Detail maklumat keluarga angkat
    </p>
</div>

<div class="two-col" style="align-items:start;gap:20px">

    {{-- Kiri: Maklumat Keluarga Angkat --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        <div class="card" style="margin-bottom:0">
            <h3><i class="ti ti-home-heart"></i> Maklumat Keluarga Angkat</h3>

            @php
                $status = $keluarga['status_display'] ?? $keluarga['status_tajaan'] ?? 'Aktif';
                $statusColor = match($status) {
                    'Aktif'            => 'green',
                    'Hampir Tamat'     => 'warn',
                    'Tamat'            => 'red',
                    'Belum Ditugaskan' => 'gray',
                    default            => 'gray',
                };
            @endphp

            <div class="info-row">
                <span class="info-label">Nama Keluarga Angkat</span>
                <span class="info-val">{{ $keluarga['nama_keluarga_angkat'] ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">No. Telefon</span>
                <span class="info-val">{{ $keluarga['no_telefon'] ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Jabatan</span>
                <span class="info-val">{{ $keluarga['jabatan'] ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">No. Kad Pengenalan</span>
                <span class="info-val">{{ $keluarga['no_ic'] ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Alamat</span>
                <span class="info-val" style="text-align:right;max-width:60%">{{ $keluarga['alamat'] ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Identiti Kepada Pelajar</span>
                <span class="info-val">
                    @if($keluarga['hide_identity'] ?? false)
                        <span class="badge warn"><i class="ti ti-eye-off"></i> Disorok</span>
                    @else
                        <span class="badge green"><i class="ti ti-eye"></i> Ditunjukkan</span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Status Tajaan</span>
                <span class="info-val">
                    <span class="badge {{ $statusColor }}">{{ $status }}</span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Tarikh Tamat Tajaan</span>
                <span class="info-val">
                    @if($keluarga['tarikh_tamat_tajaan'] ?? null)
                        @php
                            $tamat = \Carbon\Carbon::parse($keluarga['tarikh_tamat_tajaan']);
                            $bulanLagi = now()->diffInMonths($tamat, false);
                        @endphp
                        <span class="{{ $bulanLagi <= 2 && $bulanLagi >= 0 ? 'badge warn' : '' }}">
                            {{ $tamat->format('d M Y') }}
                            @if($bulanLagi > 0)
                                <span style="font-size:11px;color:var(--text-muted)">({{ $bulanLagi }} bulan lagi)</span>
                            @elseif($bulanLagi == 0)
                                <span style="font-size:11px;color:#b91c1c">(bulan ini)</span>
                            @endif
                        </span>
                    @else
                        —
                    @endif
                </span>
            </div>
        </div>

        {{-- Butang Padam --}}
        @if(session('peranan') === 'admin')
        <div class="card" style="margin-bottom:0;border-color:#fecaca">
            <h3 style="color:#b91c1c"><i class="ti ti-alert-triangle"></i> Zon Bahaya</h3>
            <p style="font-size:13px;color:var(--text-secondary);margin-bottom:12px">
                Pemadaman rekod ini tidak boleh dibatalkan. Semua data berkaitan akan hilang.
            </p>
            <button onclick="confirmPadam('{{ $keluarga['id_keluarga_angkat'] }}','{{ addslashes($keluarga['nama_keluarga_angkat']) }}')"
                class="btn danger">
                <i class="ti ti-trash"></i> Padam Rekod Ini
            </button>
        </div>
        @endif

    </div>

    {{-- Kanan: Pelajar Ditugaskan --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        <div class="card" style="margin-bottom:0">
            <h3><i class="ti ti-user"></i> Pelajar Ditugaskan</h3>

            @if($pelajar)
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;padding-bottom:16px;border-bottom:0.5px solid var(--border)">
                    <div style="width:44px;height:44px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:600;color:#fff;flex-shrink:0">
                        {{ strtoupper(substr($pelajar['nama_pelajar'] ?? 'P', 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:500;font-size:14px">{{ $pelajar['nama_pelajar'] ?? '—' }}</div>
                        <div style="font-size:12px;color:var(--text-muted)">{{ $pelajar['no_matrik'] ?? '' }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <span class="info-label">Semester</span>
                    <span class="info-val">
                        @if($pelajar['semester'] ?? null)
                            <span class="badge blue">{{ $pelajar['semester'] }}</span>
                        @else —
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status Pengajian</span>
                    <span class="info-val">
                        <span class="badge {{ ($pelajar['status_pengajian'] ?? '') === 'Aktif' ? 'green' : 'gray' }}">
                            {{ $pelajar['status_pengajian'] ?? '—' }}
                        </span>
                    </span>
                </div>

                <div style="margin-top:14px">
                    <a href="{{ route('pelajar.show', $pelajar['id_pelajar']) }}" class="btn primary" style="width:100%;justify-content:center">
                        <i class="ti ti-eye"></i> Lihat Profil Pelajar
                    </a>
                </div>

            @else
                <div class="empty-state" style="padding:24px">
                    <i class="ti ti-user-off"></i>
                    <p>Tiada pelajar ditugaskan</p>
                </div>
                @if(session('peranan') === 'admin')
                <button onclick="bukaModalTugaskan('{{ $keluarga['id_keluarga_angkat'] }}','{{ addslashes($keluarga['nama_keluarga_angkat']) }}')"
                    class="btn primary" style="width:100%;justify-content:center;margin-top:8px">
                    <i class="ti ti-user-plus"></i> Tugaskan Pelajar
                </button>
                @endif
            @endif
        </div>

    </div>
</div>

@if(session('peranan') === 'admin')

{{-- Modal Kemaskini --}}
<div class="modal-overlay" id="modal-edit">
    <div class="modal" style="max-width:520px">
        <div class="modal-head">
            <h3><i class="ti ti-edit"></i> Kemaskini Keluarga Angkat</h3>
            <button class="modal-close" onclick="closeModal('modal-edit')"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" action="{{ route('keluarga.update', $keluarga['id_keluarga_angkat']) }}">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Penuh <span class="req">*</span></label>
                        <input type="text" name="nama_keluarga_angkat"
                            value="{{ $keluarga['nama_keluarga_angkat'] }}" required>
                    </div>
                    <div class="form-group">
                        <label>No. Telefon</label>
                        <input type="tel" name="no_telefon" value="{{ $keluarga['no_telefon'] ?? '' }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Jabatan</label>
                        <input type="text" name="jabatan" value="{{ $keluarga['jabatan'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label>No. Kad Pengenalan</label>
                        <input type="text" name="no_ic" value="{{ $keluarga['no_ic'] ?? '' }}">
                    </div>
                </div>
                <div class="form-row one">
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat">{{ $keluarga['alamat'] ?? '' }}</textarea>
                    </div>
                </div>
                <div class="form-row one">
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                            <input type="checkbox" name="hide_identity" value="1" style="width:auto;"
                                {{ ($keluarga['hide_identity'] ?? false) ? 'checked' : '' }}>
                            <i class="ti ti-eye-off"></i> Sorok Identiti Daripada Pelajar
                        </label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Status Tajaan</label>
                        <select name="status_tajaan">
                            <option value="Aktif"        {{ ($keluarga['status_tajaan'] ?? '') === 'Aktif'        ? 'selected' : '' }}>Aktif</option>
                            <option value="Hampir Tamat" {{ ($keluarga['status_tajaan'] ?? '') === 'Hampir Tamat' ? 'selected' : '' }}>Hampir Tamat</option>
                            <option value="Tamat"        {{ ($keluarga['status_tajaan'] ?? '') === 'Tamat'        ? 'selected' : '' }}>Tamat</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tarikh Tamat Tajaan</label>
                        <input type="date" name="tarikh_tamat_tajaan"
                            value="{{ $keluarga['tarikh_tamat_tajaan'] ?? '' }}">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeModal('modal-edit')">Batal</button>
                <button type="submit" class="btn primary"><i class="ti ti-device-floppy"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tugaskan Pelajar --}}
<div class="modal-overlay" id="modal-tugaskan">
    <div class="modal" style="max-width:480px">
        <div class="modal-head">
            <h3><i class="ti ti-user-plus"></i> Tugaskan Pelajar</h3>
            <button class="modal-close" onclick="closeModal('modal-tugaskan')"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" id="form-tugaskan" action="{{ route('keluarga.tugaskan', $keluarga['id_keluarga_angkat']) }}">
            @csrf
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:12px">
                    <label>Pilih Pelajar <span class="req">*</span></label>
                    <select name="pelajar_id" required>
                        <option value="">— Pilih Pelajar —</option>
                        @php
                            $allPelajar = app(\App\Services\PelajarService::class)->getAll();
                        @endphp
                        @foreach($allPelajar as $p)
                        <option value="{{ $p['id_pelajar'] }}">{{ $p['nama_pelajar'] }} ({{ $p['no_matrik'] }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tarikh Mula</label>
                        <input type="date" name="tarikh_mula">
                    </div>
                    <div class="form-group">
                        <label>Tarikh Tamat</label>
                        <input type="date" name="tarikh_tamat">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeModal('modal-tugaskan')">Batal</button>
                <button type="submit" class="btn primary"><i class="ti ti-check"></i> Tugaskan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Padam --}}
<div class="modal-overlay" id="modal-padam">
    <div class="modal" style="max-width:400px">
        <div class="modal-head">
            <h3 style="color:#b91c1c"><i class="ti ti-alert-triangle"></i> Sahkan Pemadaman</h3>
            <button class="modal-close" onclick="closeModal('modal-padam')"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body">
            <p style="font-size:13px;margin-bottom:16px">
                Anda pasti mahu memadam rekod <strong id="padam-nama"></strong>? Tindakan ini tidak boleh dibatalkan.
            </p>
            <form method="POST" id="form-padam">
                @csrf @method('DELETE')
                <div style="display:flex;gap:8px;justify-content:flex-end">
                    <button type="button" class="btn" onclick="closeModal('modal-padam')">Batal</button>
                    <button type="submit" class="btn danger"><i class="ti ti-trash"></i> Ya, Padam</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endif

@endsection

@push('scripts')
<script>
function confirmPadam(id, nama) {
    document.getElementById('padam-nama').textContent = nama;
    document.getElementById('form-padam').action = '/keluarga/' + id;
    openModal('modal-padam');
}
function bukaModalTugaskan(id, nama) {
    openModal('modal-tugaskan');
}
</script>
@endpush
