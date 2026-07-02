@extends('layouts.app-keluarga')

@section('title', 'Dashboard Keluarga Angkat')
@section('page-title', 'Dashboard')

@section('content')

@php
    $nama  = session('nama', 'Keluarga Angkat');
    $email = session('email', '');
@endphp

{{-- ═══ SELAMAT DATANG ═══ --}}
<div style="background:linear-gradient(135deg,var(--primary) 0%,#1a3f73 55%,#c9891a 100%);
            border-radius:var(--radius-lg);padding:24px 28px;
            color:#fff;margin-bottom:20px;display:flex;
            align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div>
        <div style="font-size:11px;opacity:.75;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">
            Selamat Datang, Keluarga Angkat
        </div>
        <div style="font-size:20px;font-weight:700">{{ $keluarga['nama_keluarga_angkat'] ?? $nama }}</div>
        <div style="font-size:12px;opacity:.75;margin-top:2px">
            Status Tajaan:
            <strong>{{ $keluarga['status_tajaan'] ?? '—' }}</strong>
        </div>
    </div>
    <div style="text-align:right">
        <div style="font-size:11px;opacity:.75">Tamat Tajaan</div>
        <div style="font-size:18px;font-weight:700">
            @if(!empty($keluarga['tarikh_tamat_tajaan']))
                {{ \Carbon\Carbon::parse($keluarga['tarikh_tamat_tajaan'])->format('d M Y') }}
            @else
                —
            @endif
        </div>
    </div>
</div>

{{-- ═══ KAD PELAJAR YANG DITANGGUNG ═══ --}}
@if($pelajar)
<div style="background:var(--surface);border:1px solid var(--border);
            border-radius:var(--radius-lg);padding:20px;margin-bottom:20px">
    <div style="font-size:12px;font-weight:600;color:var(--text-muted);
                text-transform:uppercase;letter-spacing:.05em;margin-bottom:14px">
        <i class="ti ti-user-circle" style="font-size:14px"></i>
        Pelajar Anak Angkat Anda
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px">
        <div>
            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">Nama</div>
            <div style="font-size:14px;font-weight:600">{{ $pelajar['nama_pelajar'] ?? '—' }}</div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">No. Matrik</div>
            <div style="font-size:13px">{{ $pelajar['no_matrik'] ?? '—' }}</div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">Program</div>
            <div style="font-size:12px;color:var(--text-2)">{{ $pelajar['program'] ?? '—' }}</div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">Semester</div>
            <span class="badge blue">{{ $pelajar['semester'] ?? '—' }}</span>
        </div>
        <div>
            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">Status Pengajian</div>
            <span class="badge {{ $pelajar['status_pengajian'] === 'Aktif' ? 'green' : 'warn' }}">
                {{ $pelajar['status_pengajian'] ?? '—' }}
            </span>
        </div>
        @php
            $gpas = array_column($prestasi, 'gpa');
            $cgpa = count($gpas) ? round(array_sum($gpas)/count($gpas), 2) : 0;
            $cgpaClr = $cgpa >= 3.5 ? '#3b6d11' : ($cgpa >= 3.0 ? '#854f0b' : '#a32d2d');
        @endphp
        <div>
            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">CGPA</div>
            <div style="font-size:20px;font-weight:700;color:{{ $cgpaClr }}">
                {{ $cgpa > 0 ? number_format($cgpa, 2) : '—' }}
            </div>
        </div>
    </div>
</div>
@else
<div style="background:var(--surface-2);border:1px dashed var(--border);
            border-radius:var(--radius-lg);padding:30px;text-align:center;
            color:var(--text-muted);margin-bottom:20px">
    <i class="ti ti-user-off" style="font-size:36px;display:block;margin-bottom:10px;opacity:.4"></i>
    <p style="font-size:13px">Tiada pelajar ditugaskan kepada anda lagi.</p>
    <p style="font-size:12px;margin-top:4px">Sila hubungi pihak pentadbir untuk maklumat lanjut.</p>
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

    {{-- ═══ PRESTASI PELAJAR ═══ --}}
    <div class="table-wrap">
        <div class="table-header">
            <div class="section-title">
                <i class="ti ti-chart-bar"></i> Prestasi Akademik Pelajar
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
                    $g   = floatval($p['gpa'] ?? 0);
                    $clr = $g >= 3.5 ? '#3b6d11' : ($g >= 3.0 ? '#854f0b' : '#a32d2d');
                    $sts = $g >= 3.5 ? 'Cemerlang' : ($g >= 3.0 ? 'Memuaskan' : 'Perlu Perhatian');
                    $bdg = $g >= 3.5 ? 'green' : ($g >= 3.0 ? 'blue' : 'warn');
                @endphp
                <tr>
                    <td>{{ $p['semester'] }}</td>
                    <td style="text-align:center;font-weight:700;color:{{ $clr }}">
                        {{ number_format($g, 2) }}
                    </td>
                    <td style="text-align:center;color:var(--text-muted)">
                        {{ !empty($p['cgpa']) ? number_format($p['cgpa'], 2) : '—' }}
                    </td>
                    <td><span class="badge {{ $bdg }}" style="font-size:10px">{{ $sts }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;color:var(--text-muted);padding:20px;font-size:13px">
                        Tiada rekod prestasi lagi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ═══ SEJARAH SUMBANGAN ═══ --}}
    <div class="table-wrap">
        <div class="table-header">
            <div class="section-title">
                <i class="ti ti-cash"></i> Sejarah Sumbangan Anda
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

@endsection
