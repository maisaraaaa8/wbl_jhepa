@extends('layouts.app')
@section('title', ($pelajar['nama_pelajar'] ?? 'Profil Pelajar'))
@section('page-title', 'Profil Pelajar')

@section('topbar-actions')
    @if(session('role', session('peranan')) === 'admin' && !empty($pelajar['id_pelajar']))
    <a href="{{ route('pelajar.edit', $pelajar['id_pelajar']) }}" class="topbar-btn primary">
        <i class="ti ti-edit"></i> Edit
    </a>
    @endif
@endsection

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
        <h2>{{ $pelajar['nama_pelajar'] ?? '—' }}</h2>
        <p>
            {{ $pelajar['no_matrik'] ?? '' }}
            @if($pelajar['program'] ?? null)
                · {{ $pelajar['program'] }}
            @endif
        </p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('pelajar.index') }}" class="btn">
            <i class="ti ti-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="two-col">

    {{-- KIRI --}}
    <div>
        <div class="card">
            <h3><i class="ti ti-user"></i> Maklumat Peribadi</h3>
            <div class="info-row">
                <span class="info-label">Nama Penuh</span>
                <span class="info-val">{{ $pelajar['nama_pelajar'] ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">No. Matrik</span>
                <span class="info-val">{{ $pelajar['no_matrik'] ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">No. Kad Pengenalan (IC)</span>
                <span class="info-val">{{ $pelajar['no_ic'] ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Alamat</span>
                <span class="info-val">{{ $pelajar['alamat'] ?? '—' }}</span>
            </div>
        </div>

        <div class="card">
            <h3><i class="ti ti-school"></i> Maklumat Akademik</h3>

            {{-- PROGRAM (baharu) --}}
            <div class="info-row">
                <span class="info-label">Program Pengajian</span>
                <span class="info-val">{{ $pelajar['program'] ?? '—' }}</span>
            </div>

            {{-- FAKULTI (baharu) --}}
            <div class="info-row">
                <span class="info-label">Fakulti</span>
                <span class="info-val">{{ $pelajar['fakulti'] ?? '—' }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Semester Semasa</span>
                <span class="info-val">
                    <span class="badge blue">{{ $pelajar['semester'] ?? '—' }}</span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Status Pengajian</span>
                <span class="info-val">
                    @php $sp = $pelajar['status_pengajian'] ?? ''; @endphp
                    @if(strtolower($sp) === 'aktif')
                        <span class="badge green">Aktif</span>
                    @elseif(strtolower($sp) === 'tangguh')
                        <span class="badge warn">Tangguh</span>
                    @else
                        <span class="badge gray">{{ $sp ?: 'Tidak Aktif' }}</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- KANAN --}}
    <div>
        <div class="card">
            <h3><i class="ti ti-gavel"></i> Kelulusan Mesyuarat</h3>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-val">
                    @if(!empty($pelajar['tarikh_mesyuarat_diluluskan']))
                        <span class="badge green">
                            Diluluskan · {{ \Carbon\Carbon::parse($pelajar['tarikh_mesyuarat_diluluskan'])->format('d M Y') }}
                        </span>
                    @else
                        <span class="badge gray">Belum Diluluskan</span>
                    @endif
                </span>
            </div>

            @if(session('role', session('peranan')) === 'admin')
            <div style="display:flex;gap:8px;margin-top:10px">
                <button type="button" class="btn primary" onclick="bukaLulus()">
                    <i class="ti ti-check"></i>
                    {{ !empty($pelajar['tarikh_mesyuarat_diluluskan']) ? 'Ubah Tarikh' : 'Luluskan Sekarang' }}
                </button>
                @if(!empty($pelajar['tarikh_mesyuarat_diluluskan']))
                <form method="POST" action="{{ route('pelajar.kelulusan-mesyuarat', $pid) }}"
                      onsubmit="return confirm('Batalkan kelulusan mesyuarat pelajar ini?')" style="display:contents">
                    @csrf @method('PATCH')
                    <input type="hidden" name="aksi" value="batal">
                    <button type="submit" class="btn danger">
                        <i class="ti ti-x"></i> Batalkan Kelulusan
                    </button>
                </form>
                @endif
            </div>
            @endif
        </div>

        <div class="card">
            <h3><i class="ti ti-home-heart"></i> Maklumat Tajaan</h3>
            <div class="info-row">
                <span class="info-label">Tarikh Tamat Tajaan</span>
                <span class="info-val">
                    @if(isset($pelajar['tarikh_tamat_tajaan']) && $pelajar['tarikh_tamat_tajaan'])
                        @php
                            $tamat     = \Carbon\Carbon::parse($pelajar['tarikh_tamat_tajaan']);
                            $bulanLagi = now()->diffInMonths($tamat, false);
                            $hampir    = $bulanLagi >= 0 && $bulanLagi <= 2;
                        @endphp
                        @if($hampir)
                            <span class="badge warn">
                                ⚠ {{ $tamat->format('d M Y') }}
                                <small>({{ $bulanLagi }} bulan lagi)</small>
                            </span>
                        @elseif($tamat->isPast())
                            <span class="badge red">{{ $tamat->format('d M Y') }} (Tamat)</span>
                        @else
                            {{ $tamat->format('d M Y') }}
                        @endif
                    @else
                        <span style="color:var(--text-muted)">—</span>
                    @endif
                </span>
            </div>
        </div>

        {{-- PADAM (admin sahaja) --}}
        @if(session('role', session('peranan')) === 'admin')
        <div class="card" style="border-color:#fecaca">
            <h3 style="color:#c53030"><i class="ti ti-alert-triangle"></i> Zon Bahaya</h3>
            <p style="font-size:13px;color:var(--text-secondary);margin-bottom:12px">
                Memadam pelajar akan memadamkan semua rekod berkaitan secara kekal.
            </p>
            <button onclick="confirmPadam('{{ $pid }}','{{ addslashes($pelajar['nama_pelajar'] ?? '') }}')"
                class="btn danger">
                <i class="ti ti-trash"></i> Padam Pelajar Ini
            </button>
        </div>
        @endif
    </div>
</div>

{{-- Modal Padam --}}
<div class="modal-overlay" id="modal-padam">
    <div class="modal" style="max-width:420px">
        <div class="modal-head">
            <h3>Sahkan Pemadaman</h3>
            <button class="close-btn" onclick="closeModal('modal-padam')"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body">
            <p style="font-size:13px;line-height:1.6;margin-bottom:16px">
                Anda pasti mahu memadam rekod pelajar
                <strong id="padam-nama" style="color:#c53030"></strong>?
                Tindakan ini <strong>tidak boleh dibatalkan</strong>.
            </p>
            <form method="POST" id="form-padam">
                @csrf
                @method('DELETE')
                <div class="btn-row">
                    <button type="button" class="btn" onclick="closeModal('modal-padam')">Batal</button>
                    <button type="submit" class="btn danger"><i class="ti ti-trash"></i> Ya, Padam</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Kelulusan Mesyuarat --}}
<div class="modal-overlay" id="modal-lulus">
    <div class="modal" style="max-width:400px">
        <div class="modal-head">
            <h3>Luluskan Mesyuarat</h3>
            <button class="close-btn" onclick="closeModal('modal-lulus')"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ route('pelajar.kelulusan-mesyuarat', $pid) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="aksi" value="luluskan">
                <div class="form-group">
                    <label>Tarikh Mesyuarat</label>
                    <input type="date" name="tarikh_mesyuarat_diluluskan"
                        value="{{ old('tarikh_mesyuarat_diluluskan', !empty($pelajar['tarikh_mesyuarat_diluluskan'])
                            ? \Carbon\Carbon::parse($pelajar['tarikh_mesyuarat_diluluskan'])->format('Y-m-d')
                            : now()->format('Y-m-d')) }}"
                        required>
                    <div class="form-hint">Tarikh permohonan pelajar ini diluluskan dalam mesyuarat.</div>
                </div>
                <div class="btn-row" style="margin-top:14px">
                    <button type="button" class="btn" onclick="closeModal('modal-lulus')">Batal</button>
                    <button type="submit" class="btn primary"><i class="ti ti-check"></i> Sahkan Kelulusan</button>
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
    document.getElementById('form-padam').action = '/pelajar/' + id;
    openModal('modal-padam');
}
function bukaLulus() {
    openModal('modal-lulus');
}
</script>
@endpush
