@extends('layouts.app-pelajar')

@section('title', 'Akademik Saya')
@section('page-title', 'Akademik')

@section('content')

@php
    $cgpaClr = $cgpa >= 3.5 ? '#3b6d11' : ($cgpa >= 3.0 ? '#854f0b' : '#a32d2d');
@endphp



{{-- ═══ RINGKASAN ═══ --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:20px">
    <div class="stat-card c-navy">
        <div class="stat-icon"><i class="ti ti-chart-bar"></i></div>
        <div class="stat-label">CGPA Purata</div>
        <div class="stat-value" style="color:{{ $cgpaClr }}">
            {{ $cgpa > 0 ? number_format($cgpa, 2) : '—' }}
        </div>
        <div class="stat-sub">{{ count($prestasi) }} semester direkodkan</div>
    </div>
    <div class="stat-card c-teal">
        <div class="stat-icon"><i class="ti ti-calendar"></i></div>
        <div class="stat-label">Semester Semasa</div>
        <div class="stat-value">{{ $pelajar['semester'] ?? '—' }}</div>
        <div class="stat-sub">{{ $pelajar['status_pengajian'] ?? 'Aktif' }}</div>
    </div>
    <div class="stat-card c-purple">
        <div class="stat-icon"><i class="ti ti-school"></i></div>
        <div class="stat-label">Program</div>
        <div class="stat-value" style="font-size:14px;line-height:1.3">
            {{ $pelajar['program'] ?? '—' }}
        </div>
        <div class="stat-sub">{{ $pelajar['fakulti'] ?? '—' }}</div>
    </div>
</div>

{{-- ═══ SEJARAH PRESTASI PENUH ═══ --}}
<div class="table-wrap">
    <div class="table-header" style="background:linear-gradient(90deg,#eaf0fb 0%,#dfe9fb 100%)">
        <div class="section-title">
            <i class="ti ti-chart-bar" style="color:#001c4b"></i> Sejarah Prestasi Akademik
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Semester</th>
                <th style="text-align:center">GPA</th>
                <th style="text-align:center">CGPA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($prestasi as $p)
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
                    Tiada rekod prestasi lagi.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
