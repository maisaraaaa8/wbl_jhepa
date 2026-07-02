@extends('layouts.app-pelajar')

@section('title', 'Sumbangan Saya')
@section('page-title', 'Sumbangan')

@section('content')

<div class="stats-grid" style="margin-bottom:20px">
    <div class="stat-card">
        <div class="stat-label">Jumlah Keseluruhan</div>
        <div class="stat-value">RM{{ number_format($jumlahKeseluruhan, 2) }}</div>
        <div class="stat-sub">{{ count($sumbangan) }} rekod sumbangan</div>
    </div>
</div>

<div class="table-wrap">
    <div class="table-header">
        <div class="section-title">
            <i class="ti ti-cash"></i> Sejarah Sumbangan
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Tarikh</th>
                <th style="text-align:right">Jumlah</th>
                <th>Keterangan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sumbangan as $s)
            <tr>
                <td style="white-space:nowrap">
                    {{ !empty($s['tarikh_terima']) ? \Carbon\Carbon::parse($s['tarikh_terima'])->format('d M Y') : '—' }}
                </td>
                <td style="text-align:right;font-weight:600;color:#3b6d11">
                    RM{{ number_format($s['jumlah'] ?? 0, 2) }}
                </td>
                <td style="color:var(--text-muted)">{{ $s['keterangan'] ?? '—' }}</td>
                <td>
                    <span class="badge {{ ($s['status'] ?? '') === 'Diterima' ? 'green' : 'warn' }}">
                        {{ $s['status'] ?? '—' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center;color:var(--text-muted);padding:30px;font-size:13px">
                    <i class="ti ti-cash" style="font-size:28px;display:block;margin-bottom:8px"></i>
                    Tiada rekod sumbangan lagi.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
