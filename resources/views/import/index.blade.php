@extends('layouts.app')
@section('title','Import Excel')
@section('page-title','Import Excel')

@section('topbar-actions')
    <a href="{{ route('pelajar.index') }}" class="topbar-btn">
        <i class="ti ti-users"></i> Senarai Pelajar
    </a>
@endsection

@push('styles')
<style>
/* ===== PAGE HEADER ===== */
.page-desc { color: var(--text-muted); font-size: .875rem; margin-top: .25rem; }

/* ===== GRID ===== */
.import-grid {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 1.25rem;
    align-items: start;
}
@media(max-width:820px) {
    .import-grid { grid-template-columns: 1fr; }
}

/* ===== CARDS ===== */
.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
}
.card-head {
    padding: 1rem 1.3rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: .5rem;
}
.card-head h3 { margin: 0; font-size: .95rem; font-weight: 600; }
.card-body { padding: 1.3rem; }

/* ===== DROPZONE ===== */
.dropzone {
    border: 2px dashed var(--border);
    border-radius: 12px;
    padding: 2.5rem 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
    position: relative;
    background: var(--bg);
}
.dropzone:hover,
.dropzone.drag-over {
    border-color: var(--primary);
    background: color-mix(in srgb, var(--primary) 5%, transparent);
}
.dropzone input[type="file"] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.dropzone-icon {
    font-size: 2.5rem;
    color: var(--primary);
    display: block;
    margin-bottom: .75rem;
    opacity: .7;
}
.dropzone-title { font-size: 1rem; font-weight: 600; color: var(--text); margin-bottom: .35rem; }
.dropzone-sub   { font-size: .8rem; color: var(--text-muted); }
.dropzone-formats {
    display: flex; gap: .5rem; justify-content: center; margin-top: .85rem; flex-wrap: wrap;
}
.format-badge {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: .2rem .55rem;
    font-size: .72rem;
    font-weight: 600;
    color: var(--text-muted);
}

/* ===== FILE PREVIEW ===== */
.file-preview {
    display: none;
    align-items: center;
    gap: .75rem;
    padding: .85rem 1rem;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    margin-top: .85rem;
}
.file-preview.show { display: flex; }
.file-preview-icon { font-size: 1.5rem; color: #2563eb; flex-shrink: 0; }
.file-preview-name { font-size: .875rem; font-weight: 600; color: #1e40af; flex: 1; }
.file-preview-size { font-size: .75rem; color: #3b82f6; }
.file-preview-remove {
    background: none; border: none; cursor: pointer;
    color: #93c5fd; font-size: .9rem; padding: .2rem;
    border-radius: 4px; transition: color .15s;
}
.file-preview-remove:hover { color: #1d4ed8; }

/* ===== SUBMIT BUTTON ===== */
.btn-import {
    width: 100%; margin-top: 1rem;
    padding: .75rem 1rem;
    background: var(--primary); color: #fff;
    border: none; border-radius: 10px;
    font-size: .95rem; font-weight: 600;
    cursor: pointer; transition: opacity .15s;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
}
.btn-import:hover { opacity: .9; }
.btn-import:disabled { opacity: .5; cursor: not-allowed; }

/* ===== TEMPLATE CARD ===== */
.template-card {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    border-radius: 12px;
    padding: 1.25rem;
    color: #fff;
    margin-bottom: 1rem;
}
.template-card-title { font-size: .85rem; font-weight: 600; margin-bottom: .35rem; opacity: .9; }
.template-card-desc  { font-size: .78rem; opacity: .75; margin-bottom: 1rem; line-height: 1.5; }
.btn-template {
    display: flex; align-items: center; gap: .5rem;
    background: rgba(255,255,255,.15);
    border: 1px solid rgba(255,255,255,.3);
    color: #fff; border-radius: 8px;
    padding: .55rem .9rem; font-size: .85rem; font-weight: 600;
    cursor: pointer; text-decoration: none; transition: background .15s;
    width: 100%; justify-content: center;
}
.btn-template:hover { background: rgba(255,255,255,.25); }

/* ===== PANDUAN ===== */
.panduan-list { list-style: none; padding: 0; margin: 0; }
.panduan-list li {
    display: flex; gap: .75rem; align-items: flex-start;
    padding: .6rem 0; border-bottom: 1px solid var(--border);
    font-size: .82rem; color: var(--text-muted);
}
.panduan-list li:last-child { border-bottom: none; }
.panduan-num {
    width: 22px; height: 22px; border-radius: 50%;
    background: var(--primary); color: #fff;
    font-size: .7rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-top: 1px;
}

/* ===== KOLUM TABLE ===== */
.col-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
.col-table th {
    background: var(--bg); padding: .5rem .75rem;
    text-align: left; font-weight: 600;
    color: var(--text-muted); font-size: .72rem;
    text-transform: uppercase; letter-spacing: .04em;
    border-bottom: 1px solid var(--border);
}
.col-table td {
    padding: .5rem .75rem;
    border-bottom: 1px solid var(--border);
    color: var(--text);
}
.col-table tr:last-child td { border-bottom: none; }
.col-wajib {
    display: inline-block;
    width: 6px; height: 6px;
    background: #ef4444;
    border-radius: 50%;
    margin-left: 3px;
    vertical-align: middle;
}

/* ===== REKOD TERKINI ===== */
.history-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.history-table th {
    background: var(--bg); padding: .5rem .75rem;
    text-align: left; font-weight: 600; font-size: .72rem;
    text-transform: uppercase; letter-spacing: .04em;
    color: var(--text-muted); border-bottom: 1px solid var(--border);
}
.history-table td { padding: .6rem .75rem; border-bottom: 1px solid var(--border); color: var(--text); }
.history-table tr:last-child td { border-bottom: none; }
.history-table tr:hover td { background: var(--bg); }

/* ===== BADGE ===== */
.badge {
    display: inline-flex; align-items: center; gap: .2rem;
    padding: .2rem .55rem; border-radius: 999px;
    font-size: .7rem; font-weight: 600;
}
.badge.green { background: #dcfce7; color: #15803d; }
.badge.gray  { background: #f3f4f6; color: #6b7280; }

/* ===== ALERTS ===== */
.alert { padding: .85rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: .875rem; display: flex; align-items: flex-start; gap: .6rem; }
.alert.success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.alert.error   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

/* ===== PROGRESS ===== */
.progress-wrap { display: none; margin-top: 1rem; }
.progress-wrap.show { display: block; }
.progress-bar-bg { background: var(--border); border-radius: 999px; height: 6px; overflow: hidden; }
.progress-bar-fill { height: 100%; background: var(--primary); border-radius: 999px; width: 0%; transition: width .3s; }
.progress-label { font-size: .75rem; color: var(--text-muted); margin-top: .35rem; text-align: center; }
</style>
@endpush

@section('content')

{{-- ALERTS --}}
@if(session('success'))
<div class="alert success"><i class="ti ti-circle-check" style="font-size:1.1rem;flex-shrink:0;"></i> <span>{{ session('success') }}</span></div>
@endif
@if(session('error'))
<div class="alert error"><i class="ti ti-alert-circle" style="font-size:1.1rem;flex-shrink:0;"></i> <span>{{ session('error') }}</span></div>
@endif
@if($errors->any())
<div class="alert error">
    <i class="ti ti-alert-circle" style="font-size:1.1rem;flex-shrink:0;"></i>
    <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
</div>
@endif

<div style="margin-bottom:1.25rem;">
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;">Import Excel</h2>
    <p class="page-desc">Muat naik data pelajar dari fail Excel atau CSV ke dalam sistem.</p>
</div>

<div class="import-grid">

    {{-- ===== KIRI: UPLOAD + REKOD ===== --}}
    <div>

        {{-- Upload Card --}}
        <div class="card" style="margin-bottom:1.25rem;">
            <div class="card-head">
                <i class="ti ti-file-upload" style="color:var(--primary);"></i>
                <h3>Muat Naik Fail Excel</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('import.process') }}" enctype="multipart/form-data" id="form-import">
                @csrf

                {{-- Dropzone --}}
                <div class="dropzone" id="dropzone" onclick="document.getElementById('input-fail').click()">
                    <input type="file" name="fail_excel" id="input-fail" accept=".xlsx,.xls,.csv" style="display:none">
                    <i class="ti ti-cloud-upload dropzone-icon"></i>
                    <div class="dropzone-title">Seret & lepas fail di sini</div>
                    <div class="dropzone-sub">atau klik untuk pilih fail dari komputer anda</div>
                    <div class="dropzone-formats">
                        <span class="format-badge">.XLSX</span>
                        <span class="format-badge">.XLS</span>
                        <span class="format-badge">.CSV</span>
                        <span class="format-badge">Maks 5MB</span>
                    </div>
                </div>

                {{-- File Preview --}}
                <div class="file-preview" id="file-preview">
                    <i class="ti ti-file-spreadsheet file-preview-icon"></i>
                    <div>
                        <div class="file-preview-name" id="preview-name">—</div>
                        <div class="file-preview-size" id="preview-size">—</div>
                    </div>
                    <button type="button" class="file-preview-remove" onclick="clearFile()" title="Buang fail">
                        <i class="ti ti-x"></i>
                    </button>
                </div>

                {{-- Progress --}}
                <div class="progress-wrap" id="progress-wrap">
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" id="progress-fill"></div>
                    </div>
                    <div class="progress-label" id="progress-label">Memproses...</div>
                </div>

                <button type="submit" class="btn-import" id="btn-import" disabled>
                    <i class="ti ti-database-import"></i> Import Data ke Sistem
                </button>
                </form>
            </div>
        </div>

        {{-- Rekod Pelajar Terkini --}}
        <div class="card">
            <div class="card-head">
                <i class="ti ti-clock" style="color:var(--text-muted);"></i>
                <h3>Pelajar Terkini dalam Sistem</h3>
                <a href="{{ route('pelajar.index') }}" style="margin-left:auto;font-size:.8rem;color:var(--primary);text-decoration:none;">
                    Lihat Semua <i class="ti ti-arrow-right"></i>
                </a>
            </div>
            @if(!empty($history))
            <table class="history-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Pelajar</th>
                        <th>No. Matrik</th>
                        <th>Semester</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $i => $p)
                    <tr>
                        <td style="color:var(--text-muted);font-size:.75rem;">{{ $i + 1 }}</td>
                        <td style="font-weight:600;">{{ $p['nama_pelajar'] ?? '—' }}</td>
                        <td style="color:var(--text-muted);">{{ $p['no_matrik'] ?? '—' }}</td>
                        <td>{{ $p['semester'] ?? '—' }}</td>
                        <td>
                            @if(($p['status_pengajian'] ?? '') === 'Aktif')
                                <span class="badge green"><i class="ti ti-circle-filled" style="font-size:.5rem;"></i> Aktif</span>
                            @else
                                <span class="badge gray">{{ $p['status_pengajian'] ?? '—' }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div style="padding:2rem;text-align:center;color:var(--text-muted);">
                <i class="ti ti-users" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.4;"></i>
                <p style="margin:0;font-size:.875rem;">Tiada data pelajar lagi.</p>
            </div>
            @endif
        </div>

    </div>

    {{-- ===== KANAN: PANDUAN + TEMPLAT ===== --}}
    <div>

        {{-- Templat Download --}}
        <div class="template-card">
            <div class="template-card-title"><i class="ti ti-file-download"></i> Templat Excel</div>
            <div class="template-card-desc">
                Muat turun templat CSV ini sebelum mengisi data. Pastikan format kolum betul supaya import berjaya.
            </div>
            <a href="{{ route('import.template') }}" class="btn-template">
                <i class="ti ti-download"></i> Muat Turun Templat
            </a>
        </div>

        {{-- Panduan --}}
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-head">
                <i class="ti ti-info-circle" style="color:var(--primary);"></i>
                <h3>Panduan Import</h3>
            </div>
            <div class="card-body" style="padding-top:.75rem;padding-bottom:.75rem;">
                <ul class="panduan-list">
                    <li>
                        <span class="panduan-num">1</span>
                        Muat turun templat CSV di atas dan buka dengan Excel atau Google Sheets.
                    </li>
                    <li>
                        <span class="panduan-num">2</span>
                        Isi data pelajar mengikut format kolum. Jangan ubah nama header.
                    </li>
                    <li>
                        <span class="panduan-num">3</span>
                        Simpan sebagai <strong>.csv</strong>, <strong>.xlsx</strong> atau <strong>.xls</strong>.
                    </li>
                    <li>
                        <span class="panduan-num">4</span>
                        Muat naik fail menggunakan borang di sebelah kiri.
                    </li>
                    <li>
                        <span class="panduan-num">5</span>
                        Sistem akan memproses dan memasukkan data ke Supabase secara automatik.
                    </li>
                </ul>
            </div>
        </div>

        {{-- Format Kolum --}}
        <div class="card">
            <div class="card-head">
                <i class="ti ti-table" style="color:var(--text-muted);"></i>
                <h3>Format Kolum</h3>
                <span style="margin-left:auto;font-size:.72rem;color:#ef4444;">● Wajib</span>
            </div>
            <table class="col-table">
                <thead>
                    <tr>
                        <th>Kolum</th>
                        <th>Nama Header</th>
                        <th>Contoh</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>A</td>
                        <td>Nama Penuh <span class="col-wajib"></span></td>
                        <td style="color:var(--text-muted);">Ahmad Faris</td>
                    </tr>
                    <tr>
                        <td>B</td>
                        <td>No. Matrik</td>
                        <td style="color:var(--text-muted);">D20231234</td>
                    </tr>
                    <tr>
                        <td>C</td>
                        <td>Program</td>
                        <td style="color:var(--text-muted);">Semester 3</td>
                    </tr>
                    <tr>
                        <td>D</td>
                        <td>No. Telefon</td>
                        <td style="color:var(--text-muted);">0123456789</td>
                    </tr>
                    <tr>
                        <td>E</td>
                        <td>Email</td>
                        <td style="color:var(--text-muted);">nama@email.com</td>
                    </tr>
                    <tr>
                        <td>F</td>
                        <td>Semester Semasa</td>
                        <td style="color:var(--text-muted);">Semester 3</td>
                    </tr>
                    <tr>
                        <td>G</td>
                        <td>Status Pengajian</td>
                        <td style="color:var(--text-muted);">Aktif</td>
                    </tr>
                    <tr>
                        <td>H</td>
                        <td>Tarikh Tamat Tajaan</td>
                        <td style="color:var(--text-muted);">2026-12-31</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
const inputFail   = document.getElementById('input-fail');
const dropzone    = document.getElementById('dropzone');
const filePreview = document.getElementById('file-preview');
const previewName = document.getElementById('preview-name');
const previewSize = document.getElementById('preview-size');
const btnImport   = document.getElementById('btn-import');
const formImport  = document.getElementById('form-import');

/* ===== FILE INPUT ===== */
inputFail.addEventListener('change', function () {
    if (this.files.length > 0) showPreview(this.files[0]);
});

function showPreview(file) {
    previewName.textContent = file.name;
    previewSize.textContent = formatSize(file.size);
    filePreview.classList.add('show');
    btnImport.disabled = false;
}

function clearFile() {
    inputFail.value = '';
    filePreview.classList.remove('show');
    btnImport.disabled = true;
}

function formatSize(bytes) {
    if (bytes < 1024)       return bytes + ' B';
    if (bytes < 1024*1024)  return (bytes/1024).toFixed(1) + ' KB';
    return (bytes/(1024*1024)).toFixed(1) + ' MB';
}

/* ===== DRAG & DROP ===== */
dropzone.addEventListener('dragover', e => {
    e.preventDefault();
    dropzone.classList.add('drag-over');
});
dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
dropzone.addEventListener('drop', e => {
    e.preventDefault();
    dropzone.classList.remove('drag-over');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        // Assign to input
        const dt = new DataTransfer();
        dt.items.add(files[0]);
        inputFail.files = dt.files;
        showPreview(files[0]);
    }
});

/* ===== SUBMIT WITH PROGRESS ===== */
formImport.addEventListener('submit', function () {
    if (inputFail.files.length === 0) return;
    btnImport.disabled = true;
    btnImport.innerHTML = '<i class="ti ti-loader-2" style="animation:spin 1s linear infinite;"></i> Memproses...';

    const pw = document.getElementById('progress-wrap');
    const pf = document.getElementById('progress-fill');
    const pl = document.getElementById('progress-label');
    pw.classList.add('show');

    let pct = 0;
    const iv = setInterval(() => {
        pct = Math.min(pct + Math.random() * 15, 90);
        pf.style.width = pct + '%';
        pl.textContent = 'Memproses data... ' + Math.round(pct) + '%';
    }, 300);

    // Store interval in window to clear if needed
    window._importInterval = iv;
});

/* CSS spin */
const style = document.createElement('style');
style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
document.head.appendChild(style);
</script>
@endpush
