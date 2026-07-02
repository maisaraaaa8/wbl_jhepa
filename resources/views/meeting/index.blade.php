@extends('layouts.app')
@section('title','Meeting Record')
@section('page-title','Meeting Record')

@section('topbar-actions')
    @if(session('peranan') === 'admin')
    <button class="topbar-btn primary" onclick="bukaModalTambah()">
        <i class="ti ti-plus"></i> Rekod Pertemuan
    </button>
    @endif
@endsection

@push('styles')
<style>
/* ===== STATS ===== */
.mr-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.mr-stat {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.1rem 1.2rem;
    display: flex;
    align-items: center;
    gap: .9rem;
    transition: box-shadow .2s;
}
.mr-stat:hover { box-shadow: 0 4px 18px rgba(0,0,0,.07); }
.mr-stat-icon {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; flex-shrink: 0;
}
.mr-stat-icon.blue   { background: #eff6ff; color: #2563eb; }
.mr-stat-icon.green  { background: #dcfce7; color: #16a34a; }
.mr-stat-icon.purple { background: #f5f3ff; color: #7c3aed; }
.mr-stat-icon.amber  { background: #fffbeb; color: #d97706; }
.mr-stat-label { font-size: .7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 2px; }
.mr-stat-value { font-size: 1.5rem; font-weight: 700; color: var(--text); line-height: 1.1; }
.mr-stat-sub   { font-size: .7rem; color: var(--text-muted); margin-top: 1px; }

/* ===== FILTER BAR ===== */
.filter-bar {
    display: flex; gap: .65rem; align-items: center;
    flex-wrap: wrap; margin-bottom: 1rem;
}
.filter-bar select,
.filter-bar input[type="text"] {
    padding: .45rem .8rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: .85rem;
    background: var(--surface);
    color: var(--text);
}
.btn-reset {
    padding: .45rem .9rem; border-radius: 8px;
    border: 1px solid var(--border); background: var(--surface);
    color: var(--text-muted); cursor: pointer; font-size: .85rem;
    display: inline-flex; align-items: center; gap: .35rem;
    text-decoration: none; transition: all .15s;
}
.btn-reset:hover { border-color: #ef4444; color: #ef4444; }

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

.name-cell { display: flex; flex-direction: column; }
.name-main { font-weight: 600; }
.name-sub  { font-size: .75rem; color: var(--text-muted); margin-top: 1px; }

.sesi-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 50%;
    background: var(--bg); border: 1px solid var(--border);
    font-weight: 700; font-size: .85rem;
}

/* ===== BADGES ===== */
.badge {
    display: inline-flex; align-items: center; gap: .25rem;
    padding: .25rem .65rem; border-radius: 999px;
    font-size: .72rem; font-weight: 600; white-space: nowrap;
}
.badge.blue   { background: #eff6ff; color: #1d4ed8; }
.badge.green  { background: #dcfce7; color: #15803d; }
.badge.amber  { background: #fef3c7; color: #b45309; }
.badge.gray   { background: #f3f4f6; color: #6b7280; }

/* ===== ACTIONS ===== */
.actions { display: flex; gap: .4rem; }
.btn { display: inline-flex; align-items: center; gap: .35rem; padding: .5rem 1rem; border-radius: 8px; border: 1px solid var(--border); cursor: pointer; font-size: .875rem; font-weight: 500; transition: all .15s; background: var(--surface); color: var(--text); }
.btn.primary { background: var(--primary); color: #fff; border-color: var(--primary); }
.btn.primary:hover { opacity: .9; }
.btn:hover { background: var(--bg); }
.btn.sm {
    padding: .35rem .5rem; border-radius: 7px; border: 1px solid var(--border);
    background: var(--surface); cursor: pointer; font-size: .85rem;
    color: var(--text-muted); transition: all .15s; display: inline-flex; align-items: center;
}
.btn.sm:hover        { background: var(--bg); color: var(--text); border-color: var(--primary); }
.btn.sm.danger:hover { background: #fee2e2; color: #b91c1c; border-color: #fca5a5; }

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
.modal-close { background: none; border: none; cursor: pointer; font-size: 1.1rem; color: var(--text-muted); border-radius: 6px; padding: .2rem .4rem; }
.modal-close:hover { background: var(--bg); }
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
.form-group textarea { resize: vertical; min-height: 80px; }

/* alert */
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
<div class="mr-stats">
    <div class="mr-stat">
        <div class="mr-stat-icon blue"><i class="ti ti-calendar-event"></i></div>
        <div>
            <div class="mr-stat-label">Jumlah Pertemuan</div>
            <div class="mr-stat-value">{{ $stats['jumlah_pertemuan'] ?? 0 }}</div>
            <div class="mr-stat-sub">semua masa</div>
        </div>
    </div>
    <div class="mr-stat">
        <div class="mr-stat-icon green"><i class="ti ti-calendar-plus"></i></div>
        <div>
            <div class="mr-stat-label">Bulan Ini</div>
            <div class="mr-stat-value">{{ $stats['bulan_ini'] ?? 0 }}</div>
            <div class="mr-stat-sub">{{ now()->format('F Y') }}</div>
        </div>
    </div>
    <div class="mr-stat">
        <div class="mr-stat-icon purple"><i class="ti ti-stack-2"></i></div>
        <div>
            <div class="mr-stat-label">Jumlah Sesi</div>
            <div class="mr-stat-value">{{ $stats['jumlah_sesi'] ?? 0 }}</div>
            <div class="mr-stat-sub">keseluruhan</div>
        </div>
    </div>
    <div class="mr-stat">
        <div class="mr-stat-icon amber"><i class="ti ti-users"></i></div>
        <div>
            <div class="mr-stat-label">Bersemuka</div>
            <div class="mr-stat-value">{{ $stats['bersemuka'] ?? 0 }}</div>
            <div class="mr-stat-sub">pertemuan fizikal</div>
        </div>
    </div>
</div>

{{-- FILTER --}}
<form method="GET" action="{{ route('meeting.index') }}" class="filter-bar" id="filterForm">
    <div class="search-box">
        <i class="ti ti-search"></i>
        <input type="text" name="cari" placeholder="Cari nama pelajar..." value="{{ request('cari') }}" oninput="cariLive(this.value)">
    </div>
    <select name="id_pelajar" onchange="document.getElementById('filterForm').submit()">
        <option value="">Semua Pelajar</option>
        @foreach($pelajarList ?? [] as $p)
        <option value="{{ $p['id_pelajar'] }}" {{ request('id_pelajar') == $p['id_pelajar'] ? 'selected' : '' }}>
            {{ $p['nama_pelajar'] }}
        </option>
        @endforeach
    </select>
    <select name="jenis" onchange="document.getElementById('filterForm').submit()">
        <option value="">Semua Jenis</option>
        <option value="Bersemuka"   {{ request('jenis') === 'Bersemuka'   ? 'selected' : '' }}>Bersemuka</option>
        <option value="Dalam Talian" {{ request('jenis') === 'Dalam Talian' ? 'selected' : '' }}>Dalam Talian</option>
        <option value="Telefon"     {{ request('jenis') === 'Telefon'     ? 'selected' : '' }}>Telefon</option>
    </select>
    @if(request('cari') || request('id_pelajar') || request('jenis'))
    <a href="{{ route('meeting.index') }}" class="btn-reset">
        <i class="ti ti-x"></i> Reset
    </a>
    @endif
</form>

{{-- TABLE --}}
<div class="table-wrap">
    <div class="table-header">
        <div class="section-title">
            <i class="ti ti-calendar-event"></i> Rekod Pertemuan
            <span class="rec-count">({{ count($meetings ?? []) }} rekod)</span>
        </div>
        <div class="search-box">
            <i class="ti ti-search"></i>
            <input type="text" placeholder="Cari cepat..." oninput="cariLive(this.value)">
        </div>
    </div>

    <table id="meetTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Tarikh</th>
                <th>Pelajar</th>
                <th>Keluarga Angkat</th>
                <th>Jenis</th>
                <th>Jumlah Sesi</th>
                <th>Nota</th>
                <th>Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($meetings ?? [] as $i => $m)
            <tr>
                <td style="color:var(--text-muted);font-size:.8rem;">{{ $i + 1 }}</td>

                {{-- Tarikh --}}
                <td>
                    <div style="font-weight:500;">
                        {{ !empty($m['tarikh_pertemuan']) ? \Carbon\Carbon::parse($m['tarikh_pertemuan'])->format('d M Y') : '—' }}
                    </div>
                    <div style="font-size:.72rem;color:var(--text-muted);">
                        {{ !empty($m['tarikh_pertemuan']) ? \Carbon\Carbon::parse($m['tarikh_pertemuan'])->diffForHumans() : '' }}
                    </div>
                </td>

                {{-- Pelajar --}}
                <td>
                    <div class="name-cell">
                        <span class="name-main">{{ $m['pelajar_nama'] ?? '—' }}</span>
                        @if(!empty($m['pelajar_matrik']))
                        <span class="name-sub">{{ $m['pelajar_matrik'] }}</span>
                        @endif
                    </div>
                </td>

                {{-- Keluarga --}}
                <td>{{ $m['keluarga_nama'] ?? '—' }}</td>

                {{-- Jenis --}}
                <td>
                    @php $jenis = $m['jenis_pertemuan'] ?? 'Bersemuka'; @endphp
                    @if(strtolower($jenis) === 'bersemuka')
                        <span class="badge blue"><i class="ti ti-users" style="font-size:.7rem;"></i> Bersemuka</span>
                    @elseif(strtolower($jenis) === 'dalam talian')
                        <span class="badge green"><i class="ti ti-video" style="font-size:.7rem;"></i> Dalam Talian</span>
                    @elseif(strtolower($jenis) === 'telefon')
                        <span class="badge amber"><i class="ti ti-phone" style="font-size:.7rem;"></i> Telefon</span>
                    @else
                        <span class="badge gray">{{ $jenis }}</span>
                    @endif
                </td>

                {{-- Jumlah Sesi --}}
                <td>
                    <span class="sesi-badge">{{ $m['jumlah_sesi'] ?? 1 }}</span>
                </td>

                {{-- Nota --}}
                <td style="max-width:180px;font-size:.8rem;color:var(--text-muted);">
                    {{ !empty($m['catatan']) ? \Illuminate\Support\Str::limit($m['catatan'], 50) : '—' }}
                </td>

                {{-- Tindakan --}}
                <td>
                    <div class="actions">
                        <button onclick="lihatHistory({{ $m['id_pelajar'] }}, '{{ addslashes($m['pelajar_nama'] ?? '') }}')" class="btn sm" title="Lihat Sejarah">
                            <i class="ti ti-history"></i>
                        </button>
                        @if(session('peranan') === 'admin')
                        <button onclick="editMeeting({{ json_encode($m) }})" class="btn sm" title="Kemaskini">
                            <i class="ti ti-edit"></i>
                        </button>
                        <button onclick="confirmPadam('{{ $m['id'] }}')" class="btn sm danger" title="Padam">
                            <i class="ti ti-trash"></i>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8">
                    <div class="empty-state">
                        <i class="ti ti-calendar-event"></i>
                        <p>Tiada rekod pertemuan ditemui.</p>
                        @if(session('peranan') === 'admin')
                        <button class="btn primary" onclick="bukaModalTambah()" style="margin-top:.8rem;">
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

@if(session('peranan') === 'admin')

{{-- ===== MODAL TAMBAH / EDIT ===== --}}
<div class="modal-overlay" id="modal-meeting">
    <div class="modal">
        <div class="modal-head">
            <h3 id="modal-title"><i class="ti ti-calendar-plus"></i> Tambah Rekod Pertemuan</h3>
            <button class="modal-close" onclick="closeModal('modal-meeting')"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" id="form-meeting" action="{{ route('meeting.store') }}">
        @csrf
        <input type="hidden" name="_method" id="form-method" value="POST">

        <div class="modal-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Pelajar <span class="req">*</span></label>
                    <select name="id_pelajar" id="m-pelajar" required>
                        <option value="">— Pilih Pelajar —</option>
                        @foreach($pelajarList ?? [] as $p)
                        <option value="{{ $p['id_pelajar'] }}">{{ $p['nama_pelajar'] }}</option>
                        @endforeach
                    </select>
                    {{-- Hidden backup supaya id_pelajar tetap dihantar walaupun select disabled --}}
                    <input type="hidden" id="m-pelajar-hidden">
                </div>
                <div class="form-group">
                    <label>Tarikh Pertemuan <span class="req">*</span></label>
                    <input type="date" name="tarikh_pertemuan" id="m-tarikh" value="{{ now()->format('Y-m-d') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Jenis Pertemuan <span class="req">*</span></label>
                    <select name="jenis_pertemuan" id="m-jenis" required>
                        <option value="Bersemuka">Bersemuka</option>
                        <option value="Dalam Talian">Dalam Talian</option>
                        <option value="Telefon">Telefon</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah Sesi <span class="req">*</span></label>
                    <input type="number" name="jumlah_sesi" id="m-sesi" min="1" value="1" placeholder="cth: 1" required>
                </div>
            </div>

            <div class="form-row one">
                <div class="form-group">
                    <label>Nota / Catatan</label>
                    <textarea name="catatan" id="m-nota" placeholder="Ringkasan perbincangan..."></textarea>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn" onclick="tutupModal()">Batal</button>
            <button type="submit" class="btn primary"><i class="ti ti-device-floppy"></i> Simpan Rekod</button>
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
                Adakah anda pasti mahu memadam rekod pertemuan ini?
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

{{-- ===== MODAL HISTORY ===== --}}
<div class="modal-overlay" id="modal-history">
    <div class="modal" style="max-width:640px;">
        <div class="modal-head">
            <h3 id="history-title"><i class="ti ti-history"></i> Sejarah Pertemuan</h3>
            <button class="modal-close" onclick="closeModal('modal-history')"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body" id="history-body" style="max-height:420px;overflow-y:auto;padding:0;">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" onclick="closeModal('modal-history')">Tutup</button>
        </div>
    </div>
</div>

@endif

@endsection

@push('scripts')
<script>
/* ===== MODAL HELPERS ===== */
function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}

// Tutup modal jika klik overlay luar
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});

/* ===== TAMBAH REKOD ===== */
function bukaModalTambah() {
    // Reset form ke mod tambah
    document.getElementById('modal-title').innerHTML = '<i class="ti ti-calendar-plus"></i> Tambah Rekod Pertemuan';
    document.getElementById('form-method').value = 'POST';
    document.getElementById('form-meeting').action = '{{ route("meeting.store") }}';
    document.getElementById('form-meeting').reset();
    document.getElementById('m-tarikh').value = '{{ now()->format("Y-m-d") }}';
    document.getElementById('m-sesi').value = 1;
    // Pastikan pelajar boleh ditukar semasa tambah
    document.getElementById('m-pelajar').disabled = false;
    document.getElementById('m-pelajar-hidden').removeAttribute('name');
    document.getElementById('m-pelajar-hidden').value = '';
    openModal('modal-meeting');
}

function tutupModal() {
    closeModal('modal-meeting');
}

/* ===== EDIT REKOD ===== */
function editMeeting(m) {
    document.getElementById('modal-title').innerHTML = '<i class="ti ti-edit"></i> Kemaskini Rekod Pertemuan';
    document.getElementById('form-method').value = 'PUT';
    document.getElementById('form-meeting').action = `/meeting/${m.id}`;

    // Isi semula nilai
    document.getElementById('m-pelajar').value = m.id_pelajar;
    document.getElementById('m-pelajar').disabled = true; // Pelajar tidak boleh ditukar semasa edit
    document.getElementById('m-pelajar-hidden').setAttribute('name', 'id_pelajar');
    document.getElementById('m-pelajar-hidden').value = m.id_pelajar;
    document.getElementById('m-tarikh').value = m.tarikh_pertemuan ? m.tarikh_pertemuan.substring(0, 10) : '';
    document.getElementById('m-jenis').value = m.jenis_pertemuan || 'Bersemuka';
    document.getElementById('m-sesi').value = m.jumlah_sesi || 1;
    document.getElementById('m-nota').value = m.catatan || '';

    openModal('modal-meeting');
}

/* ===== PADAM ===== */
function confirmPadam(id) {
    document.getElementById('form-padam').action = `/meeting/${id}`;
    openModal('modal-padam');
}

/* ===== LIHAT HISTORY ===== */
function lihatHistory(idPelajar, namaPelajar) {
    document.getElementById('history-title').innerHTML =
        '<i class="ti ti-history"></i> Sejarah Pertemuan \u2014 ' + namaPelajar;
    document.getElementById('history-body').innerHTML =
        '<div style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="ti ti-loader" style="font-size:1.5rem;display:block;margin-bottom:.5rem;"></i>Memuatkan...</div>';
    openModal('modal-history');

    const allRows = @json($meetings ?? []);
    const filtered = allRows.filter(m => String(m.id_pelajar) === String(idPelajar));

    if (filtered.length === 0) {
        document.getElementById('history-body').innerHTML =
            '<div style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="ti ti-calendar-off" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.4;"></i>Tiada rekod pertemuan untuk pelajar ini.</div>';
        return;
    }

    let html = '<table style="width:100%;border-collapse:collapse;font-size:.85rem;">';
    html += '<thead><tr style="background:var(--bg);">';
    ['#','Tarikh','Jenis','Sesi','Nota'].forEach(h => {
        html += `<th style="padding:.6rem .8rem;text-align:left;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--text-muted);border-bottom:1px solid var(--border);">${h}</th>`;
    });
    html += '</tr></thead><tbody>';

    filtered.forEach((m, i) => {
        const tarikh = m.tarikh_pertemuan
            ? new Date(m.tarikh_pertemuan).toLocaleDateString('ms-MY', {day:'2-digit',month:'short',year:'numeric'})
            : '\u2014';
        const jenis = m.jenis_pertemuan || 'Bersemuka';
        let badge = '';
        if (jenis.toLowerCase() === 'bersemuka')
            badge = `<span style="padding:.2rem .55rem;border-radius:999px;font-size:.72rem;font-weight:600;background:#eff6ff;color:#1d4ed8;display:inline-flex;align-items:center;gap:.2rem;"><i class="ti ti-users" style="font-size:.7rem;"></i> Bersemuka</span>`;
        else if (jenis.toLowerCase() === 'dalam talian')
            badge = `<span style="padding:.2rem .55rem;border-radius:999px;font-size:.72rem;font-weight:600;background:#dcfce7;color:#15803d;display:inline-flex;align-items:center;gap:.2rem;"><i class="ti ti-video" style="font-size:.7rem;"></i> Dalam Talian</span>`;
        else if (jenis.toLowerCase() === 'telefon')
            badge = `<span style="padding:.2rem .55rem;border-radius:999px;font-size:.72rem;font-weight:600;background:#fef3c7;color:#b45309;display:inline-flex;align-items:center;gap:.2rem;"><i class="ti ti-phone" style="font-size:.7rem;"></i> Telefon</span>`;
        else
            badge = `<span style="padding:.2rem .55rem;border-radius:999px;font-size:.72rem;background:#f3f4f6;color:#6b7280;">${jenis}</span>`;

        const nota = m.catatan ? (m.catatan.length > 60 ? m.catatan.substring(0, 60) + '...' : m.catatan) : '\u2014';
        html += `<tr style="border-bottom:1px solid var(--border);">
            <td style="padding:.65rem .8rem;color:var(--text-muted);font-size:.8rem;">${i+1}</td>
            <td style="padding:.65rem .8rem;font-weight:500;">${tarikh}</td>
            <td style="padding:.65rem .8rem;">${badge}</td>
            <td style="padding:.65rem .8rem;text-align:center;"><span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:var(--bg);border:1px solid var(--border);font-weight:700;font-size:.8rem;">${m.jumlah_sesi||1}</span></td>
            <td style="padding:.65rem .8rem;color:var(--text-muted);font-size:.8rem;">${nota}</td>
        </tr>`;
    });
    html += '</tbody></table>';

    const totalSesi = filtered.reduce((s, m) => s + (parseInt(m.jumlah_sesi)||1), 0);
    html += `<div style="padding:.8rem 1rem;background:var(--bg);border-top:1px solid var(--border);display:flex;gap:1.5rem;font-size:.8rem;color:var(--text-muted);">
        <span><strong style="color:var(--text);">${filtered.length}</strong> pertemuan</span>
        <span><strong style="color:var(--text);">${totalSesi}</strong> jumlah sesi</span>
    </div>`;
    document.getElementById('history-body').innerHTML = html;
}

/* ===== CARI LIVE ===== */
function cariLive(q) {
    document.querySelectorAll('#meetTable tbody tr').forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none';
    });
}
</script>
@endpush
