@extends('layouts.app')
@section('title','Sumbangan')
@section('page-title','Sumbangan')

@section('topbar-actions')
    @if(session('role', session('peranan')) === 'admin')
    <button class="topbar-btn primary" onclick="openModal('modal-tambah')">
        <i class="ti ti-plus"></i> Tambah Rekod
    </button>
    @endif
@endsection

@push('styles')
<style>
/* ===== STATS GRID ===== */
.sb-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.sb-stat {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.2rem 1.3rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: box-shadow .2s;
}
.sb-stat:hover { box-shadow: 0 4px 18px rgba(0,0,0,.07); }
.sb-stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.35rem; flex-shrink: 0;
}
.sb-stat-icon.green  { background: #dcfce7; color: #16a34a; }
.sb-stat-icon.blue   { background: #eff6ff; color: #2563eb; }
.sb-stat-icon.amber  { background: #fffbeb; color: #d97706; }
.sb-stat-icon.purple { background: #f5f3ff; color: #7c3aed; }
.sb-stat-label { font-size: .72rem; color: var(--text-muted); margin-bottom: 3px; font-weight: 500; text-transform: uppercase; letter-spacing: .04em; }
.sb-stat-value { font-size: 1.55rem; font-weight: 700; color: var(--text); line-height: 1.1; }
.sb-stat-sub   { font-size: .72rem; color: var(--text-muted); margin-top: 2px; }

/* ===== CHART BOX ===== */
.chart-box {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.2rem 1.4rem;
    margin-bottom: 1.5rem;
}
.chart-box-title {
    font-size: .9rem; font-weight: 600; color: var(--text);
    margin-bottom: 1rem; display: flex; align-items: center; gap: .4rem;
}
.chart-bars {
    display: flex;
    align-items: flex-end;
    gap: .5rem;
    height: 120px;
    overflow-x: auto;
    padding-bottom: .3rem;
}
.chart-col {
    display: flex; flex-direction: column; align-items: center;
    flex: 1; min-width: 36px; gap: 4px;
}
.chart-bar-wrap {
    width: 100%; display: flex; align-items: flex-end;
    height: 90px; gap: 2px;
}
.chart-bar {
    flex: 1; border-radius: 4px 4px 0 0;
    min-height: 4px; transition: opacity .2s;
}
.chart-bar:hover { opacity: .8; }
.chart-bar.diterima   { background: #22c55e; }
.chart-bar.tertunggak { background: #f59e0b; }
.chart-label { font-size: .65rem; color: var(--text-muted); white-space: nowrap; }

/* ===== FILTER BAR ===== */
.filter-bar {
    display: flex; gap: .65rem; align-items: center;
    flex-wrap: wrap; margin-bottom: 1rem;
}
.filter-bar select,
.filter-bar input[type="text"],
.filter-bar input[type="month"] {
    padding: .45rem .8rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: .85rem;
    background: var(--surface);
    color: var(--text);
}
.filter-bar .btn-reset {
    padding: .45rem .9rem; border-radius: 8px;
    border: 1px solid var(--border); background: var(--surface);
    color: var(--text-muted); cursor: pointer; font-size: .85rem;
    display: flex; align-items: center; gap: .35rem; transition: all .15s;
}
.filter-bar .btn-reset:hover { border-color: #ef4444; color: #ef4444; }

/* ===== TABLE ===== */
.table-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
.table-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.2rem; border-bottom: 1px solid var(--border); gap: .8rem; flex-wrap: wrap;
}
.section-title { font-weight: 600; font-size: .95rem; color: var(--text); display: flex; align-items: center; gap: .4rem; }
.rec-count { font-weight: 400; color: var(--text-muted); font-size: .85rem; }
.search-box { position: relative; }
.search-box i { position: absolute; left: .65rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: .9rem; }
.search-box input { padding: .4rem .75rem .4rem 2rem; border: 1px solid var(--border); border-radius: 8px; font-size: .85rem; background: var(--bg); width: 210px; }

table { width: 100%; border-collapse: collapse; }
thead th {
    padding: .7rem 1rem; text-align: left; font-size: .72rem;
    font-weight: 600; text-transform: uppercase; letter-spacing: .05em;
    color: var(--text-muted); border-bottom: 1px solid var(--border);
    white-space: nowrap; background: var(--bg);
}
tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: var(--bg); }
tbody td { padding: .8rem 1rem; font-size: .875rem; color: var(--text); vertical-align: middle; }

.family-cell { display: flex; flex-direction: column; }
.family-nama { font-weight: 600; }
.family-sub  { font-size: .75rem; color: var(--text-muted); margin-top: 1px; }

.amount-cell { font-weight: 700; font-size: .95rem; color: var(--text); }
.amount-sub  { font-size: .72rem; color: var(--text-muted); font-weight: 400; }

/* ===== BADGES ===== */
.badge {
    display: inline-flex; align-items: center; gap: .25rem;
    padding: .25rem .65rem; border-radius: 999px;
    font-size: .72rem; font-weight: 600; white-space: nowrap;
}
.badge.green  { background: #dcfce7; color: #15803d; }
.badge.amber  { background: #fef3c7; color: #b45309; }
.badge.red    { background: #fee2e2; color: #b91c1c; }
.badge.gray   { background: #f3f4f6; color: #6b7280; }

/* ===== ACTIONS ===== */
.actions { display: flex; gap: .4rem; }
.btn.sm {
    padding: .35rem .5rem; border-radius: 7px; border: 1px solid var(--border);
    background: var(--surface); cursor: pointer; font-size: .85rem;
    color: var(--text-muted); transition: all .15s; display: inline-flex; align-items: center;
}
.btn.sm:hover         { background: var(--bg); color: var(--text); border-color: var(--primary); }
.btn.sm.danger:hover  { background: #fee2e2; color: #b91c1c; border-color: #fca5a5; }

/* ===== EMPTY ===== */
.empty-state { text-align: center; padding: 3.5rem 1rem; color: var(--text-muted); }
.empty-state i { font-size: 2.5rem; display: block; margin-bottom: .8rem; opacity: .4; }
.empty-state p { margin: 0; font-size: .95rem; }

/* ===== MODAL ===== */
.modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    display: none; align-items: center; justify-content: center;
    z-index: 1000; padding: 1rem;
}
.modal-overlay.open { display: flex; }
.modal {
    background: var(--surface); border-radius: 16px; width: 100%;
    max-width: 520px; max-height: 92vh; overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    animation: slideUp .2s ease;
}
@keyframes slideUp {
    from { transform: translateY(28px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
.modal-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.1rem 1.3rem; border-bottom: 1px solid var(--border);
}
.modal-head h3 { margin: 0; font-size: 1rem; font-weight: 600; }
.modal-close { background: none; border: none; cursor: pointer; font-size: 1.1rem; color: var(--text-muted); border-radius: 6px; padding: .2rem .4rem; transition: background .15s; }
.modal-close:hover { background: var(--bg); color: var(--text); }
.modal-body { padding: 1.3rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: .6rem; padding: 1rem 1.3rem; border-top: 1px solid var(--border); }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: .85rem; margin-bottom: .85rem; }
.form-row.one { grid-template-columns: 1fr; }
.form-group { display: flex; flex-direction: column; gap: .35rem; }
.form-group label { font-size: .8rem; font-weight: 600; color: var(--text-muted); }
.form-group label .req { color: #ef4444; }
.form-group input,
.form-group select,
.form-group textarea {
    padding: .5rem .75rem; border: 1px solid var(--border); border-radius: 8px;
    font-size: .875rem; background: var(--bg); color: var(--text); transition: border-color .15s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
.form-group textarea { resize: vertical; min-height: 70px; }

/* Keluarga card preview */
.keluarga-preview {
    background: var(--bg); border: 1px solid var(--border);
    border-radius: 8px; padding: .6rem .85rem; margin-top: .4rem;
    font-size: .82rem; color: var(--text-muted); display: none;
}
.keluarga-preview.show { display: block; }

/* BUTTONS */
.btn { display: inline-flex; align-items: center; gap: .35rem; padding: .5rem 1rem; border-radius: 8px; border: 1px solid var(--border); cursor: pointer; font-size: .875rem; font-weight: 500; transition: all .15s; background: var(--surface); color: var(--text); }
.btn.primary { background: var(--primary); color: #fff; border-color: var(--primary); }
.btn.primary:hover { opacity: .9; }
.btn:hover { background: var(--bg); }

/* ALERT */
.alert { padding: .8rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: .875rem; display: flex; align-items: center; gap: .5rem; }
.alert.success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.alert.error   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
</style>
@endpush

@section('content')

{{-- ALERTS --}}
@if(session('success'))
<div class="alert success"><i class="ti ti-circle-check"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert error"><i class="ti ti-alert-circle"></i> {{ session('error') }}</div>
@endif

{{-- STATS --}}
<div class="sb-stats">
    <div class="sb-stat">
        <div class="sb-stat-icon green"><i class="ti ti-cash"></i></div>
        <div>
            <div class="sb-stat-label">Jumlah Sumbangan</div>
            <div class="sb-stat-value">RM{{ number_format($stats['jumlah_bulan_ini'] ?? 0, 0) }}</div>
            <div class="sb-stat-sub">semua masa</div>
        </div>
    </div>
    <div class="sb-stat">
        <div class="sb-stat-icon blue"><i class="ti ti-circle-check"></i></div>
        <div>
            <div class="sb-stat-label">Diterima</div>
            <div class="sb-stat-value">{{ $stats['bilangan_diterima'] ?? 0 }}</div>
            <div class="sb-stat-sub">jumlah pembayaran diterima</div>
        </div>
    </div>
    <div class="sb-stat">
        <div class="sb-stat-icon amber"><i class="ti ti-clock-exclamation"></i></div>
        <div>
            <div class="sb-stat-label">Tertunggak</div>
            <div class="sb-stat-value">{{ $stats['bilangan_tertunggak'] ?? 0 }}</div>
            <div class="sb-stat-sub">perlu tindakan segera</div>
        </div>
    </div>
    <div class="sb-stat">
        <div class="sb-stat-icon purple"><i class="ti ti-report-money"></i></div>
        <div>
            <div class="sb-stat-label">Jumlah Keseluruhan</div>
            <div class="sb-stat-value">RM{{ number_format($stats['jumlah_keseluruhan'] ?? 0, 0) }}</div>
            <div class="sb-stat-sub">semua masa</div>
        </div>
    </div>
</div>

{{-- CARTA TREND BULANAN --}}
@if(!empty($trendData))
<div class="chart-box">
    <div class="chart-box-title">
        <i class="ti ti-chart-bar"></i> Trend Sumbangan 12 Bulan
        <span style="margin-left:auto;display:flex;gap:.8rem;font-size:.75rem;font-weight:400;color:var(--text-muted);">
            <span><span style="display:inline-block;width:10px;height:10px;background:#22c55e;border-radius:2px;margin-right:3px;"></span>Diterima</span>
            <span><span style="display:inline-block;width:10px;height:10px;background:#f59e0b;border-radius:2px;margin-right:3px;"></span>Tertunggak</span>
        </span>
    </div>
    @php
        $maxVal = collect($trendData)->max(fn($d) => $d['jumlah']);
        $maxVal = $maxVal > 0 ? $maxVal : 1;
    @endphp
    <div class="chart-bars">
        @foreach($trendData as $bar)
        @php
            $hD = round(($bar['diterima'] / $maxVal) * 80);
            $hT = round(($bar['tertunggak'] / $maxVal) * 80);
        @endphp
        <div class="chart-col" title="{{ $bar['label'] }}: RM{{ number_format($bar['jumlah'],0) }}">
            <div class="chart-bar-wrap">
                @if($bar['diterima'] > 0)
                <div class="chart-bar diterima" style="height:{{ $hD }}px;" title="Diterima: RM{{ number_format($bar['diterima'],0) }}"></div>
                @endif
                @if($bar['tertunggak'] > 0)
                <div class="chart-bar tertunggak" style="height:{{ $hT }}px;" title="Tertunggak: RM{{ number_format($bar['tertunggak'],0) }}"></div>
                @endif
                @if($bar['jumlah'] == 0)
                <div style="height:4px;flex:1;background:var(--border);border-radius:4px 4px 0 0;"></div>
                @endif
            </div>
            <div class="chart-label">{{ \Carbon\Carbon::parse($bar['bulan'].'-01')->format('M') }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- FILTER --}}
<form method="GET" action="{{ route('sumbangan.index') }}" class="filter-bar" id="filterForm">
    <div class="search-box">
        <i class="ti ti-search"></i>
        <input type="text" name="cari" placeholder="Cari nama..." value="{{ request('cari') }}" oninput="cariLive(this.value)">
    </div>
    <input type="month" name="bulan" value="{{ request('bulan') }}" onchange="document.getElementById('filterForm').submit()" title="Tapis ikut bulan">
    <select name="status" onchange="document.getElementById('filterForm').submit()">
        <option value="">Semua Status</option>
        <option value="Diterima"    {{ request('status') === 'Diterima'    ? 'selected' : '' }}>Diterima</option>
        <option value="Tertunggak"  {{ request('status') === 'Tertunggak'  ? 'selected' : '' }}>Tertunggak</option>
        <option value="Tangguhan"   {{ request('status') === 'Tangguhan'   ? 'selected' : '' }}>Tangguhan</option>
    </select>
    @if(request('cari') || request('bulan') || request('status'))
    <a href="{{ route('sumbangan.index') }}" class="btn-reset">
        <i class="ti ti-x"></i> Reset
    </a>
    @endif
</form>

{{-- TABLE --}}
<div class="table-wrap">
    <div class="table-header">
        <div class="section-title">
            <i class="ti ti-cash"></i> Rekod Sumbangan
            <span class="rec-count">({{ count($sumbangan ?? []) }} rekod)</span>
        </div>
    </div>

    <table id="sumbTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Bulan</th>
                <th>Keluarga Angkat</th>
                <th>Pelajar</th>
                <th>Jumlah (RM)</th>
                <th>Status</th>
                <th>Tarikh Terima</th>
                <th>Nota</th>
                <th>Sejarah</th>
                @if(session('role', session('peranan')) === 'admin')<th>Tindakan</th>@endif
            </tr>
        </thead>
        <tbody>
            @forelse($sumbangan ?? [] as $i => $s)
            <tr>
                <td style="color:var(--text-muted);font-size:.8rem;">{{ $i + 1 }}</td>

                {{-- Bulan --}}
                <td>
                    @if(!empty($s['bulan']))
                        <span style="font-weight:500;">{{ \Carbon\Carbon::parse($s['bulan'].'-01')->format('M Y') }}</span>
                    @elseif(!empty($s['tarikh_terima']))
                        <span style="font-weight:500;">{{ \Carbon\Carbon::parse($s['tarikh_terima'])->format('M Y') }}</span>
                    @else
                        <span style="color:var(--text-muted)">—</span>
                    @endif
                </td>

                {{-- Keluarga --}}
                <td>
                    <div class="family-cell">
                        <span class="family-nama">{{ $s['keluarga_nama'] ?? '—' }}</span>
                    </div>
                </td>

                {{-- Pelajar --}}
                <td>
                    <div class="family-cell">
                        <span>{{ $s['pelajar_nama'] ?? '—' }}</span>
                        @if(!empty($s['pelajar_matrik']))
                        <span class="family-sub">{{ $s['pelajar_matrik'] }}</span>
                        @endif
                    </div>
                </td>

                {{-- Jumlah --}}
                <td>
                    <div class="amount-cell">RM{{ number_format($s['jumlah'] ?? 0, 2) }}</div>
                </td>

                {{-- Status --}}
                <td>
                    @php $status = $s['status'] ?? 'Diterima'; @endphp
                    @if(strtolower($status) === 'diterima')
                        <span class="badge green"><i class="ti ti-circle-check" style="font-size:.7rem;"></i> Diterima</span>
                    @elseif(strtolower($status) === 'tertunggak')
                        <span class="badge amber"><i class="ti ti-clock" style="font-size:.7rem;"></i> Tertunggak</span>
                    @elseif(strtolower($status) === 'tangguhan')
                        <span class="badge gray"><i class="ti ti-pause" style="font-size:.7rem;"></i> Tangguhan</span>
                    @else
                        <span class="badge gray">{{ $status }}</span>
                    @endif
                </td>

                {{-- Tarikh --}}
                <td style="font-size:.82rem;color:var(--text-muted);">
                    {{ !empty($s['tarikh_terima']) ? \Carbon\Carbon::parse($s['tarikh_terima'])->format('d M Y') : '—' }}
                </td>

                {{-- Nota/Keterangan --}}
                <td style="max-width:150px;font-size:.8rem;color:var(--text-muted);">
                    {{ !empty($s['keterangan']) ? \Illuminate\Support\Str::limit($s['keterangan'], 40) : '—' }}
                </td>

                {{-- Sejarah --}}
                <td>
                    <button class="btn sm" title="Lihat Sejarah Sumbangan"
                            onclick="lihatSejarah('{{ $s['id_pelajar'] ?? '' }}')">
                        <i class="ti ti-history"></i>
                    </button>
                </td>

                {{-- Tindakan --}}
                @if(session('role', session('peranan')) === 'admin')
                <td>
                    <div class="actions">
                        <button onclick="editSumbangan({{ json_encode($s) }})" class="btn sm" title="Kemaskini">
                            <i class="ti ti-edit"></i>
                        </button>
                        <button onclick="confirmPadam('{{ $s['id'] }}','{{ now()->format('M Y') }}')" class="btn sm danger" title="Padam">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ session('role', session('peranan')) === 'admin' ? 10 : 9 }}">
                    <div class="empty-state">
                        <i class="ti ti-cash"></i>
                        <p>Tiada rekod sumbangan ditemui.</p>
                        @if(session('role', session('peranan')) === 'admin')
                        <button class="btn primary" onclick="openModal('modal-tambah')" style="margin-top:.8rem;">
                            <i class="ti ti-plus"></i> Tambah Rekod
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(session('role', session('peranan')) === 'admin')

{{-- ===== MODAL TAMBAH / EDIT ===== --}}
<div class="modal-overlay" id="modal-tambah">
    <div class="modal">
        <div class="modal-head">
            <h3 id="modal-title"><i class="ti ti-cash"></i> Tambah Rekod Sumbangan</h3>
            <button class="modal-close" onclick="closeModal('modal-tambah')"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" id="form-sumbangan" action="{{ route('sumbangan.store') }}">
        @csrf
        <input type="hidden" name="_method" id="form-method" value="POST">

        <div class="modal-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Keluarga Angkat <span class="req">*</span></label>
                    <select name="id_keluarga_angkat" id="s-keluarga" required onchange="previewKeluarga(this)">
                        <option value="">— Pilih Keluarga —</option>
                        @foreach($keluargaList ?? [] as $k)
                        <option value="{{ $k['id_keluarga_angkat'] }}"
                                data-pelajar="{{ $k['id_pelajar'] ?? '' }}">
                            {{ $k['nama_keluarga_angkat'] }}
                        </option>
                        @endforeach
                    </select>
                    <div class="keluarga-preview" id="keluarga-preview">
                        <i class="ti ti-info-circle"></i> <span id="preview-text"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Bulan / Tahun <span class="req">*</span></label>
                    <input type="month" name="bulan" id="s-bulan" value="{{ now()->format('Y-m') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Jumlah (RM) <span class="req">*</span></label>
                    <input type="number" name="jumlah" id="s-jumlah" step="0.01" min="0" placeholder="cth: 600" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="s-status">
                        <option value="Diterima">Diterima</option>
                        <option value="Tertunggak">Tertunggak</option>
                        <option value="Tangguhan">Tangguhan</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Tarikh Terima</label>
                    <input type="date" name="tarikh_terima" id="s-tarikh" value="{{ now()->format('Y-m-d') }}">
                </div>
            </div>

            <div class="form-row one">
                <div class="form-group">
                    <label>Nota / Keterangan</label>
                    <textarea name="keterangan" id="s-nota" placeholder="Nota tambahan (pilihan)..."></textarea>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn" onclick="tutupModalTambah()">Batal</button>
            <button type="submit" class="btn primary"><i class="ti ti-device-floppy"></i> Simpan</button>
        </div>
        </form>
    </div>
</div>

{{-- ===== MODAL PADAM ===== --}}
<div class="modal-overlay" id="modal-padam">
    <div class="modal" style="max-width:400px;">
        <div class="modal-head">
            <h3 style="color:#b91c1c;"><i class="ti ti-alert-triangle"></i> Sahkan Pemadaman</h3>
            <button class="modal-close" onclick="closeModal('modal-padam')"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body">
            <p style="margin:0;font-size:.9rem;">
                Adakah anda pasti mahu memadam rekod sumbangan <strong id="padam-label"></strong>?
                Tindakan ini tidak boleh dibatalkan.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" onclick="closeModal('modal-padam')">Batal</button>
            <form method="POST" id="form-padam">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn" style="background:#ef4444;color:#fff;border-color:#ef4444;">
                <i class="ti ti-trash"></i> Ya, Padam
            </button>
            </form>
        </div>
    </div>
</div>

@endif

{{-- ===== MODAL SEJARAH SUMBANGAN ===== --}}
<div class="modal-overlay" id="modal-sejarah">
    <div class="modal" style="max-width:680px;">
        <div class="modal-head">
            <h3><i class="ti ti-history"></i> Sejarah Sumbangan</h3>
            <button class="modal-close" onclick="closeModal('modal-sejarah')"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body">
            <div id="sejarah-loading" style="text-align:center;padding:2rem;color:var(--text-muted);">
                <i class="ti ti-loader-2"></i> Memuatkan sejarah...
            </div>
            <div id="sejarah-content" style="display:none;">
                <div style="margin-bottom:1rem;">
                    <div style="font-weight:700;font-size:1.05rem;" id="sj-pelajar-nama"></div>
                    <div style="font-size:.8rem;color:var(--text-muted);" id="sj-pelajar-sub"></div>
                </div>
                <div class="sb-stats" style="grid-template-columns:repeat(3,1fr);margin-bottom:1rem;">
                    <div class="sb-stat" style="padding:.8rem;">
                        <div>
                            <div class="sb-stat-label">Keseluruhan</div>
                            <div class="sb-stat-value" style="font-size:1.15rem;" id="sj-jumlah-keseluruhan"></div>
                        </div>
                    </div>
                    <div class="sb-stat" style="padding:.8rem;">
                        <div>
                            <div class="sb-stat-label">Diterima</div>
                            <div class="sb-stat-value" style="font-size:1.15rem;color:#16a34a;" id="sj-jumlah-diterima"></div>
                        </div>
                    </div>
                    <div class="sb-stat" style="padding:.8rem;">
                        <div>
                            <div class="sb-stat-label">Tertunggak</div>
                            <div class="sb-stat-value" style="font-size:1.15rem;color:#d97706;" id="sj-jumlah-tertunggak"></div>
                        </div>
                    </div>
                </div>
                <div style="max-height:340px;overflow-y:auto;border:1px solid var(--border);border-radius:10px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Jumlah (RM)</th>
                                <th>Status</th>
                                <th>Tarikh Terima</th>
                                <th>Nota</th>
                            </tr>
                        </thead>
                        <tbody id="sejarah-tbody"></tbody>
                    </table>
                </div>
            </div>
            <div id="sejarah-empty" style="display:none;" class="empty-state">
                <i class="ti ti-history"></i>
                <p>Tiada sejarah sumbangan untuk pelajar ini.</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" onclick="closeModal('modal-sejarah')">Tutup</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ===== MODAL ===== */
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); });
});

/* ===== PREVIEW KELUARGA ===== */
function previewKeluarga(sel) {
    const preview = document.getElementById('keluarga-preview');
    const text    = document.getElementById('preview-text');
    const opt     = sel.options[sel.selectedIndex];
    if (sel.value) {
        text.textContent = 'ID Pelajar: ' + (opt.dataset.pelajar || 'Belum ditugaskan');
        preview.classList.add('show');
    } else {
        preview.classList.remove('show');
    }
}

/* ===== EDIT SUMBANGAN ===== */
function editSumbangan(s) {
    const f = document.getElementById('form-sumbangan');
    f.action = `/sumbangan/${s.id}`;
    document.getElementById('form-method').value = 'PUT';
    document.getElementById('modal-title').innerHTML = '<i class="ti ti-edit"></i> Kemaskini Rekod Sumbangan';

    document.getElementById('s-keluarga').value = s.id_keluarga_angkat ?? '';
    document.getElementById('s-bulan').value    = s.bulan ?? '';
    document.getElementById('s-jumlah').value   = s.jumlah ?? '';
    document.getElementById('s-status').value   = s.status ?? 'Diterima';
    document.getElementById('s-tarikh').value   = s.tarikh_terima ?? '';
    document.getElementById('s-nota').value     = s.keterangan ?? '';

    openModal('modal-tambah');
}

/* ===== RESET MODAL ===== */
function tutupModalTambah() {
    const f = document.getElementById('form-sumbangan');
    f.action = '{{ route("sumbangan.store") }}';
    document.getElementById('form-method').value = 'POST';
    document.getElementById('modal-title').innerHTML = '<i class="ti ti-cash"></i> Tambah Rekod Sumbangan';
    f.reset();
    document.getElementById('keluarga-preview').classList.remove('show');
    closeModal('modal-tambah');
}

/* Butang tambah buka modal fresh */
document.querySelector('[onclick="openModal(\'modal-tambah\')"]')
    ?.addEventListener('click', function() {
        const f = document.getElementById('form-sumbangan');
        f.action = '{{ route("sumbangan.store") }}';
        document.getElementById('form-method').value = 'POST';
        document.getElementById('modal-title').innerHTML = '<i class="ti ti-cash"></i> Tambah Rekod Sumbangan';
        f.reset();
        document.getElementById('s-bulan').value  = '{{ now()->format("Y-m") }}';
        document.getElementById('s-tarikh').value = '{{ now()->format("Y-m-d") }}';
        document.getElementById('keluarga-preview').classList.remove('show');
    });

/* ===== PADAM ===== */
function confirmPadam(id, label) {
    document.getElementById('padam-label').textContent = label;
    document.getElementById('form-padam').action = `/sumbangan/${id}`;
    openModal('modal-padam');
}

/* ===== CARI LIVE ===== */
function cariLive(q) {
    const rows = document.querySelectorAll('#sumbTable tbody tr');
    rows.forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none';
    });
}

/* ===== SEJARAH SUMBANGAN ===== */
const STATUS_BADGE = {
    diterima:   '<span class="badge green"><i class="ti ti-circle-check" style="font-size:.7rem;"></i> Diterima</span>',
    tertunggak: '<span class="badge amber"><i class="ti ti-clock" style="font-size:.7rem;"></i> Tertunggak</span>',
    tangguhan:  '<span class="badge gray"><i class="ti ti-pause" style="font-size:.7rem;"></i> Tangguhan</span>',
};

function lihatSejarah(idPelajar) {
    if (!idPelajar) {
        alert('Pelajar belum ditugaskan untuk rekod ini.');
        return;
    }

    openModal('modal-sejarah');
    document.getElementById('sejarah-loading').style.display = 'block';
    document.getElementById('sejarah-content').style.display = 'none';
    document.getElementById('sejarah-empty').style.display   = 'none';

    fetch(`/sumbangan/sejarah/${idPelajar}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('sejarah-loading').style.display = 'none';

            const rekod     = data.rekod || [];
            const ringkasan = data.ringkasan || {};

            if (rekod.length === 0) {
                document.getElementById('sejarah-empty').style.display = 'block';
                return;
            }

            document.getElementById('sejarah-content').style.display = 'block';
            document.getElementById('sj-pelajar-nama').textContent =
                (ringkasan.pelajar_nama || '—') + ' — ' + (ringkasan.keluarga_nama || '—');
            document.getElementById('sj-pelajar-sub').textContent =
                (ringkasan.pelajar_matrik || '') + ' • ' + (ringkasan.bilangan_rekod || 0) + ' rekod sumbangan';

            document.getElementById('sj-jumlah-keseluruhan').textContent = 'RM' + Number(ringkasan.jumlah_keseluruhan || 0).toLocaleString('en-MY', {minimumFractionDigits: 2});
            document.getElementById('sj-jumlah-diterima').textContent    = 'RM' + Number(ringkasan.jumlah_diterima || 0).toLocaleString('en-MY', {minimumFractionDigits: 2});
            document.getElementById('sj-jumlah-tertunggak').textContent  = 'RM' + Number(ringkasan.jumlah_tertunggak || 0).toLocaleString('en-MY', {minimumFractionDigits: 2});

            const tbody = document.getElementById('sejarah-tbody');
            tbody.innerHTML = rekod.map(r => {
                const statusKey = (r.status || 'diterima').toLowerCase();
                const badge     = STATUS_BADGE[statusKey] || `<span class="badge gray">${r.status || '—'}</span>`;
                const bulanLbl  = r.bulan ? r.bulan : (r.tarikh_terima ? r.tarikh_terima.substring(0,7) : '—');
                return `
                    <tr>
                        <td>${bulanLbl}</td>
                        <td><strong>RM${Number(r.jumlah || 0).toLocaleString('en-MY', {minimumFractionDigits: 2})}</strong></td>
                        <td>${badge}</td>
                        <td style="font-size:.82rem;color:var(--text-muted);">${r.tarikh_terima || '—'}</td>
                        <td style="font-size:.82rem;color:var(--text-muted);">${r.keterangan || '—'}</td>
                    </tr>`;
            }).join('');
        })
        .catch(() => {
            document.getElementById('sejarah-loading').style.display = 'none';
            document.getElementById('sejarah-empty').style.display   = 'block';
        });
}
</script>
@endpush
