@extends('layouts.app-pelajar')

@section('title', 'Keluarga Angkat')
@section('page-title', 'Keluarga angkat')

@section('content')

@if($keluarga)

    @php
        $status = $keluarga['status_tajaan'] ?? 'Aktif';
        $statusColor = match($status) {
            'Aktif'        => 'green',
            'Hampir Tamat' => 'warn',
            'Tamat'        => 'red',
            default        => 'gray',
        };
        $sejak = !empty($keluarga['created_at']) ? \Carbon\Carbon::parse($keluarga['created_at'])->format('M Y') : null;
    @endphp

    {{-- ═══ KAD PENGENALAN KELUARGA ═══ --}}
    <div class="table-wrap" style="margin-bottom:20px">
        <div style="padding:20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
            <div style="width:56px;height:56px;border-radius:50%;background:var(--primary);
                        display:flex;align-items:center;justify-content:center;
                        font-size:20px;font-weight:700;color:#fff;flex-shrink:0">
                {{ strtoupper(substr($keluarga['nama_keluarga_angkat'] ?? 'K', 0, 2)) }}
            </div>
            <div style="flex:1;min-width:200px">
                <div style="font-size:16px;font-weight:600">{{ $keluarga['nama_keluarga_angkat'] ?? '—' }}</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                    Keluarga angkat anda{{ $sejak ? " sejak {$sejak}" : '' }}
                </div>
            </div>
            <span class="badge {{ $statusColor }}">{{ $status }}</span>
        </div>
    </div>

    {{-- ═══ BUTIRAN ═══ --}}
    <div class="table-wrap">
        <div class="table-header">
            <div class="section-title">
                <i class="ti ti-home-heart"></i> Butiran tajaan
            </div>
        </div>
        <div style="padding:4px 20px 20px">
            <div class="info-row">
                <span class="info-label">Nama keluarga</span>
                <span class="info-val">{{ $keluarga['nama_keluarga_angkat'] ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">No. Telefon</span>
                <span class="info-val">{{ $keluarga['no_telefon'] ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Lokasi / Alamat</span>
                <span class="info-val" style="text-align:right;max-width:60%">{{ $keluarga['alamat'] ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Jumlah tajaan</span>
                <span class="info-val">
                    {{ $jumlahBulanan !== null ? 'RM ' . number_format($jumlahBulanan, 0) . '/bulan' : '—' }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Tempoh tajaan</span>
                <span class="info-val">
                    @if(!empty($keluarga['tarikh_tamat_tajaan']))
                        @php $tamat = \Carbon\Carbon::parse($keluarga['tarikh_tamat_tajaan']); @endphp
                        Tamat {{ $tamat->format('M Y') }}
                    @else
                        —
                    @endif
                </span>
            </div>
        </div>
    </div>

@else

    {{-- ═══ TIADA KELUARGA ANGKAT DITUGASKAN ═══ --}}
    <div class="table-wrap">
        <div class="empty-state" style="padding:48px 24px">
            <i class="ti ti-users" style="font-size:32px;color:var(--text-muted)"></i>
            <p style="margin-top:10px">Anda belum ditugaskan kepada mana-mana keluarga angkat.</p>
            <p style="font-size:12px;color:var(--text-muted)">Hubungi pihak pentadbir Protege Bitara UPSI untuk maklumat lanjut.</p>
        </div>
    </div>

@endif

@endsection
