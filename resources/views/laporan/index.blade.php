@extends('layouts.app')
@section('title','Laporan')
@section('page-title','Laporan')

@push('styles')
<style>
.lap-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.25rem;
    align-items: start;
}
@media(max-width:860px){ .lap-grid{ grid-template-columns:1fr; } }

.card { background:var(--surface); border:1px solid var(--border); border-radius:14px; overflow:hidden; }
.card-head { padding:1rem 1.3rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:.5rem; }
.card-head h3 { margin:0; font-size:.95rem; font-weight:600; }
.card-body { padding:1.3rem; }

/* stats */
.stats-grid { display:grid; grid-template-columns:1fr 1fr; gap:.85rem; margin-bottom:1.25rem; }
.stat-item { background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:1rem; display:flex; align-items:center; gap:.75rem; }
.stat-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
.stat-icon.blue   { background:#eff6ff; color:#2563eb; }
.stat-icon.green  { background:#dcfce7; color:#16a34a; }
.stat-icon.purple { background:#f5f3ff; color:#7c3aed; }
.stat-icon.amber  { background:#fffbeb; color:#d97706; }
.stat-icon.rose   { background:#fff1f2; color:#e11d48; }
.stat-icon.teal   { background:#f0fdfa; color:#0d9488; }
.stat-label { font-size:.7rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
.stat-value { font-size:1.3rem; font-weight:700; color:var(--text); line-height:1.1; }

/* form */
.form-group { display:flex; flex-direction:column; gap:.35rem; margin-bottom:.85rem; }
.form-group label { font-size:.8rem; font-weight:600; color:var(--text-muted); }
.form-group select,
.form-group input[type="date"] {
    padding:.5rem .75rem; border:1px solid var(--border); border-radius:8px;
    font-size:.875rem; background:var(--bg); color:var(--text);
    transition:border-color .15s; width:100%;
}
.form-group select:focus,
.form-group input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(99,102,241,.12); }

.divider { height:1px; background:var(--border); margin:1rem 0; }

/* btn */
.btn { display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1rem; border-radius:8px; border:1px solid var(--border); cursor:pointer; font-size:.875rem; font-weight:500; transition:all .15s; background:var(--surface); color:var(--text); text-decoration:none; }
.btn.primary { background:var(--primary); color:#fff; border-color:var(--primary); }
.btn.primary:hover { opacity:.9; }
.btn.full { width:100%; justify-content:center; }
.btn.outline { border-color:var(--primary); color:var(--primary); }
.btn.outline:hover { background:var(--primary); color:#fff; }

/* jenis cards */
.jenis-grid { display:grid; grid-template-columns:1fr 1fr; gap:.65rem; margin-bottom:.85rem; }
.jenis-card {
    border:2px solid var(--border); border-radius:10px; padding:.75rem;
    cursor:pointer; transition:all .15s; text-align:center; background:var(--bg);
    position:relative;
}
.jenis-card:hover { border-color:var(--primary); }
.jenis-card.active { border-color:var(--primary); background:color-mix(in srgb, var(--primary) 6%, transparent); }
.jenis-card input[type="radio"] { position:absolute; opacity:0; }
.jenis-card i { font-size:1.4rem; display:block; margin-bottom:.3rem; }
.jenis-card span { font-size:.78rem; font-weight:600; color:var(--text); }
.jenis-card.active span { color:var(--primary); }

.j-pelajar  i { color:#2563eb; }
.j-sumbangan i { color:#16a34a; }
.j-keluarga  i { color:#7c3aed; }
.j-meeting   i { color:#d97706; }
.j-prestasi  i { color:#0d9488; }

/* Penanda warna jenis laporan — kekal kelihatan walau dah scroll ke bahagian filter */
:root { --jenis-color: #2563eb; }
.filter-label-badge {
    display:inline-flex; align-items:center; gap:.45rem;
    padding:.4rem .8rem; border-radius:20px;
    font-size:.78rem; font-weight:700;
    background: color-mix(in srgb, var(--jenis-color) 12%, transparent);
    color: var(--jenis-color);
    margin-bottom: 1rem;
    transition: background .2s, color .2s;
}
.filter-label-badge i { font-size:.9rem; }
#btn-cetak {
    background: var(--jenis-color) !important;
    border-color: var(--jenis-color) !important;
    transition: background .2s, border-color .2s;
}

/* info panel */
.info-row { display:flex; justify-content:space-between; align-items:center; padding:.55rem 0; border-bottom:1px solid var(--border); font-size:.83rem; }
.info-row:last-child { border-bottom:none; }
.info-row .label { color:var(--text-muted); }
.info-row .value { font-weight:600; color:var(--text); }
.badge-aktif { background:#dcfce7; color:#15803d; padding:.15rem .55rem; border-radius:999px; font-size:.72rem; font-weight:600; }

/* preview table */
.prev-wrap { overflow-x:auto; }
table.prev-table { width:100%; border-collapse:collapse; font-size:.82rem; }
table.prev-table th { background:var(--bg); padding:.55rem .85rem; text-align:left; font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--text-muted); border-bottom:1px solid var(--border); white-space:nowrap; }
table.prev-table td { padding:.65rem .85rem; border-bottom:1px solid var(--border); color:var(--text); }
table.prev-table tr:last-child td { border-bottom:none; }
table.prev-table tr:hover td { background:var(--bg); }

.empty { text-align:center; padding:2.5rem 1rem; color:var(--text-muted); }
.empty i { font-size:2rem; display:block; margin-bottom:.5rem; opacity:.35; }

.page-desc { color:var(--text-muted); font-size:.875rem; margin-top:.25rem; margin-bottom:1.25rem; }
</style>
@endpush

@section('content')

<h2 style="margin:0;font-size:1.1rem;font-weight:700;">Jana Laporan</h2>
<p class="page-desc">Cetak dan eksport laporan program Protege Bitara UPSI</p>

{{-- STATS --}}
<div class="stats-grid">
    <div class="stat-item">
        <div class="stat-icon blue"><i class="ti ti-users"></i></div>
        <div><div class="stat-label">Jumlah Pelajar</div><div class="stat-value">{{ $stats['jumlah_pelajar'] ?? 0 }}</div></div>
    </div>
    <div class="stat-item">
        <div class="stat-icon green"><i class="ti ti-user-check"></i></div>
        <div><div class="stat-label">Pelajar Aktif</div><div class="stat-value">{{ $stats['pelajar_aktif'] ?? 0 }}</div></div>
    </div>
    <div class="stat-item">
        <div class="stat-icon purple"><i class="ti ti-heart-handshake"></i></div>
        <div><div class="stat-label">Keluarga Angkat</div><div class="stat-value">{{ $stats['jumlah_keluarga'] ?? 0 }}</div></div>
    </div>
    <div class="stat-item">
        <div class="stat-icon amber"><i class="ti ti-cash"></i></div>
        <div><div class="stat-label">Jumlah Sumbangan</div><div class="stat-value">RM{{ number_format($stats['jumlah_sumbangan'] ?? 0, 0) }}</div></div>
    </div>
    <div class="stat-item">
        <div class="stat-icon teal"><i class="ti ti-calendar-event"></i></div>
        <div><div class="stat-label">Jumlah Meeting</div><div class="stat-value">{{ $stats['jumlah_meeting'] ?? 0 }}</div></div>
    </div>
    <div class="stat-item">
        <div class="stat-icon rose"><i class="ti ti-alert-triangle"></i></div>
        <div><div class="stat-label">Hampir Tamat</div><div class="stat-value">{{ $stats['hampir_tamat'] ?? 0 }}</div></div>
    </div>
</div>

<div class="lap-grid">

    {{-- KIRI: TETAPAN --}}
    <div>
        <div class="card" style="margin-bottom:1.25rem;">
            <div class="card-head">
                <i class="ti ti-settings" style="color:var(--primary);"></i>
                <h3>Tetapan Laporan</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('laporan.cetak') }}" id="form-laporan" target="_blank">
                @csrf

                {{-- Jenis Laporan --}}
                <div style="margin-bottom:.85rem;">
                    <label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:.5rem;">Jenis Laporan</label>
                    <div class="jenis-grid">
                        <label class="jenis-card j-pelajar active" id="jk-pelajar">
                            <input type="radio" name="jenis" value="pelajar" checked onchange="tukarJenis(this)">
                            <i class="ti ti-users"></i>
                            <span>Senarai Pelajar</span>
                        </label>
                        <label class="jenis-card j-sumbangan" id="jk-sumbangan">
                            <input type="radio" name="jenis" value="sumbangan" onchange="tukarJenis(this)">
                            <i class="ti ti-cash"></i>
                            <span>Sumbangan</span>
                        </label>
                        <label class="jenis-card j-keluarga" id="jk-keluarga">
                            <input type="radio" name="jenis" value="keluarga" onchange="tukarJenis(this)">
                            <i class="ti ti-heart-handshake"></i>
                            <span>Keluarga Angkat</span>
                        </label>
                        <label class="jenis-card j-meeting" id="jk-meeting">
                            <input type="radio" name="jenis" value="meeting" onchange="tukarJenis(this)">
                            <i class="ti ti-calendar-event"></i>
                            <span>Meeting Record</span>
                        </label>
                        <label class="jenis-card j-prestasi" id="jk-prestasi" style="grid-column:1/-1;">
                            <input type="radio" name="jenis" value="prestasi" onchange="tukarJenis(this)">
                            <i class="ti ti-chart-bar"></i>
                            <span>Prestasi Pelajar</span>
                        </label>
                    </div>
                </div>

                <div class="divider"></div>

                {{-- Penanda jenis laporan yang dipilih — warna berubah ikut jenis --}}
                <div class="filter-label-badge" id="filter-label">
                    <i class="ti ti-file-text"></i>
                    <span id="filter-label-text">Senarai Pelajar</span>
                </div>

                {{-- Filter Dinamik --}}
                <div id="filter-pelajar">
                    <div class="form-group">
                        <label>Status Pengajian</label>
                        <select name="status">
                            <option value="">Semua Status</option>
                            <option value="Aktif">Aktif</option>
                            <option value="Tamat">Tamat</option>
                            <option value="Tangguh">Tangguh</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Semester</label>
                        <select name="semester">
                            <option value="">Semua Semester</option>
                            @foreach($semesterList ?? [] as $sem)
                            <option value="{{ $sem }}">{{ $sem }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="filter-sumbangan" style="display:none;">
                    <div class="form-group">
                        <label>Status Sumbangan</label>
                        <select name="status">
                            <option value="">Semua Status</option>
                            <option value="Diterima">Diterima</option>
                            <option value="Tertunggak">Tertunggak</option>
                        </select>
                    </div>
                </div>

                <div id="filter-keluarga" style="display:none;">
                    <div class="form-group">
                        <label>Status Tajaan</label>
                        <select name="status">
                            <option value="">Semua Status</option>
                            <option value="Aktif">Aktif</option>
                            <option value="Tamat">Tamat</option>
                        </select>
                    </div>
                </div>

                <div id="filter-meeting" style="display:none;">
                    <div class="form-group">
                        <label>Jenis Pertemuan</label>
                        <select name="jenis_pertemuan">
                            <option value="">Semua Jenis</option>
                            <option value="Bersemuka">Bersemuka</option>
                            <option value="Dalam Talian">Dalam Talian</option>
                            <option value="Telefon">Telefon</option>
                        </select>
                    </div>
                </div>

                <div id="filter-prestasi" style="display:none;">
                    <div class="form-group">
                        <label>Semester</label>
                        <select name="semester">
                            <option value="">Semua Semester</option>
                            @foreach($semesterList ?? [] as $sem)
                            <option value="{{ $sem }}">{{ $sem }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="divider"></div>

                {{-- Saiz & Orientasi --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;margin-bottom:.85rem;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Saiz Kertas</label>
                        <select name="saiz">
                            <option value="A4">A4 (210×297mm)</option>
                            <option value="A3">A3 (297×420mm)</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Orientasi</label>
                        <select name="orientasi">
                            <option value="portrait">Portrait</option>
                            <option value="landscape">Landscape</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Tarikh Laporan</label>
                    <input type="date" name="tarikh_laporan" value="{{ now()->format('Y-m-d') }}">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem;margin-top:1rem;">
                    <button type="submit" class="btn primary full" id="btn-cetak">
                        <i class="ti ti-printer"></i> Cetak / PDF
                    </button>
                    <button type="button" class="btn outline full" onclick="praLihat()">
                        <i class="ti ti-eye"></i> Pratonton
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>

    {{-- KANAN: MAKLUMAT --}}
    <div>
        <div class="card" style="margin-bottom:1.25rem;">
            <div class="card-head">
                <i class="ti ti-info-circle" style="color:var(--text-muted);"></i>
                <h3>Maklumat Laporan</h3>
            </div>
            <div class="card-body" style="padding-top:.75rem;padding-bottom:.75rem;">
                <div class="info-row">
                    <span class="label">Tarikh Cetak</span>
                    <span class="value">{{ now()->format('d M Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Jumlah Pelajar</span>
                    <span class="value">{{ $stats['jumlah_pelajar'] ?? 0 }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Semester Teratas</span>
                    <span class="value">{{ $stats['semester_teratas'] ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Tajaan Hampir Tamat</span>
                    <span class="value" style="color:#e11d48;">{{ $stats['hampir_tamat'] ?? 0 }} pelajar</span>
                </div>
                <div class="info-row">
                    <span class="label">Status Program</span>
                    <span class="badge-aktif">Aktif</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <i class="ti ti-bulb" style="color:#d97706;"></i>
                <h3>Panduan</h3>
            </div>
            <div class="card-body" style="padding-top:.75rem;padding-bottom:.75rem;">
                <div style="font-size:.8rem;color:var(--text-muted);line-height:1.7;">
                    <p style="margin:0 0 .5rem;">① Pilih <strong>jenis laporan</strong> yang dikehendaki.</p>
                    <p style="margin:0 0 .5rem;">② Tetapkan <strong>filter</strong> (status, semester, dll).</p>
                    <p style="margin:0 0 .5rem;">③ Pilih <strong>saiz kertas</strong> dan orientasi.</p>
                    <p style="margin:0 0 .5rem;">④ Klik <strong>Cetak / PDF</strong> — laporan akan dibuka dalam tab baharu.</p>
                    <p style="margin:0;">⑤ Guna fungsi <em>Print</em> browser untuk simpan sebagai PDF.</p>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
const filterIds = ['filter-pelajar','filter-sumbangan','filter-keluarga','filter-meeting','filter-prestasi'];
const jenisIds  = ['jk-pelajar','jk-sumbangan','jk-keluarga','jk-meeting','jk-prestasi'];

// Sepadan dengan warna ikon .j-xxx yang sedia ada dalam sistem
const jenisMeta = {
    pelajar:   { label: 'Senarai Pelajar',  color: '#2563eb' },
    sumbangan: { label: 'Sumbangan',        color: '#16a34a' },
    keluarga:  { label: 'Keluarga Angkat',  color: '#7c3aed' },
    meeting:   { label: 'Meeting Record',   color: '#d97706' },
    prestasi:  { label: 'Prestasi Pelajar', color: '#0d9488' },
};

function tukarJenis(radio) {
    // Reset active
    jenisIds.forEach(id => document.getElementById(id)?.classList.remove('active'));
    document.getElementById('jk-' + radio.value)?.classList.add('active');

    // Show/hide filters
    filterIds.forEach(id => { if(document.getElementById(id)) document.getElementById(id).style.display = 'none'; });
    const tgt = document.getElementById('filter-' + radio.value);
    if(tgt) tgt.style.display = 'block';

    // Kemaskini label + warna penanda supaya nampak walau dah scroll
    const meta = jenisMeta[radio.value] || jenisMeta.pelajar;
    const labelEl = document.getElementById('filter-label-text');
    if (labelEl) labelEl.textContent = meta.label;
    document.documentElement.style.setProperty('--jenis-color', meta.color);
}

function praLihat() {
    document.getElementById('form-laporan').target = '_blank';
    document.getElementById('form-laporan').submit();
}

// Klik pada jenis card
document.querySelectorAll('.jenis-card').forEach(card => {
    card.addEventListener('click', function() {
        const radio = this.querySelector('input[type="radio"]');
        if(radio) { radio.checked = true; tukarJenis(radio); }
    });
});

// Pastikan label & warna betul semasa mula-mula buka page
document.addEventListener('DOMContentLoaded', function() {
    const checked = document.querySelector('input[name="jenis"]:checked');
    if (checked) tukarJenis(checked);
});
</script>
@endpush
