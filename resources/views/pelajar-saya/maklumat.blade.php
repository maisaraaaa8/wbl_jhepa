@extends('layouts.app-pelajar')

@section('title', 'Maklumat Pelajar')
@section('page-title', 'Maklumat Pelajar')

@push('styles')
<style>
.mk-hero {
    background: linear-gradient(135deg, var(--primary) 0%, #1a3f73 55%, #c9891a 100%);
    border-radius: var(--radius-lg);
    padding: 24px 28px;
    color: #fff;
    margin-bottom: 20px;
    display: flex; align-items: center; gap: 18px; flex-wrap: wrap;
}
.mk-avatar {
    width: 60px; height: 60px; border-radius: 50%;
    background: rgba(255,255,255,0.16);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; font-weight: 700; flex-shrink: 0;
    border: 2px solid rgba(255,255,255,0.3);
}
.mk-hero-name { font-size: 19px; font-weight: 700; }
.mk-hero-sub  { font-size: 12.5px; opacity: .85; margin-top: 3px; }
.mk-hero-meta { font-size: 12px; opacity: .8; margin-top: 6px; display: flex; gap: 14px; flex-wrap: wrap; }
.mk-hero-meta span { display: inline-flex; align-items: center; gap: 5px; }

.info-grid {
    padding: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 18px;
}
.info-label {
    font-size: 11px; color: var(--text-muted); font-weight: 600;
    text-transform: uppercase; margin-bottom: 4px; letter-spacing: .03em;
}
.info-value { font-size: 14px; color: var(--text); }
.info-value.muted { color: var(--text-muted); font-style: italic; font-size: 13px; }
</style>
@endpush

@section('content')

@if($pelajar)
@php
    $namaP   = $pelajar['nama_pelajar'] ?? 'Pelajar';
    $initial = strtoupper(collect(explode(' ', $namaP))->map(fn($w) => $w[0] ?? '')->take(2)->implode(''));

    $status      = $pelajar['status_pengajian'] ?? 'Aktif';
    $statusBadge = match(strtolower($status)) {
        'aktif'   => 'green',
        'tangguh' => 'warn',
        'tamat'   => 'red',
        default   => 'gray',
    };
@endphp

{{-- ═══ HERO PROFIL ═══ --}}
<div class="mk-hero">
    <div class="mk-avatar">{{ $initial }}</div>
    <div style="flex:1;min-width:0">
        <div class="mk-hero-name">{{ $namaP }}</div>
        <div class="mk-hero-sub">{{ $pelajar['no_matrik'] ?? '—' }}</div>
        <div class="mk-hero-meta">
            <span><i class="ti ti-building-bank"></i> {{ $pelajar['fakulti'] ?? '—' }}</span>
            <span><i class="ti ti-calendar"></i> {{ $pelajar['semester'] ?? '—' }}</span>
        </div>
    </div>
    <span class="badge {{ $statusBadge }}" style="font-size:12px;padding:4px 12px">{{ $status }}</span>
</div>

{{-- ═══ MAKLUMAT PERIBADI ═══ --}}
<div class="table-wrap">
    <div class="table-header">
        <div class="section-title">
            <i class="ti ti-id-badge-2"></i> Maklumat Peribadi
        </div>
    </div>
    <div class="info-grid">
        <div>
            <div class="info-label">Nama Penuh</div>
            <div class="info-value">{{ $pelajar['nama_pelajar'] ?? '—' }}</div>
        </div>
        <div>
            <div class="info-label">No. Matrik</div>
            <div class="info-value">{{ $pelajar['no_matrik'] ?? '—' }}</div>
        </div>
        <div>
            <div class="info-label">No. Kad Pengenalan</div>
            @if(!empty($pelajar['no_ic']))
                <div class="info-value">{{ $pelajar['no_ic'] }}</div>
            @else
                <div class="info-value muted">Belum dikemaskini</div>
            @endif
        </div>
        <div>
            <div class="info-label">Alamat</div>
            @if(!empty($pelajar['alamat']))
                <div class="info-value">{{ $pelajar['alamat'] }}</div>
            @else
                <div class="info-value muted">Belum dikemaskini</div>
            @endif
        </div>
    </div>
</div>

{{-- ═══ MAKLUMAT AKADEMIK ═══ --}}
<div class="table-wrap" style="margin-top:18px">
    <div class="table-header">
        <div class="section-title">
            <i class="ti ti-school"></i> Maklumat Akademik
        </div>
    </div>
    <div class="info-grid">
        <div>
            <div class="info-label">Program</div>
            <div class="info-value">{{ $pelajar['program'] ?? '—' }}</div>
        </div>
        <div>
            <div class="info-label">Fakulti</div>
            <div class="info-value">{{ $pelajar['fakulti'] ?? '—' }}</div>
        </div>
        <div>
            <div class="info-label">Semester</div>
            <div class="info-value">
                @if(!empty($pelajar['semester']))
                    <span class="badge blue">{{ $pelajar['semester'] }}</span>
                @else
                    —
                @endif
            </div>
        </div>
        <div>
            <div class="info-label">Status Pengajian</div>
            <div class="info-value">
                <span class="badge {{ $statusBadge }}">{{ $status }}</span>
            </div>
        </div>
        <div>
            <div class="info-label">Tarikh Tamat Tajaan</div>
            <div class="info-value">
                {{ !empty($pelajar['tarikh_tamat_tajaan']) ? \Carbon\Carbon::parse($pelajar['tarikh_tamat_tajaan'])->format('d M Y') : '—' }}
            </div>
        </div>
        <div>
            <div class="info-label">Status Kelulusan Mesyuarat</div>
            <div class="info-value">
                @if(!empty($pelajar['tarikh_mesyuarat_diluluskan']))
                    <span class="badge green">
                        Diluluskan — {{ \Carbon\Carbon::parse($pelajar['tarikh_mesyuarat_diluluskan'])->format('d M Y') }}
                    </span>
                @else
                    <span class="badge gray">Belum Diluluskan</span>
                @endif
            </div>
        </div>
    </div>
</div>

@else
{{-- ═══ TIADA REKOD ═══ --}}
<div class="table-wrap">
    <div style="text-align:center;padding:60px 20px;color:var(--text-muted)">
        <i class="ti ti-user-question" style="font-size:40px;display:block;margin-bottom:12px"></i>
        <div style="font-size:14px;font-weight:600;margin-bottom:4px">Rekod Pelajar Tidak Dijumpai</div>
        <div style="font-size:13px">Akaun anda belum dipautkan dengan mana-mana rekod pelajar. Sila hubungi pihak pentadbir.</div>
    </div>
</div>
@endif

@endsection
