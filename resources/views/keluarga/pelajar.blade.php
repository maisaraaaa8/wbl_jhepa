@extends('layouts.app-keluarga')

@section('title', 'Pelajar Saya')
@section('page-title', 'Pelajar Saya')

@push('styles')
<style>
.initial-circle {
    width: 34px; height: 34px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.pelajar-row-cell { display: flex; align-items: center; gap: 10px; }
.gpa-wrap { display: flex; align-items: center; gap: 6px; }
.gpa-bar-bg { width: 50px; height: 5px; background: var(--border); border-radius: 4px; overflow: hidden; flex-shrink: 0; }
.gpa-bar-fill { height: 100%; border-radius: 4px; }
.gpa-bar-fill.high { background: #48bb78; }
.gpa-bar-fill.mid  { background: #ed8936; }
.gpa-bar-fill.low  { background: #fc8181; }
.gpa-num { font-size: 12px; font-variant-numeric: tabular-nums; }
.empty-state {
    background: var(--surface-2); border: 1px dashed var(--border);
    border-radius: var(--radius-lg); padding: 40px 20px; text-align: center;
    color: var(--text-muted);
}
</style>
@endpush

@section('content')

<div class="filter-row" style="margin-bottom:16px">
    <div style="font-size:13px;color:var(--text-muted)">
        Senarai pelajar di bawah jagaan anda
    </div>
    <form method="GET" action="{{ route('keluarga-saya.pelajar') }}">
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
    <div class="empty-state">
        <i class="ti ti-user-off" style="font-size:36px;display:block;margin-bottom:10px;opacity:.4"></i>
        <p style="font-size:13px">Tiada pelajar ditugaskan kepada anda lagi.</p>
        <p style="font-size:12px;margin-top:4px">Sila hubungi pihak pentadbir untuk maklumat lanjut.</p>
    </div>
@else
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th width="36">#</th>
                <th>Pelajar</th>
                <th>Program</th>
                <th style="text-align:center">Sem</th>
                <th style="text-align:center">GPA</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pelajarList as $i => $p)
            @php
                $gpa      = floatval($p['latest_gpa'] ?? 0);
                $gpaClass = $gpa >= 3.50 ? 'high' : ($gpa >= 3.00 ? 'mid' : ($gpa > 0 ? 'low' : ''));
                $pct      = $gpa > 0 ? min(($gpa / 4) * 100, 100) : 0;

                $nama    = $p['nama_pelajar'] ?? '—';
                $initial = strtoupper(collect(explode(' ', $nama))->map(fn($w) => $w[0] ?? '')->take(2)->implode(''));
                $hue     = crc32($nama) % 360;

                $status = $p['status_tajaan'] ?? 'Aktif';
                $tamat  = !empty($p['tarikh_tamat']) ? \Carbon\Carbon::parse($p['tarikh_tamat']) : null;
                $statusLabel = $status;
                $statusBadge = 'green';
                if ($tamat) {
                    $bulanLagi = now()->diffInMonths($tamat, false);
                    if ($bulanLagi < 0) { $statusLabel = 'Tajaan Tamat'; $statusBadge = 'red'; }
                    elseif ($bulanLagi <= 2) { $statusLabel = 'Tajaan Tamat Tidak Lama'; $statusBadge = 'warn'; }
                    else { $statusLabel = 'Aktif'; $statusBadge = 'green'; }
                }
            @endphp
            <tr>
                <td style="color:var(--text-muted);font-size:12px">{{ $i + 1 }}</td>
                <td>
                    <div class="pelajar-row-cell">
                        <div class="initial-circle" style="background:hsl({{ $hue }},55%,45%)">{{ $initial }}</div>
                        <div>
                            <div class="student-name">{{ $nama }}</div>
                            <div class="student-id">{{ $p['no_matrik'] ?? '—' }}</div>
                        </div>
                    </div>
                </td>
                <td><div class="prog-text">{{ $p['program'] ?? '—' }}</div></td>
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
                <td><span class="badge {{ $statusBadge }}" style="font-size:10px">{{ $statusLabel }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection
