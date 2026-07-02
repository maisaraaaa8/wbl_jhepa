@extends('layouts.app')
@section('title','Notifikasi')
@section('page-title','Notifikasi')

@push('styles')
<style>
/* ===== STATS ===== */
.notif-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.ns-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1rem 1.1rem;
    display: flex; align-items: center; gap: .85rem;
    transition: box-shadow .2s;
}
.ns-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); }
.ns-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}
.ns-icon.red    { background: #fee2e2; color: #dc2626; }
.ns-icon.amber  { background: #fef3c7; color: #d97706; }
.ns-icon.blue   { background: #eff6ff; color: #2563eb; }
.ns-icon.green  { background: #dcfce7; color: #16a34a; }
.ns-label { font-size: .7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.ns-value { font-size: 1.45rem; font-weight: 700; color: var(--text); line-height: 1.1; }

/* ===== SECTION HEADER ===== */
.section-head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: .85rem;
}
.section-head-left { display: flex; align-items: center; gap: .5rem; }
.section-head h3 { margin: 0; font-size: .95rem; font-weight: 700; }
.section-count {
    background: var(--bg); border: 1px solid var(--border);
    border-radius: 999px; padding: .15rem .55rem;
    font-size: .72rem; font-weight: 600; color: var(--text-muted);
}
.section-count.red    { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
.section-count.amber  { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.section-count.blue   { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }

/* ===== NOTIF ITEMS ===== */
.notif-group { margin-bottom: 1.5rem; }

.notif-list { display: flex; flex-direction: column; gap: .65rem; }

.notif-item {
    display: flex; align-items: flex-start; gap: 1rem;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1rem 1.2rem;
    transition: all .2s;
    position: relative;
    overflow: hidden;
}
.notif-item:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); transform: translateY(-1px); }

.notif-item::before {
    content: '';
    position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
    border-radius: 0;
}
.notif-item.danger::before  { background: #dc2626; }
.notif-item.warning::before { background: #d97706; }
.notif-item.success::before { background: #16a34a; }
.notif-item.info::before    { background: #2563eb; }

.notif-avatar {
    width: 42px; height: 42px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem; flex-shrink: 0;
}
.notif-avatar.danger  { background: #fee2e2; color: #dc2626; }
.notif-avatar.warning { background: #fef3c7; color: #d97706; }
.notif-avatar.success { background: #dcfce7; color: #16a34a; }
.notif-avatar.info    { background: #eff6ff; color: #2563eb; }

.notif-content { flex: 1; min-width: 0; }
.notif-title   { font-weight: 700; font-size: .9rem; color: var(--text); margin-bottom: .25rem; }
.notif-body    { font-size: .82rem; color: var(--text-muted); line-height: 1.55; }
.notif-meta    { display: flex; align-items: center; gap: .75rem; margin-top: .45rem; flex-wrap: wrap; }
.notif-time    { font-size: .72rem; color: var(--text-muted); display: flex; align-items: center; gap: .25rem; }
.notif-urgency {
    font-size: .7rem; font-weight: 700; padding: .15rem .5rem;
    border-radius: 999px; display: inline-flex; align-items: center; gap: .2rem;
}
.notif-urgency.red   { background: #fee2e2; color: #b91c1c; }
.notif-urgency.amber { background: #fef3c7; color: #92400e; }

.notif-actions { display: flex; gap: .4rem; align-items: center; flex-shrink: 0; }
.btn-sm {
    padding: .35rem .75rem; border-radius: 8px;
    border: 1px solid var(--border); background: var(--surface);
    cursor: pointer; font-size: .78rem; font-weight: 500;
    color: var(--text); text-decoration: none;
    display: inline-flex; align-items: center; gap: .3rem;
    transition: all .15s;
}
.btn-sm:hover { background: var(--bg); border-color: var(--primary); color: var(--primary); }
.btn-sm.primary { background: var(--primary); color: #fff; border-color: var(--primary); }
.btn-sm.primary:hover { opacity: .9; }

/* ===== EMPTY ===== */
.empty-notif {
    text-align: center;
    padding: 3.5rem 1rem;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
}
.empty-notif i { font-size: 3rem; color: #16a34a; display: block; margin-bottom: .75rem; }
.empty-notif h3 { font-size: 1rem; font-weight: 600; margin-bottom: .35rem; }
.empty-notif p  { font-size: .875rem; color: var(--text-muted); }

/* ===== ALERT ===== */
.alert { padding: .8rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: .875rem; display: flex; align-items: center; gap: .5rem; }
.alert.success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }

/* ===== PAGE DESC ===== */
.page-desc { color: var(--text-muted); font-size: .875rem; margin-top: .25rem; margin-bottom: 1.25rem; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert success"><i class="ti ti-circle-check"></i> {{ session('success') }}</div>
@endif

<h2 style="margin:0;font-size:1.1rem;font-weight:700;">Notifikasi</h2>
<p class="page-desc">Peringatan dan makluman sistem Protege Bitara UPSI</p>

{{-- ===== STATS CARDS ===== --}}
<div class="notif-stats">
    <div class="ns-card">
        <div class="ns-icon red"><i class="ti ti-bell-ringing"></i></div>
        <div>
            <div class="ns-label">Jumlah Notifikasi</div>
            <div class="ns-value">{{ $stats['jumlah_notif'] ?? 0 }}</div>
        </div>
    </div>
    <div class="ns-card">
        <div class="ns-icon amber"><i class="ti ti-clock-exclamation"></i></div>
        <div>
            <div class="ns-label">Hampir Tamat</div>
            <div class="ns-value">{{ $stats['hampir_tamat'] ?? 0 }}</div>
        </div>
    </div>
    <div class="ns-card">
        <div class="ns-icon red"><i class="ti ti-cash-off"></i></div>
        <div>
            <div class="ns-label">Sumbangan Tunggak</div>
            <div class="ns-value">{{ $stats['tertunggak'] ?? 0 }}</div>
        </div>
    </div>
    <div class="ns-card">
        <div class="ns-icon blue"><i class="ti ti-chart-bar-off"></i></div>
        <div>
            <div class="ns-label">GPA Rendah</div>
            <div class="ns-value">{{ $stats['gpa_rendah'] ?? 0 }}</div>
        </div>
    </div>
</div>

{{-- ===== TAJAAN HAMPIR TAMAT ===== --}}
@if(!empty($hampirTamat))
<div class="notif-group">
    <div class="section-head">
        <div class="section-head-left">
            <i class="ti ti-clock-exclamation" style="color:#d97706;font-size:1.1rem;"></i>
            <h3>Tajaan Hampir Tamat</h3>
            <span class="section-count amber">{{ count($hampirTamat) }} peringatan</span>
        </div>
        <a href="{{ route('keluarga.index') }}" class="btn-sm">
            <i class="ti ti-arrow-right"></i> Uruskan Tajaan
        </a>
    </div>
    <div class="notif-list">
        @foreach($hampirTamat as $t)
        @php
            $jenis   = $t['teruk'] ? 'danger' : 'warning';
            $hari    = $t['hari_berbaki'];
        @endphp
        <div class="notif-item {{ $jenis }}">
            <div class="notif-avatar {{ $jenis }}">
                <i class="ti ti-{{ $t['teruk'] ? 'alert-triangle' : 'clock' }}"></i>
            </div>
            <div class="notif-content">
                <div class="notif-title">Tajaan hampir tamat — {{ $t['pelajar_nama'] }}</div>
                <div class="notif-body">
                    Tajaan daripada <strong>{{ $t['keluarga_nama'] }}</strong>
                    akan tamat pada <strong>{{ \Carbon\Carbon::parse($t['tarikh_tamat'])->format('d M Y') }}</strong>.
                    @if($t['teruk'])
                        <strong style="color:#dc2626;"> Perlu tindakan segera.</strong>
                    @else
                        Sila semak dan proses pembaharuan.
                    @endif
                </div>
                <div class="notif-meta">
                    <span class="notif-time"><i class="ti ti-calendar" style="font-size:.75rem;"></i> {{ \Carbon\Carbon::parse($t['tarikh_tamat'])->diffForHumans() }}</span>
                    @if($t['teruk'])
                    <span class="notif-urgency red"><i class="ti ti-alert-triangle" style="font-size:.65rem;"></i> Kritikal</span>
                    @else
                    <span class="notif-urgency amber"><i class="ti ti-clock" style="font-size:.65rem;"></i> {{ $hari }} hari lagi</span>
                    @endif
                </div>
            </div>
            <div class="notif-actions">
                <a href="{{ route('keluarga.index') }}" class="btn-sm primary">
                    <i class="ti ti-eye"></i> Semak
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ===== SUMBANGAN TERTUNGGAK ===== --}}
@if(!empty($sumbTertunggak))
<div class="notif-group">
    <div class="section-head">
        <div class="section-head-left">
            <i class="ti ti-cash-off" style="color:#dc2626;font-size:1.1rem;"></i>
            <h3>Sumbangan Tertunggak</h3>
            <span class="section-count red">{{ count($sumbTertunggak) }} rekod</span>
        </div>
        <a href="{{ route('sumbangan.index') }}" class="btn-sm">
            <i class="ti ti-arrow-right"></i> Lihat Sumbangan
        </a>
    </div>
    <div class="notif-list">
        @foreach($sumbTertunggak as $s)
        <div class="notif-item danger">
            <div class="notif-avatar danger">
                <i class="ti ti-cash-off"></i>
            </div>
            <div class="notif-content">
                <div class="notif-title">Sumbangan tertunggak — {{ $s['pelajar_nama'] }}</div>
                <div class="notif-body">
                    Sumbangan bulan <strong>{{ \Carbon\Carbon::parse($s['bulan'])->format('F Y') }}</strong>
                    daripada <strong>{{ $s['keluarga_nama'] }}</strong> belum diterima.
                </div>
                <div class="notif-meta">
                    <span class="notif-time"><i class="ti ti-cash" style="font-size:.75rem;"></i> RM{{ number_format($s['jumlah'], 2) }}</span>
                    <span class="notif-urgency red"><i class="ti ti-alert-circle" style="font-size:.65rem;"></i> Belum Selesai</span>
                </div>
            </div>
            <div class="notif-actions">
                <a href="{{ route('sumbangan.index') }}" class="btn-sm primary">
                    <i class="ti ti-eye"></i> Semak
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ===== NOTIFIKASI LAIN ===== --}}
@if(!empty($notifLain))
<div class="notif-group">
    <div class="section-head">
        <div class="section-head-left">
            <i class="ti ti-bell" style="color:#2563eb;font-size:1.1rem;"></i>
            <h3>Maklumat Sistem</h3>
            <span class="section-count blue">{{ count($notifLain) }} notifikasi</span>
        </div>
    </div>
    <div class="notif-list">
        @foreach($notifLain as $n)
        @php
            $jenisMap = [
                'success' => 'success',
                'warning' => 'warning',
                'info'    => 'info',
            ];
            $jenis = $jenisMap[$n['jenis']] ?? 'info';
            $ikonMap = [
                'success' => 'circle-check',
                'warning' => 'alert-triangle',
                'info'    => 'info-circle',
            ];
            $ikon = $ikonMap[$n['jenis']] ?? 'bell';
        @endphp
        <div class="notif-item {{ $jenis }}">
            <div class="notif-avatar {{ $jenis }}">
                <i class="ti ti-{{ $n['ikon'] ?? $ikon }}"></i>
            </div>
            <div class="notif-content">
                <div class="notif-title">{{ $n['tajuk'] }}</div>
                <div class="notif-body">{{ $n['mesej'] }}</div>
                <div class="notif-meta">
                    <span class="notif-time"><i class="ti ti-clock" style="font-size:.75rem;"></i> {{ $n['masa'] ?? now()->format('d M Y') }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ===== EMPTY STATE ===== --}}
@if(empty($hampirTamat) && empty($sumbTertunggak) && empty($notifLain))
<div class="empty-notif">
    <i class="ti ti-bell-check"></i>
    <h3>Tiada Notifikasi</h3>
    <p>Semua tajaan dan sumbangan dalam keadaan baik. Tiada peringatan pada masa ini.</p>
</div>
@endif

@endsection
