@extends('layouts.app-pelajar')

@section('title', 'Akademik')
@section('page-title', 'Akademik')

@section('content')

@php
    $g = fn($v) => $v === null ? null : floatval($v);
    $cgpaClr = function ($v) {
        if ($v === null) return 'var(--text-1)';
        if ($v >= 3.50) return '#3b6d11';
        if ($v >= 3.00) return '#854f0b';
        return '#a32d2d';
    };
@endphp

{{-- ═══ RINGKASAN ═══ --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:20px">

    <div class="stat-card">
        <div class="stat-label">Semester Semasa</div>
        <div class="stat-value">{{ $pelajar['semester'] ?? '—' }}</div>
        <div class="stat-sub">{{ $pelajar['status_pengajian'] ?? 'Aktif' }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">GPA Terkini</div>
        <div class="stat-value" style="color:{{ $cgpaClr($g($gpaTerkini)) }}">
            {{ $gpaTerkini !== null ? number_format($gpaTerkini, 2) : '—' }}
        </div>
        <div class="stat-sub">Semester terkini direkodkan</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">CGPA Keseluruhan</div>
        <div class="stat-value" style="color:{{ $cgpaClr($g($cgpaKeseluruhan)) }}">
            {{ $cgpaKeseluruhan !== null ? number_format($cgpaKeseluruhan, 2) : '—' }}
        </div>
        <div class="stat-sub">{{ count($prestasi) }} semester direkodkan</div>
    </div>

</div>

{{-- ═══ KEPUTUSAN SEMESTER ═══ --}}
<div class="table-wrap">
    <div class="table-header">
        <div class="section-title">
            <i class="ti ti-clipboard-list"></i> Keputusan semester
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Semester</th>
                <th style="text-align:center">GPA</th>
                <th style="text-align:center">CGPA</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($prestasi as $p)
                @php
                    $gpaVal = floatval($p['gpa'] ?? 0);
                    $status = $gpaVal >= 3.50 ? 'Cemerlang' : ($gpaVal >= 2.00 ? 'Lulus' : 'Perlu Perhatian');
                    $badge  = $gpaVal >= 3.50 ? 'green' : ($gpaVal >= 2.00 ? 'blue' : 'red');
                @endphp
                <tr>
                    <td style="font-weight:500">{{ $p['semester'] ?? '—' }}</td>
                    <td style="text-align:center;font-weight:600;color:{{ $cgpaClr($gpaVal) }}">
                        {{ number_format($gpaVal, 2) }}
                    </td>
                    <td style="text-align:center;color:var(--text-muted)">
                        {{ !empty($p['cgpa']) ? number_format($p['cgpa'], 2) : '—' }}
                    </td>
                    <td><span class="badge {{ $badge }}">{{ $status }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center;color:var(--text-muted);padding:24px;font-size:13px">
                        Tiada rekod keputusan semester lagi.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
