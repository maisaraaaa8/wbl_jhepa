@extends('layouts.app-pelajar')

@section('title', 'Keluarga Angkat Saya')
@section('page-title', 'Keluarga Angkat')

@section('content')

@if($keluarga)
<div class="table-wrap">
    <div class="table-header">
        <div class="section-title">
            <i class="ti ti-home-heart"></i> Maklumat Keluarga Angkat
        </div>
        <span class="badge {{ ($keluarga['status_tajaan'] ?? '') === 'Aktif' ? 'green' : 'warn' }}">
            {{ $keluarga['status_tajaan'] ?? '—' }}
        </span>
    </div>
    @if($keluarga['hide_identity'] ?? false)
    <div style="padding:20px;">
        <div style="display:flex;align-items:center;gap:12px;background:var(--bg);border-radius:10px;padding:16px;margin-bottom:18px;">
            <i class="ti ti-user-question" style="font-size:22px;color:var(--text-muted);"></i>
            <div>
                <div style="font-size:14px;font-weight:600;">Penderma Tanpa Nama</div>
                <div style="font-size:12.5px;color:var(--text-muted);">Keluarga angkat anda memilih untuk merahsiakan identiti mereka.</div>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px">
            <div>
                <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">Tarikh Tamat Tajaan</div>
                <div style="font-size:14px">
                    {{ !empty($keluarga['tarikh_tamat_tajaan']) ? \Carbon\Carbon::parse($keluarga['tarikh_tamat_tajaan'])->format('d M Y') : '—' }}
                </div>
            </div>
        </div>
    </div>
    @else
    <div style="padding:20px;display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px">
        <div>
            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">Nama</div>
            <div style="font-size:14px;font-weight:600">{{ $keluarga['nama_keluarga_angkat'] ?? '—' }}</div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">No. Telefon</div>
            <div style="font-size:14px">{{ $keluarga['no_telefon'] ?? '—' }}</div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">Jabatan</div>
            <div style="font-size:14px">{{ $keluarga['jabatan'] ?? '—' }}</div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">Alamat</div>
            <div style="font-size:14px;color:var(--text-2)">{{ $keluarga['alamat'] ?? '—' }}</div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">Tarikh Tamat Tajaan</div>
            <div style="font-size:14px">
                {{ !empty($keluarga['tarikh_tamat_tajaan']) ? \Carbon\Carbon::parse($keluarga['tarikh_tamat_tajaan'])->format('d M Y') : '—' }}
            </div>
        </div>
    </div>
    @endif
</div>
@else
<div class="table-wrap">
    <div style="text-align:center;padding:60px 20px;color:var(--text-muted)">
        <i class="ti ti-home-heart" style="font-size:40px;display:block;margin-bottom:12px"></i>
        <div style="font-size:14px;font-weight:600;margin-bottom:4px">Belum Ditugaskan Keluarga Angkat</div>
        <div style="font-size:13px">Anda belum dipasangkan dengan mana-mana keluarga angkat setakat ini.</div>
    </div>
</div>
@endif

@endsection
