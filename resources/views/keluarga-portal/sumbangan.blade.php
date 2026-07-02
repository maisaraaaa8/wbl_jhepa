@extends('layouts.app-keluarga')

@section('title', 'Sumbangan')
@section('page-title', 'Sumbangan')

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
        <div class="ps-hero-title">Sumbangan</div>
        <div class="ps-hero-sub">Ringkasan &amp; sejarah sumbangan yang anda hantar, {{ $nama }}</div>
    </div>
    <div class="ps-hero-icon"><i class="ti ti-cash"></i></div>
</div>

{{-- ═══ KAD RINGKASAN ═══ --}}
<div class="ps-stats">
    <div class="ps-stat-card">
        <div class="ps-stat-icon violet"><i class="ti ti-calendar-dollar"></i></div>
        <div>
            <div class="ps-stat-value">RM {{ number_format($jumlahBulanIni, 0) }}</div>
            <div class="ps-stat-label">Sumbangan Bulan Ini</div>
        </div>
    </div>
    <div class="ps-stat-card">
        <div class="ps-stat-icon green"><i class="ti ti-user-heart"></i></div>
        <div>
            <div class="ps-stat-value">{{ $bilPelajarBulan }}</div>
            <div class="ps-stat-label">Pelajar Disumbang Bulan Ini</div>
        </div>
    </div>
    <div class="ps-stat-card">
        <div class="ps-stat-icon blue"><i class="ti ti-report-money"></i></div>
        <div>
            <div class="ps-stat-value">RM {{ number_format($jumlahTahunIni, 0) }}</div>
            <div class="ps-stat-label">Jumlah Tahun Ini</div>
        </div>
    </div>
</div>

{{-- ═══ SEJARAH SUMBANGAN ═══ --}}
<div class="ps-card">
    <div class="ps-card-head">
        <div>
            <div class="ps-card-head-title">Sejarah Sumbangan</div>
            <div class="ps-card-head-sub">Semua rekod sumbangan untuk pelajar di bawah jagaan anda</div>
        </div>
    </div>

    @if(count($sumbanganList) === 0)
        <div style="padding:18px">
            <div class="ps-empty">
                <i class="ti ti-cash-off"></i>
                <p style="font-size:13.5px;font-weight:500;color:var(--text)">Tiada rekod sumbangan lagi.</p>
                <p style="font-size:12px;margin-top:5px">Rekod akan dipaparkan di sini sebaik sumbangan didaftarkan oleh pihak pentadbir.</p>
            </div>
        </div>
    @else
    <div class="table-wrap" style="border:none;border-radius:0;margin-bottom:0">
        <table>
            <thead>
                <tr>
                    <th width="36">#</th>
                    <th>Tarikh</th>
                    <th>Pelajar</th>
                    <th style="text-align:right">Jumlah (RM)</th>
                    <th>Nota</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sumbanganList as $i => $s)
                @php
                    $status = $s['status'] ?? 'Diterima';
                    $badge  = match(strtolower($status)) {
                        'diterima'   => 'green',
                        'tertunggak' => 'warn',
                        default      => 'gray',
                    };
                    $label = strtolower($status) === 'diterima' ? 'Diterima' : $status;
                @endphp
                <tr>
                    <td style="color:var(--text-muted);font-size:12px">{{ $i + 1 }}</td>
                    <td style="white-space:nowrap">
                        {{ !empty($s['tarikh_terima']) ? \Carbon\Carbon::parse($s['tarikh_terima'])->format('d M Y') : '—' }}
                    </td>
                    <td>{{ $s['nama_pelajar'] ?? '—' }}</td>
                    <td style="text-align:right;font-weight:600;color:#3b6d11">
                        {{ number_format($s['jumlah'] ?? 0, 2) }}
                    </td>
                    <td style="color:var(--text-muted)">{{ $s['keterangan'] ?? '—' }}</td>
                    <td><span class="badge {{ $badge }}">{{ $label }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
