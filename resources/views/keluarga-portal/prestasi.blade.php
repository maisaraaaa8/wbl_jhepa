@extends('layouts.app-keluarga')

@section('title', 'Prestasi')
@section('page-title', 'Prestasi')

@push('styles')
<style>
.ps-hero {
    background: linear-gradient(135deg, var(--primary) 0%, #1a3f73 55%, #c9891a 100%);
    border-radius: var(--radius-lg);
    padding: 22px 26px;
    color: #fff;
    margin-bottom: 18px;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px;
}
.ps-hero-eyebrow { font-size: 11px; opacity: .8; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px; }
.ps-hero-title { font-size: 19px; font-weight: 600; }
.ps-hero-sub { font-size: 12.5px; opacity: .85; margin-top: 3px; }
.ps-hero-icon {
    width: 46px; height: 46px; border-radius: 50%;
    background: rgba(255,255,255,.16);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; flex-shrink: 0;
}

.ps-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 18px; }
.ps-stat-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-lg); padding: 15px 16px;
    display: flex; align-items: center; gap: 12px;
}
.ps-stat-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.ps-stat-icon.violet { background: #f3effe; color: #6234a1; }
.ps-stat-icon.green  { background: #eaf3de; color: #3b6d11; }
.ps-stat-icon.blue   { background: #e6f1fb; color: #185fa5; }
.ps-stat-value { font-size: 20px; font-weight: 600; line-height: 1.15; }
.ps-stat-label { font-size: 11.5px; color: var(--text-muted); margin-top: 1px; }

.ps-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-lg); overflow: hidden;
}
.ps-card-head {
    display: flex; align-items: center; justify-content: space-between;
    gap: 10px; flex-wrap: wrap;
    padding: 16px 18px; border-bottom: 1px solid var(--border);
}
.ps-card-head-title { font-size: 14px; font-weight: 600; }
.ps-card-head-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

.ps-empty {
    background: var(--surface-2); border: 1px dashed var(--border-strong);
    border-radius: var(--radius-lg); padding: 48px 20px; text-align: center;
    color: var(--text-muted);
}
.ps-empty i { font-size: 40px; display: block; margin-bottom: 12px; opacity: .45; }

/* Tab pelajar (bila lebih dari 1 pelajar ditaja) */
.pelajar-tabs {
    display: flex; gap: 8px; flex-wrap: wrap;
    padding: 14px 18px; border-bottom: 1px solid var(--border);
}
.pelajar-tab {
    padding: 7px 14px; border-radius: 999px; border: 1px solid var(--border);
    background: var(--surface); color: var(--text-muted);
    font-size: 12.5px; font-weight: 500; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px; transition: all .15s;
}
.pelajar-tab:hover { border-color: var(--primary); color: var(--text); }
.pelajar-tab.active { background: var(--primary); border-color: var(--primary); color: #fff; }

.trend-badge { display: inline-flex; align-items: center; gap: 3px; font-size: 11.5px; font-weight: 600; }
.trend-badge.naik   { color: #3b6d11; }
.trend-badge.turun  { color: #a32d2d; }
.trend-badge.stabil { color: var(--text-muted); }

@media (max-width: 900px) { .ps-stats { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

@php
    $nama = session('nama', 'Keluarga Angkat');
@endphp

{{-- ═══ HERO ═══ --}}
<div class="ps-hero">
    <div>
        <div class="ps-hero-eyebrow">Portal Keluarga Angkat</div>
        <div class="ps-hero-title">Prestasi Akademik</div>
        <div class="ps-hero-sub">Prestasi pelajar di bawah jagaan {{ $nama }}</div>
    </div>
    <div class="ps-hero-icon"><i class="ti ti-chart-bar"></i></div>
</div>

@if(count($pelajarList) === 0)

    <div class="ps-card">
        <div style="padding:18px">
            <div class="ps-empty">
                <i class="ti ti-chart-bar-off"></i>
                <p style="font-size:13.5px;font-weight:500;color:var(--text)">Tiada pelajar ditugaskan kepada anda lagi.</p>
                <p style="font-size:12px;margin-top:5px">Sila hubungi pihak pentadbir untuk maklumat lanjut.</p>
            </div>
        </div>
    </div>

@else

{{-- ═══ KAD RINGKASAN ═══ --}}
<div class="ps-stats">
    <div class="ps-stat-card">
        <div class="ps-stat-icon violet"><i class="ti ti-users"></i></div>
        <div>
            <div class="ps-stat-value">{{ count($pelajarList) }}</div>
            <div class="ps-stat-label">Jumlah Pelajar Ditaja</div>
        </div>
    </div>
    <div class="ps-stat-card">
        <div class="ps-stat-icon blue"><i class="ti ti-report-analytics"></i></div>
        <div>
            <div class="ps-stat-value">{{ $purataCgpaKeseluruhan > 0 ? number_format($purataCgpaKeseluruhan, 2) : '—' }}</div>
            <div class="ps-stat-label">Purata CGPA Keseluruhan</div>
        </div>
    </div>
    <div class="ps-stat-card">
        <div class="ps-stat-icon green"><i class="ti ti-star"></i></div>
        <div>
            <div class="ps-stat-value">{{ $pelajarAktif['status_prestasi'] ?? '—' }}</div>
            <div class="ps-stat-label">Status Pelajar Dipilih</div>
        </div>
    </div>
</div>

<div class="ps-card">
    {{-- Tab pilih pelajar --}}
    @if(count($pelajarList) > 1)
    <div class="pelajar-tabs">
        @foreach($pelajarList as $p)
        <a href="{{ route('keluarga-portal.prestasi', ['pelajar' => $p['id_pelajar']]) }}"
           class="pelajar-tab {{ ($pelajarAktif['id_pelajar'] ?? null) === $p['id_pelajar'] ? 'active' : '' }}">
            <i class="ti ti-user"></i> {{ $p['nama_pelajar'] }}
        </a>
        @endforeach
    </div>
    @endif

    <div class="ps-card-head">
        <div>
            <div class="ps-card-head-title">{{ $pelajarAktif['nama_pelajar'] ?? '—' }}</div>
            <div class="ps-card-head-sub">
                {{ $pelajarAktif['no_matrik'] ?? '—' }} ·
                {{ $pelajarAktif['program'] ?? '—' }} ·
                Semester {{ $pelajarAktif['semester_kini'] ?? '—' }}
            </div>
        </div>
        @if(!empty($pelajarAktif['trend']))
        <span class="trend-badge {{ $pelajarAktif['trend'] }}">
            @if($pelajarAktif['trend'] === 'naik')
                <i class="ti ti-trending-up"></i> Trend Naik
            @elseif($pelajarAktif['trend'] === 'turun')
                <i class="ti ti-trending-down"></i> Trend Turun
            @else
                <i class="ti ti-minus"></i> Stabil
            @endif
        </span>
        @endif
    </div>

    <div class="table-wrap" style="border:none;border-radius:0;margin-bottom:0">
        <table>
            <thead>
                <tr>
                    <th>Semester</th>
                    <th style="text-align:center">GPA</th>
                    <th style="text-align:center">CGPA</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pelajarAktif['prestasi'] ?? [] as $p)
                @php
                    $g   = floatval($p['gpa'] ?? 0);
                    $clr = $g >= 3.5 ? '#3b6d11' : ($g >= 3.0 ? '#854f0b' : '#a32d2d');
                @endphp
                <tr>
                    <td>{{ $p['semester'] ?? '—' }}</td>
                    <td style="text-align:center;font-weight:600;color:{{ $clr }}">
                        {{ number_format($g, 2) }}
                    </td>
                    <td style="text-align:center;color:var(--text-muted)">
                        {{ !empty($p['cgpa']) ? number_format($p['cgpa'], 2) : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align:center;color:var(--text-muted);padding:30px;font-size:13px">
                        <i class="ti ti-chart-bar" style="font-size:28px;display:block;margin-bottom:8px"></i>
                        Tiada rekod prestasi lagi untuk pelajar ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif

@endsection
