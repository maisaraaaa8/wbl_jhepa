@extends('layouts.app')

@section('title', 'Prestasi Akademik')
@section('page-title', 'Prestasi Akademik')



@push('styles')
<style>
/* ── KAD STATISTIK ─────────────────────────────── */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: box-shadow .15s, transform .15s;
}
.stat-card:hover {
    box-shadow: 0 4px 18px rgba(0,0,0,.08);
    transform: translateY(-1px);
    position: relative;
    z-index: 1;
}
.stat-icon {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; flex-shrink: 0;
}
.stat-icon.blue   { background: #e6f1fb; color: #185fa5; }
.stat-icon.purple { background: #f3effe; color: #6234a1; }
.stat-icon.green  { background: #eaf3de; color: #3b6d11; }
.stat-icon.yellow { background: #faeeda; color: #854f0b; }
.stat-icon.red    { background: #fcebeb; color: #a32d2d; }
.stat-label { font-size: 11px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
.stat-value { font-size: 28px; font-weight: 700; color: var(--text); line-height: 1; margin-top: 3px; }
.stat-sub   { font-size: 11px; color: var(--text-muted); margin-top: 3px; }

/* ── SECTION BOX ─────────────────────────────────── */
.section-box {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    margin-bottom: 20px;
    overflow: hidden;
}
.section-box-head {
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    background: var(--surface-2);
}
.section-box-title {
    font-size: 13px; font-weight: 600;
    color: var(--text);
    display: flex; align-items: center; gap: 7px;
}
.section-box-title i { color: var(--text-muted); font-size: 15px; }
.section-box-body { padding: 20px; position: relative; z-index: 2; }

/* ── FORM ────────────────────────────────────────── */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 14px;
    align-items: end;
}
@media (max-width: 768px) { .form-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 480px) { .form-grid { grid-template-columns: 1fr; } }

.form-group { display: flex; flex-direction: column; gap: 5px; position: relative; z-index: 4; }
.form-label { font-size: 12px; font-weight: 600; color: var(--text-2); }
.form-control {
    position: relative;
    z-index: 5;
    padding: 8px 11px;
    border: 1px solid var(--border-strong);
    border-radius: var(--radius);
    font-size: 13px; color: var(--text);
    background: var(--surface);
    transition: border-color .15s, box-shadow .15s;
    width: 100%;
}
.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(30,58,95,.08);
}
.form-control::placeholder { color: var(--text-muted); }
select.form-control { cursor: pointer; }

/* GPA preview pill */
.gpa-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 600;
    margin-top: 4px; transition: all .2s;
}
.gpa-pill.green  { background: #eaf3de; color: #3b6d11; }
.gpa-pill.yellow { background: #faeeda; color: #854f0b; }
.gpa-pill.red    { background: #fcebeb; color: #a32d2d; }
.gpa-pill.muted  { background: var(--surface-2); color: var(--text-muted); }

/* ── JADUAL ──────────────────────────────────────── */
.table-wrap table { width: 100%; border-collapse: collapse; }
.table-wrap th {
    padding: 10px 14px;
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .05em;
    color: var(--text-muted);
    background: var(--surface-2);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
.table-wrap td {
    padding: 11px 14px;
    font-size: 13px; color: var(--text);
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}
.table-wrap tbody tr:last-child td { border-bottom: none; }
.table-wrap tbody tr { transition: background .1s; }
.table-wrap tbody tr:hover { background: var(--surface-2); }

.sem-val {
    font-size: 13px;
    font-variant-numeric: tabular-nums;
    text-align: center;
}
.sem-val.empty { color: var(--text-muted); }

/* CGPA bar */
.cgpa-wrap { display: flex; align-items: center; gap: 8px; }
.cgpa-bar-bg {
    flex: 1; height: 6px;
    background: var(--border);
    border-radius: 20px; overflow: hidden;
}
.cgpa-bar-fill { height: 100%; border-radius: 20px; transition: width .5s ease; }
.fill-green  { background: linear-gradient(90deg, #68d391, #38a169); }
.fill-yellow { background: linear-gradient(90deg, #f6ad55, #dd6b20); }
.fill-red    { background: linear-gradient(90deg, #fc8181, #e53e3e); }
.cgpa-num {
    font-size: 14px; font-weight: 700;
    font-variant-numeric: tabular-nums;
    min-width: 36px; text-align: right;
}
.cgpa-num.green  { color: #3b6d11; }
.cgpa-num.yellow { color: #854f0b; }
.cgpa-num.red    { color: #a32d2d; }

/* ── FILTER BAR ──────────────────────────────────── */
.filter-bar { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.search-wrap {
    display: flex; align-items: center; gap: 7px;
    border: 1px solid var(--border-strong);
    border-radius: var(--radius);
    padding: 6px 10px;
    background: var(--surface);
    min-width: 200px;
}
.search-wrap i { color: var(--text-muted); font-size: 14px; }
.search-wrap input {
    border: none; background: transparent;
    outline: none; font-size: 13px; color: var(--text); width: 100%;
}

/* ── MODAL ───────────────────────────────────────── */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.45); z-index: 400;
    align-items: center; justify-content: center; padding: 20px;
}
.modal-overlay.open { display: flex; }
.modal {
    background: var(--surface);
    border-radius: var(--radius-lg);
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    width: 100%; max-width: 520px;
    animation: popIn .2s cubic-bezier(.34,1.56,.64,1);
}
@keyframes popIn {
    from { opacity:0; transform: scale(.94) translateY(10px); }
    to   { opacity:1; transform: scale(1) translateY(0); }
}
.modal-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; border-bottom: 1px solid var(--border);
}
.modal-head h3 { font-size: 15px; font-weight: 600; }
.close-btn {
    background: none; border: none; color: var(--text-muted);
    cursor: pointer; font-size: 18px;
    display: flex; align-items: center;
    border-radius: var(--radius); padding: 4px;
    transition: background .15s, color .15s;
}
.close-btn:hover { background: var(--surface-2); color: var(--text); }
.modal-body { padding: 20px; }
.btn-row { display: flex; gap: 8px; justify-content: flex-end; margin-top: 18px; }

/* Detail grid dalam modal */
.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 10px; margin-top: 14px;
}
.detail-sem {
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 14px 12px; text-align: center;
}
.detail-sem-label {
    font-size: 10px; color: var(--text-muted);
    font-weight: 600; text-transform: uppercase;
    letter-spacing: .04em; margin-bottom: 6px;
}
.detail-sem-gpa { font-size: 26px; font-weight: 700; }
.detail-sem-actions { display: flex; gap: 4px; justify-content: center; margin-top: 10px; }

/* CGPA summary dalam modal */
.cgpa-summary {
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 14px 18px;
    display: flex; align-items: center;
    justify-content: space-between;
    margin-bottom: 4px;
}
.cgpa-big { font-size: 36px; font-weight: 800; line-height: 1; }

/* Empty state */
.empty-state {
    text-align: center; padding: 56px 20px;
    color: var(--text-muted);
}
.empty-state i { font-size: 48px; display: block; margin-bottom: 12px; opacity: .3; }
.empty-state p  { font-size: 13px; }
</style>
@endpush

@section('content')

{{-- ═══════════ STATISTIK ═══════════ --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="ti ti-clipboard-list"></i></div>
        <div>
            <div class="stat-label">Jumlah Rekod</div>
            <div class="stat-value">{{ $statistik['jumlah'] }}</div>
            <div class="stat-sub">Keseluruhan semester</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="ti ti-trending-up"></i></div>
        <div>
            <div class="stat-label">Purata GPA</div>
            <div class="stat-value">{{ number_format($statistik['purata_gpa'], 2) }}</div>
            <div class="stat-sub">Semua pelajar</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="ti ti-award"></i></div>
        <div>
            <div class="stat-label">Cemerlang</div>
            <div class="stat-value">{{ $statistik['cemerlang'] }}</div>
            <div class="stat-sub">GPA ≥ 3.50</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="ti ti-chart-bar"></i></div>
        <div>
            <div class="stat-label">Memuaskan</div>
            <div class="stat-value">{{ $statistik['memuaskan'] }}</div>
            <div class="stat-sub">GPA 3.00 – 3.49</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="ti ti-alert-triangle"></i></div>
        <div>
            <div class="stat-label">Perlu Perhatian</div>
            <div class="stat-value">{{ $statistik['perlu_perhatian'] }}</div>
            <div class="stat-sub">GPA &lt; 3.00</div>
        </div>
    </div>
</div>

{{-- ═══════════ FORM TAMBAH ═══════════ --}}
<div class="section-box">
    <div class="section-box-head">
        <div class="section-box-title">
            <i class="ti ti-pencil-plus"></i> Tambah Keputusan Semester
        </div>
        <div style="font-size:12px;color:var(--text-muted)">Rekod keputusan peperiksaan semester pelajar</div>
    </div>
    <div class="section-box-body">
        <form method="POST" action="{{ route('prestasi.store') }}">
            @csrf
            <div class="form-grid">

                {{-- Pelajar --}}
                <div class="form-group">
                    <label class="form-label">Pelajar</label>
                    <select name="id_pelajar" class="form-control" required>
                        <option value="">— Pilih Pelajar —</option>
                        @foreach($senarai_pelajar as $p)
                            <option value="{{ $p['id_pelajar'] }}"
                                {{ old('id_pelajar') == $p['id_pelajar'] ? 'selected' : '' }}>
                                {{ $p['nama_pelajar'] }}
                                @if(!empty($p['no_matrik'])) ({{ $p['no_matrik'] }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Semester --}}
                <div class="form-group">
                    <label class="form-label">Semester</label>
                    <select name="semester" class="form-control" required>
                        <option value="">— Pilih Semester —</option>
                        @foreach(\App\Services\PrestasiService::SENARAI_SEMESTER as $sem)
                            <option value="{{ $sem }}" {{ old('semester') == $sem ? 'selected' : '' }}>
                                {{ $sem }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- GPA --}}
                <div class="form-group">
                    <label class="form-label">GPA</label>
                    <input
                        type="number" name="gpa"
                        class="form-control"
                        placeholder="cth: 3.45"
                        min="0" max="4" step="0.01"
                        value="{{ old('gpa') }}"
                        required
                        oninput="previewGpa(this.value)"
                        id="inp-gpa"
                    >
                    <div>
                        <span class="gpa-pill muted" id="gpa-pill">
                            <i class="ti ti-info-circle"></i> 0.00 – 4.00
                        </span>
                    </div>
                </div>

            </div>

            <div style="margin-top:16px; display:flex; justify-content:flex-end">
                <button type="submit" class="btn primary">
                    <i class="ti ti-device-floppy"></i> Simpan Rekod
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════ JADUAL REKOD ═══════════ --}}
<div class="section-box">
    <div class="section-box-head">
        <div class="section-box-title">
            <i class="ti ti-table"></i> Rekod Prestasi Semua Pelajar
        </div>

        <form method="GET" action="{{ route('prestasi.index') }}">
            <div class="filter-bar">
                <div class="search-wrap">
                    <i class="ti ti-search"></i>
                    <input name="cari" placeholder="Cari nama / no. matrik..."
                           value="{{ request('cari') }}" onchange="this.form.submit()">
                </div>
                <select name="semester" class="topbar-btn" style="cursor:pointer" onchange="this.form.submit()">
                    <option value="">Semua Semester</option>
                    @foreach(\App\Services\PrestasiService::SENARAI_SEMESTER as $sem)
                        <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>
                            {{ $sem }}
                        </option>
                    @endforeach
                </select>
                @if(request()->hasAny(['cari', 'semester']))
                    <a href="{{ route('prestasi.index') }}" class="topbar-btn">
                        <i class="ti ti-x"></i> Reset
                    </a>
                @endif
            </div>
        </form>

        <div style="font-size:12px;color:var(--text-muted)">
            {{ count($ringkasan) }} pelajar
        </div>
    </div>

    <div class="table-wrap" style="border:none;border-radius:0;margin-bottom:0">
        <table>
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>Pelajar</th>
                    <th style="text-align:center">Sem 1</th>
                    <th style="text-align:center">Sem 2</th>
                    <th style="text-align:center">Sem 3</th>
                    <th style="text-align:center">Sem 4</th>
                    <th style="text-align:center">Sem 5</th>
                    <th style="text-align:center">Sem 6</th>
                    <th>CGPA</th>
                    <th>Status</th>
                    <th width="120">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ringkasan as $i => $p)
                @php
                    $cgpa = $p['cgpa'];
                    $colorClass = match(true) {
                        $cgpa === null      => 'muted',
                        $cgpa >= 3.50       => 'green',
                        $cgpa >= 3.00       => 'yellow',
                        default             => 'red',
                    };
                    $fillClass = match($colorClass) {
                        'green'  => 'fill-green',
                        'yellow' => 'fill-yellow',
                        'red'    => 'fill-red',
                        default  => '',
                    };
                    $badgeClass = match($colorClass) {
                        'green'  => 'green',
                        'yellow' => 'warn',
                        'red'    => 'red',
                        default  => 'gray',
                    };
                @endphp
                <tr>
                    <td style="color:var(--text-muted);font-size:12px">{{ $i + 1 }}</td>
                    <td>
                        <div style="font-weight:500;font-size:13px">{{ $p['nama_pelajar'] }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $p['no_matrik'] }}</div>
                    </td>

                    @foreach(\App\Services\PrestasiService::SENARAI_SEMESTER as $sem)
                    @php $gv = $p['semester'][$sem] ?? null; @endphp
                    <td class="sem-val {{ $gv === null ? 'empty' : '' }}">
                        @if($gv !== null)
                            <span title="{{ $sem }}">{{ number_format($gv, 2) }}</span>
                        @else —
                        @endif
                    </td>
                    @endforeach

                    <td style="min-width:140px">
                        @if($cgpa !== null)
                        <div class="cgpa-wrap">
                            <div class="cgpa-bar-bg">
                                <div class="cgpa-bar-fill {{ $fillClass }}"
                                     style="width:{{ min(100, ($cgpa/4)*100) }}%"></div>
                            </div>
                            <span class="cgpa-num {{ $colorClass }}">{{ number_format($cgpa, 2) }}</span>
                        </div>
                        @else
                            <span style="color:var(--text-muted)">—</span>
                        @endif
                    </td>

                    <td>
                        @if($cgpa !== null)
                            <span class="badge {{ $badgeClass }}">{{ $p['status'] }}</span>
                        @else
                            <span style="color:var(--text-muted);font-size:12px">Tiada rekod</span>
                        @endif
                    </td>

                    <td>
                        <div style="display:flex;gap:4px;align-items:center">
                            <button
                                class="btn sm"
                                onclick="bukaDetail('{{ $p['id_pelajar'] }}', @js($p['nama_pelajar']))"
                                title="Lihat sejarah & edit prestasi"
                                style="padding:4px 8px"
                            >
                                <i class="ti ti-history"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11">
                        <div class="empty-state">
                            <i class="ti ti-chart-bar-off"></i>
                            <p>Tiada rekod prestasi ditemui.</p>
                            @if(request()->hasAny(['cari','semester']))
                                <a href="{{ route('prestasi.index') }}"
                                   style="color:var(--primary);font-size:13px;margin-top:6px;display:inline-block">
                                    Reset carian
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════ MODAL: DETAIL PELAJAR ═══════════ --}}
<div class="modal-overlay" id="modal-detail">
    <div class="modal" style="max-width:580px">
        <div class="modal-head">
            <div>
                <h3 id="modal-detail-nama">Detail Prestasi</h3>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px">Sejarah GPA semua semester · Klik Edit atau Padam pada semester</div>
            </div>
            <button class="close-btn" onclick="closeModal('modal-detail')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="modal-body" id="modal-detail-body">
            <div style="text-align:center;padding:30px;color:var(--text-muted)">
                <i class="ti ti-loader" style="font-size:28px"></i>
                <p style="margin-top:8px;font-size:13px">Memuatkan...</p>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════ MODAL: EDIT GPA ═══════════ --}}
<div class="modal-overlay" id="modal-edit">
    <div class="modal" style="max-width:380px">
        <div class="modal-head">
            <h3>Kemaskini GPA</h3>
            <button class="close-btn" onclick="closeModal('modal-edit')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="modal-body">
            <form method="POST" id="form-edit">
                @csrf @method('PUT')
                <p id="edit-label" style="font-size:13px;color:var(--text-2);margin-bottom:14px"></p>
                <div class="form-group">
                    <label class="form-label">GPA Baru</label>
                    <input type="number" name="gpa" id="edit-gpa" class="form-control"
                           placeholder="0.00 – 4.00" min="0" max="4" step="0.01" required
                           oninput="previewGpaEdit(this.value)">
                    <div>
                        <span class="gpa-pill muted" id="edit-gpa-pill">
                            <i class="ti ti-info-circle"></i> 0.00 – 4.00
                        </span>
                    </div>
                </div>
                <div class="btn-row">
                    <button type="button" class="btn" onclick="closeModal('modal-edit')">Batal</button>
                    <button type="submit" class="btn primary">
                        <i class="ti ti-check"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════ MODAL: PADAM ═══════════ --}}
<div class="modal-overlay" id="modal-padam">
    <div class="modal" style="max-width:360px">
        <div class="modal-head">
            <h3>Sahkan Pemadaman</h3>
            <button class="close-btn" onclick="closeModal('modal-padam')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="padam-label" style="font-size:13px;margin-bottom:16px;line-height:1.6"></p>
            <form method="POST" id="form-padam">
                @csrf @method('DELETE')
                <div class="btn-row">
                    <button type="button" class="btn" onclick="closeModal('modal-padam')">Batal</button>
                    <button type="submit" class="btn danger">
                        <i class="ti ti-trash"></i> Ya, Padam
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── GPA preview pill (form tambah) ────────────────────
function previewGpa(val) { updatePill('gpa-pill', val); }
function previewGpaEdit(val) { updatePill('edit-gpa-pill', val); }

function updatePill(pillId, val) {
    const pill = document.getElementById(pillId);
    const gpa  = parseFloat(val);
    if (!pill) return;
    if (isNaN(gpa) || val === '') {
        pill.className = 'gpa-pill muted';
        pill.innerHTML = '<i class="ti ti-info-circle"></i> 0.00 – 4.00';
        return;
    }
    if (gpa < 0 || gpa > 4) {
        pill.className = 'gpa-pill red';
        pill.innerHTML = '<i class="ti ti-x"></i> Luar julat (0 – 4)';
        return;
    }
    if (gpa >= 3.50) {
        pill.className = 'gpa-pill green';
        pill.innerHTML = '<i class="ti ti-award"></i> Cemerlang (≥3.50)';
    } else if (gpa >= 3.00) {
        pill.className = 'gpa-pill yellow';
        pill.innerHTML = '<i class="ti ti-chart-bar"></i> Memuaskan (3.00–3.49)';
    } else {
        pill.className = 'gpa-pill red';
        pill.innerHTML = '<i class="ti ti-alert-triangle"></i> Perlu Perhatian (<3.00)';
    }
}

// ── Buka modal detail ──────────────────────────────────
async function bukaDetail(pelajarId, nama) {
    document.getElementById('modal-detail-nama').textContent = nama;
    document.getElementById('modal-detail-body').innerHTML =
        '<div style="text-align:center;padding:30px;color:var(--text-muted)">' +
        '<i class="ti ti-loader" style="font-size:28px"></i>' +
        '<p style="margin-top:8px;font-size:13px">Memuatkan...</p></div>';
    openModal('modal-detail');

    try {
        const res  = await fetch(`/prestasi/pelajar/${pelajarId}`);
        const data = await res.json();
        renderDetail(data, nama);
    } catch (e) {
        document.getElementById('modal-detail-body').innerHTML =
            '<p style="color:#a32d2d;padding:20px;font-size:13px">Gagal memuatkan data.</p>';
    }
}

function renderDetail(data, nama) {
    if (!data.length) {
        document.getElementById('modal-detail-body').innerHTML =
            '<div class="empty-state"><i class="ti ti-chart-bar-off"></i>' +
            '<p>Tiada rekod semester untuk pelajar ini.</p></div>';
        return;
    }

    // Kira CGPA dari purata GPA
    const gpas = data.map(r => parseFloat(r.gpa));
    const cgpa = (gpas.reduce((a, b) => a + b, 0) / gpas.length).toFixed(2);
    const cgpaNum = parseFloat(cgpa);
    const cgpaClr = cgpaNum >= 3.50 ? '#3b6d11' : cgpaNum >= 3.00 ? '#854f0b' : '#a32d2d';
    const badgeCls = cgpaNum >= 3.50 ? 'green' : cgpaNum >= 3.00 ? 'warn' : 'red';
    const statusLabel = cgpaNum >= 3.50 ? 'Cemerlang' : cgpaNum >= 3.00 ? 'Memuaskan' : 'Perlu Perhatian';

    let html = `
    <div class="cgpa-summary">
        <div>
            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em">
                CGPA Keseluruhan
            </div>
            <div class="cgpa-big" style="color:${cgpaClr}">${cgpa}</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:3px">
                ${data.length} semester direkodkan
            </div>
        </div>
        <span class="badge ${badgeCls}" style="font-size:12px;padding:5px 14px">${statusLabel}</span>
    </div>
    <div class="detail-grid">`;

    data.forEach(r => {
        const g   = parseFloat(r.gpa);
        const clr = g >= 3.50 ? '#3b6d11' : g >= 3.00 ? '#854f0b' : '#a32d2d';
        const bgClr = g >= 3.50 ? '#eaf3de' : g >= 3.00 ? '#faeeda' : '#fcebeb';
        html += `
        <div class="detail-sem" style="background:${bgClr};border-color:${clr}33">
            <div class="detail-sem-label">${r.semester}</div>
            <div class="detail-sem-gpa" style="color:${clr}">${g.toFixed(2)}</div>
            <div class="detail-sem-actions" style="margin-top:10px;display:flex;gap:4px;justify-content:center">
                <button onclick="bukaEdit('${r.id}','${r.semester}','${g}')"
                        class="btn sm" style="padding:4px 10px;font-size:11px" title="Edit GPA">
                    <i class="ti ti-pencil"></i> Edit
                </button>
                <button onclick="bukaPadam('${r.id}','${r.semester}',@js($p['nama_pelajar'] ?? ''))"
                        class="btn sm danger" style="padding:4px 8px;font-size:11px" title="Padam rekod">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        </div>`;
    });

    html += '</div>';
    document.getElementById('modal-detail-body').innerHTML = html;
}

// ── Edit modal ─────────────────────────────────────────
function bukaEdit(id, semester, gpa) {
    document.getElementById('edit-label').textContent = `Kemaskini GPA untuk ${semester}`;
    document.getElementById('edit-gpa').value         = gpa;
    document.getElementById('form-edit').action       = `/prestasi/${id}`;
    updatePill('edit-gpa-pill', gpa);
    closeModal('modal-detail');
    openModal('modal-edit');
}

// ── Padam modal ────────────────────────────────────────
function bukaPadam(id, semester, nama) {
    document.getElementById('padam-label').innerHTML =
        `Anda pasti mahu memadam rekod <strong>${semester}</strong> bagi pelajar <strong>${nama}</strong>?
         <br><span style="color:#a32d2d;font-size:12px">Tindakan ini tidak boleh dibatalkan.</span>`;
    document.getElementById('form-padam').action = `/prestasi/${id}`;
    closeModal('modal-detail');
    openModal('modal-padam');
}
</script>
@endpush
