@extends('layouts.app-keluarga')

@section('title', 'Pelajar Saya')
@section('page-title', 'Pelajar Saya')

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

.initial-circle {
    width: 38px; height: 38px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: #fff; flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(0,0,0,.12);
}
.pelajar-row-cell { display: flex; align-items: center; gap: 12px; }
.gpa-wrap { display: flex; align-items: center; gap: 7px; }
.gpa-bar-bg { width: 56px; height: 6px; background: var(--border); border-radius: 4px; overflow: hidden; flex-shrink: 0; }
.gpa-bar-fill { height: 100%; border-radius: 4px; transition: width .3s; }
.gpa-bar-fill.high { background: #48bb78; }
.gpa-bar-fill.mid  { background: #ed8936; }
.gpa-bar-fill.low  { background: #fc8181; }
.gpa-num { font-size: 12.5px; font-weight: 600; font-variant-numeric: tabular-nums; }

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
        <div class="ps-hero-title">Pelajar Saya</div>
        <div class="ps-hero-sub">Senarai pelajar di bawah jagaan {{ $nama }}</div>
    </div>
    <div class="ps-hero-icon"><i class="ti ti-users"></i></div>
</div>

{{-- ═══ KAD RINGKASAN ═══ --}}
<div class="ps-stats">
    <div class="ps-stat-card">
        <div class="ps-stat-icon violet"><i class="ti ti-user-heart"></i></div>
        <div>
            <div class="ps-stat-value">{{ $jumlahPelajar }}</div>
            <div class="ps-stat-label">Jumlah Pelajar Ditaja</div>
        </div>
    </div>
    <div class="ps-stat-card">
        <div class="ps-stat-icon green"><i class="ti ti-circle-check"></i></div>
        <div>
            <div class="ps-stat-value">{{ $jumlahAktif }}</div>
            <div class="ps-stat-label">Tajaan Aktif</div>
        </div>
    </div>
    <div class="ps-stat-card">
        <div class="ps-stat-icon blue"><i class="ti ti-chart-bar"></i></div>
        <div>
            <div class="ps-stat-value">{{ $purataGpa > 0 ? number_format($purataGpa, 2) : '—' }}</div>
            <div class="ps-stat-label">Purata GPA</div>
        </div>
    </div>
</div>

{{-- ═══ SENARAI PELAJAR ═══ --}}
<div class="ps-card">
    <div class="ps-card-head">
        <div>
            <div class="ps-card-head-title">Senarai Pelajar</div>
            <div class="ps-card-head-sub">Pelajar di bawah jagaan anda</div>
        </div>
        <form method="GET" action="{{ route('keluarga-portal.pelajar') }}">
            <div class="search-box" style="width:240px">
                <i class="ti ti-search"></i>
                <input
                    name="cari"
                    placeholder="Cari pelajar..."
                    value="{{ $cari }}"
                    onchange="this.form.submit()"
                    style="border:none;background:transparent;outline:none;font-size:13px;width:100%;color:var(--text)"
                >
            </div>
        </form>
    </div>

    @if(count($pelajarList) === 0)
        <div style="padding:18px">
            <div class="ps-empty">
                <i class="ti ti-user-off"></i>
                <p style="font-size:13.5px;font-weight:500;color:var(--text)">
                    @if($cari !== '')
                        Tiada pelajar sepadan dengan carian "{{ $cari }}".
                    @else
                        Tiada pelajar ditugaskan kepada anda lagi.
                    @endif
                </p>
                <p style="font-size:12px;margin-top:5px">Sila hubungi pihak pentadbir untuk maklumat lanjut.</p>
            </div>
        </div>
    @else
    <div class="table-wrap" style="border:none;border-radius:0;margin-bottom:0">
        <table>
            <thead>
                <tr>
                    <th width="36">#</th>
                    <th>Pelajar</th>
                    <th>Program</th>
                    <th style="text-align:center">Sem</th>
                    <th>GPA</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pelajarList as $i => $p)
                @php
                    $gpa      = floatval($p['latest_gpa'] ?? 0);
                    $gpaClass = $gpa >= 3.50 ? 'high' : ($gpa >= 3.00 ? 'mid' : ($gpa > 0 ? 'low' : ''));
                    $pct      = $gpa > 0 ? min(($gpa / 4) * 100, 100) : 0;

                    $namaP   = $p['nama_pelajar'] ?? '—';
                    $initial = strtoupper(collect(explode(' ', $namaP))->map(fn($w) => $w[0] ?? '')->take(2)->implode(''));
                    $hue     = crc32($namaP) % 360;

                    $status = $p['status_tajaan'] ?? 'Aktif';
                    $tamat  = !empty($p['tarikh_tamat']) ? \Carbon\Carbon::parse($p['tarikh_tamat']) : null;
                    $statusLabel = $status;
                    $statusBadge = 'green';
                    if ($tamat) {
                        $bulanLagi = now()->diffInMonths($tamat, false);
                        if ($bulanLagi < 0)      { $statusLabel = 'Tajaan Tamat';           $statusBadge = 'red';  }
                        elseif ($bulanLagi <= 2) { $statusLabel = 'Tajaan Tamat Tidak Lama'; $statusBadge = 'warn'; }
                        else                     { $statusLabel = 'Aktif';                   $statusBadge = 'green'; }
                    }
                @endphp
                <tr>
                    <td style="color:var(--text-muted);font-size:12px">{{ $i + 1 }}</td>
                    <td>
                        <div class="pelajar-row-cell">
                            <div class="initial-circle" style="background:hsl({{ $hue }},55%,45%)">{{ $initial }}</div>
                            <div>
                                <div class="student-name">{{ $namaP }}</div>
                                <div class="student-id">{{ $p['no_matrik'] ?? '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td><div class="prog-text" style="font-size:13px;color:var(--text-2)">{{ $p['program'] ?? '—' }}</div></td>
                    <td style="text-align:center">
                        @if(!empty($p['semester']))
                            <span class="badge blue">{{ $p['semester'] }}</span>
                        @else
                            <span style="color:var(--text-muted)">—</span>
                        @endif
                    </td>
                    <td>
                        @if($gpa > 0)
                        <div class="gpa-wrap">
                            <div class="gpa-bar-bg"><div class="gpa-bar-fill {{ $gpaClass }}" style="width:{{ $pct }}%"></div></div>
                            <span class="gpa-num">{{ number_format($gpa, 2) }}</span>
                        </div>
                        @else
                            <span style="color:var(--text-muted);font-size:12px">—</span>
                        @endif
                    </td>
                    <td><span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
