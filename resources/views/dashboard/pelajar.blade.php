@extends('layouts.app-pelajar')

@section('title', 'Dashboard Saya')
@section('page-title', 'Dashboard')

@section('content')

@php
    $nama  = session('nama', 'Pelajar');
    $email = session('email', '');
@endphp

{{-- ═══ SELAMAT DATANG ═══ --}}
<div style="background:linear-gradient(135deg,#1e3a5f 0%,#2d5a8e 100%);
            border-radius:var(--radius-lg);padding:24px 28px;
            color:#fff;margin-bottom:20px;display:flex;
            align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div>
        <div style="font-size:11px;opacity:.75;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">
            Selamat Datang
        </div>
        <div style="font-size:20px;font-weight:700">{{ $nama }}</div>
        <div style="font-size:12px;opacity:.75;margin-top:2px">
            {{ $pelajar['program'] ?? 'Pelajar Protege Bitara UPSI' }}
        </div>
    </div>
    <div style="text-align:right">
        <div style="font-size:11px;opacity:.75">No. Matrik</div>
        <div style="font-size:18px;font-weight:700">{{ $pelajar['no_matrik'] ?? '—' }}</div>
        <div style="margin-top:4px">
            <span style="background:rgba(255,255,255,.2);padding:3px 10px;
                         border-radius:20px;font-size:11px;font-weight:600">
                {{ $pelajar['semester'] ?? '—' }}
            </span>
        </div>
    </div>
</div>

{{-- ═══ KAD RINGKASAN ═══ --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:20px">

    {{-- CGPA --}}
    @php
        $gpas = array_column($prestasi, 'gpa');
        $cgpa = count($gpas) ? round(array_sum($gpas)/count($gpas), 2) : 0;
        $cgpaClr = $cgpa >= 3.5 ? '#3b6d11' : ($cgpa >= 3.0 ? '#854f0b' : '#a32d2d');
    @endphp
    <div class="stat-card">
        <div class="stat-label">CGPA Semasa</div>
        <div class="stat-value" style="color:{{ $cgpaClr }}">
            {{ $cgpa > 0 ? number_format($cgpa, 2) : '—' }}
        </div>
        <div class="stat-sub">{{ count($prestasi) }} semester direkodkan</div>
    </div>

    {{-- Semester --}}
    <div class="stat-card">
        <div class="stat-label">Semester</div>
        <div class="stat-value">{{ $pelajar['semester'] ?? '—' }}</div>
        <div class="stat-sub">{{ $pelajar['status_pengajian'] ?? 'Aktif' }}</div>
    </div>

    {{-- Keluarga Angkat --}}
    <div class="stat-card">
        <div class="stat-label">Keluarga Angkat</div>
        <div class="stat-value" style="font-size:15px;line-height:1.3">
            {{ $keluarga['nama_keluarga_angkat'] ?? '—' }}
        </div>
        <div class="stat-sub">{{ $keluarga['status_tajaan'] ?? 'Tiada rekod' }}</div>
    </div>

    {{-- Tajaan Tamat --}}
    <div class="stat-card {{ !empty($keluarga['tarikh_tamat_tajaan']) && \Carbon\Carbon::parse($keluarga['tarikh_tamat_tajaan'])->diffInMonths(now(), false) > -2 ? 'warn' : '' }}">
        <div class="stat-label">Tamat Tajaan</div>
        <div class="stat-value" style="font-size:15px">
            @if(!empty($keluarga['tarikh_tamat_tajaan']))
                {{ \Carbon\Carbon::parse($keluarga['tarikh_tamat_tajaan'])->format('M Y') }}
            @elseif(!empty($pelajar['tarikh_tamat_tajaan']))
                {{ \Carbon\Carbon::parse($pelajar['tarikh_tamat_tajaan'])->format('M Y') }}
            @else
                —
            @endif
        </div>
        <div class="stat-sub">Tarikh tamat program</div>
    </div>

</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">

    {{-- ═══ PRESTASI AKADEMIK ═══ --}}
    <div class="table-wrap">
        <div class="table-header">
            <div class="section-title">
                <i class="ti ti-chart-bar"></i> Prestasi Akademik
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
                @php $g = floatval($p['gpa'] ?? 0); $clr = $g >= 3.5 ? '#3b6d11' : ($g >= 3.0 ? '#854f0b' : '#a32d2d'); @endphp
                <tr>
                    <td>{{ $p['semester'] }}</td>
                    <td style="text-align:center;font-weight:600;color:{{ $clr }}">
                        {{ number_format($g, 2) }}
                    </td>
                    <td style="text-align:center;color:var(--text-muted)">
                        {{ !empty($p['cgpa']) ? number_format($p['cgpa'], 2) : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align:center;color:var(--text-muted);padding:20px;font-size:13px">
                        Tiada rekod prestasi lagi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ═══ SUMBANGAN TERKINI ═══ --}}
    <div class="table-wrap">
        <div class="table-header">
            <div class="section-title">
                <i class="ti ti-cash"></i> Sumbangan Terkini
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Tarikh</th>
                    <th style="text-align:right">Jumlah</th>
                    <th>Nota</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sumbangan as $s)
                <tr>
                    <td style="font-size:12px;white-space:nowrap">
                        {{ !empty($s['tarikh_terima']) ? \Carbon\Carbon::parse($s['tarikh_terima'])->format('d M Y') : '—' }}
                    </td>
                    <td style="text-align:right;font-weight:600;color:#3b6d11">
                        RM{{ number_format($s['jumlah'] ?? 0, 2) }}
                    </td>
                    <td style="font-size:12px;color:var(--text-muted)">
                        {{ $s['keterangan'] ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align:center;color:var(--text-muted);padding:20px;font-size:13px">
                        Tiada rekod sumbangan lagi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- ═══ MAKLUMAT KELUARGA ANGKAT ═══ --}}
@if($keluarga)
<div class="table-wrap">
    <div class="table-header">
        <div class="section-title"><i class="ti ti-home-heart"></i> Maklumat Keluarga Angkat</div>
    </div>
    <div style="padding:16px 20px;display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px">
        <div>
            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">Nama</div>
            <div style="font-size:13px;font-weight:500">{{ $keluarga['nama_keluarga_angkat'] ?? '—' }}</div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">No. Telefon</div>
            <div style="font-size:13px">{{ $keluarga['no_telefon'] ?? '—' }}</div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">Status Tajaan</div>
            <div>
                <span class="badge {{ $keluarga['status_tajaan'] === 'Aktif' ? 'green' : 'warn' }}">
                    {{ $keluarga['status_tajaan'] ?? '—' }}
                </span>
            </div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">Alamat</div>
            <div style="font-size:13px;color:var(--text-2)">{{ $keluarga['alamat'] ?? '—' }}</div>
        </div>
    </div>
</div>
@endif

@endsection
