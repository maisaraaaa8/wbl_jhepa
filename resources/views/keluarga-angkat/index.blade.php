@extends('layouts.app')
@section('title','Keluarga Angkat')
@section('page-title','Keluarga Angkat')

@section('topbar-actions')
    @if(session('peranan') === 'admin')
    <button class="topbar-btn primary" onclick="bukaModalTambah()">
        <i class="ti ti-plus"></i> Tambah Keluarga
    </button>
    @endif
@endsection

@push('styles')
<style>
/* ===== STATS ===== */
.ka-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(165px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.ka-stat {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.15rem 1.2rem;
    display: flex;
    align-items: center;
    gap: .9rem;
    transition: box-shadow .2s;
}
.ka-stat:hover { box-shadow: 0 4px 18px rgba(0,0,0,.08); }
.ka-stat-icon {
    width: 46px; height: 46px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; flex-shrink: 0;
}
.ka-stat-icon.blue   { background: #eff6ff; color: #2563eb; }
.ka-stat-icon.green  { background: #f0fdf4; color: #16a34a; }
.ka-stat-icon.amber  { background: #fffbeb; color: #d97706; }
.ka-stat-icon.gray   { background: #f9fafb; color: #6b7280; }
.ka-stat-label { font-size: .71rem; color: var(--text-muted); margin-bottom: 2px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.ka-stat-value { font-size: 1.55rem; font-weight: 700; color: var(--text); line-height: 1.1; }

/* ===== FILTER ===== */
.filter-bar { display: flex; gap: .6rem; align-items: center; flex-wrap: wrap; margin-bottom: 1rem; }
.filter-bar select,
.filter-bar input[type="text"] {
    padding: .45rem .8rem; border: 1px solid var(--border); border-radius: 8px;
    font-size: .85rem; background: var(--surface); color: var(--text);
}
.btn-reset {
    padding: .45rem .85rem; border-radius: 8px; border: 1px solid var(--border);
    background: var(--surface); color: var(--text-muted); cursor: pointer;
    font-size: .85rem; display: inline-flex; align-items: center; gap: .3rem;
    transition: all .15s; text-decoration: none;
}
.btn-reset:hover { border-color: #ef4444; color: #ef4444; }

/* ===== TABLE ===== */
.table-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
.table-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.2rem; border-bottom: 1px solid var(--border); gap: .8rem; flex-wrap: wrap; }
.section-title { font-weight: 600; font-size: .95rem; color: var(--text); display: flex; align-items: center; gap: .4rem; }
.search-box { position: relative; }
.search-box i { position: absolute; left: .65rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: .9rem; pointer-events: none; }
.search-box input { padding: .4rem .75rem .4rem 2rem; border: 1px solid var(--border); border-radius: 8px; font-size: .85rem; background: var(--bg); width: 210px; }

table { width: 100%; border-collapse: collapse; }
thead th { padding: .7rem 1rem; text-align: left; font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); border-bottom: 1px solid var(--border); white-space: nowrap; background: var(--bg); }
tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: var(--bg); }
tbody td { padding: .8rem 1rem; font-size: .875rem; color: var(--text); vertical-align: middle; }

.family-cell .family-nama { font-weight: 600; }
.family-cell .family-sub  { font-size: .75rem; color: var(--text-muted); margin-top: 1px; display: flex; align-items: center; gap: .2rem; }
.pelajar-matrik { font-size: .75rem; color: var(--primary); margin-top: 1px; }

/* ===== BADGES ===== */
.badge { display: inline-flex; align-items: center; gap: .25rem; padding: .25rem .65rem; border-radius: 999px; font-size: .72rem; font-weight: 600; white-space: nowrap; }
.badge.green  { background: #dcfce7; color: #15803d; }
.badge.amber  { background: #fef3c7; color: #b45309; }
.badge.red    { background: #fee2e2; color: #b91c1c; }
.badge.gray   { background: #f3f4f6; color: #6b7280; }

/* ===== ACTIONS ===== */
.actions { display: flex; gap: .35rem; }
.btn-icon {
    width: 32px; height: 32px; border-radius: 7px; border: 1px solid var(--border);
    background: var(--surface); cursor: pointer; font-size: .9rem;
    color: var(--text-muted); transition: all .15s;
    display: inline-flex; align-items: center; justify-content: center;
}
.btn-icon:hover              { background: var(--bg); color: var(--text); border-color: var(--primary); }
.btn-icon.view:hover         { background: #eff6ff; color: #2563eb; border-color: #93c5fd; }
.btn-icon.edit:hover         { background: #f0fdf4; color: #16a34a; border-color: #86efac; }
.btn-icon.assign:hover       { background: #f5f3ff; color: #7c3aed; border-color: #c4b5fd; }
.btn-icon.danger:hover       { background: #fee2e2; color: #b91c1c; border-color: #fca5a5; }

/* ===== EMPTY ===== */
.empty-state { text-align: center; padding: 3.5rem 1rem; color: var(--text-muted); }
.empty-state i { font-size: 2.5rem; display: block; margin-bottom: .8rem; opacity: .4; }

/* ===== MODAL ===== */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: none; align-items: center; justify-content: center; z-index: 1000; padding: 1rem; }
.modal-overlay.open { display: flex; }
.modal { background: var(--surface); border-radius: 16px; width: 100%; max-width: 580px; max-height: 93vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.22); animation: slideUp .2s ease; }
.modal.modal-sm { max-width: 420px; }
.modal.modal-lg { max-width: 680px; }
@keyframes slideUp { from { transform: translateY(28px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.modal-head { display: flex; align-items: center; justify-content: space-between; padding: 1.1rem 1.3rem; border-bottom: 1px solid var(--border); }
.modal-head h3 { margin: 0; font-size: 1rem; font-weight: 600; display: flex; align-items: center; gap: .4rem; }
.modal-close { background: none; border: none; cursor: pointer; font-size: 1.1rem; color: var(--text-muted); border-radius: 6px; padding: .2rem .4rem; transition: background .15s; }
.modal-close:hover { background: var(--bg); color: var(--text); }
.modal-body { padding: 1.3rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: .6rem; padding: 1rem 1.3rem; border-top: 1px solid var(--border); }

/* FORM */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: .85rem; margin-bottom: .85rem; }
.form-row.one   { grid-template-columns: 1fr; }
.form-row.three { grid-template-columns: 1fr 1fr 1fr; }
.form-group { display: flex; flex-direction: column; gap: .32rem; }
.form-group label { font-size: .79rem; font-weight: 600; color: var(--text-muted); }
.form-group label .req { color: #ef4444; }
.form-group input,
.form-group select,
.form-group textarea { padding: .5rem .75rem; border: 1px solid var(--border); border-radius: 8px; font-size: .875rem; background: var(--bg); color: var(--text); transition: border-color .15s; }
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
.form-group textarea { resize: vertical; min-height: 72px; }
.form-divider { font-size: .8rem; font-weight: 600; color: var(--text-muted); padding: .4rem 0 .6rem; border-bottom: 1px dashed var(--border); margin-bottom: .85rem; display: flex; align-items: center; gap: .4rem; }
.form-hint { font-size: .75rem; color: var(--text-muted); margin-top: .2rem; }

/* BUTTONS */
.btn { display: inline-flex; align-items: center; gap: .35rem; padding: .5rem 1rem; border-radius: 8px; border: 1px solid var(--border); cursor: pointer; font-size: .875rem; font-weight: 500; transition: all .15s; background: var(--surface); color: var(--text); }
.btn.primary { background: var(--primary); color: #fff; border-color: var(--primary); }
.btn.primary:hover { opacity: .9; }
.btn:hover { background: var(--bg); }

/* ALERT */
.alert { padding: .8rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: .875rem; display: flex; align-items: center; gap: .5rem; }
.alert.success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.alert.error   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

/* VIEW DETAIL */
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .9rem; }
.detail-item label { font-size: .73rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .04em; display: block; margin-bottom: .25rem; }
.detail-item span  { font-size: .875rem; color: var(--text); }
.detail-divider { border: none; border-top: 1px dashed var(--border); margin: 1rem 0; }
.pelajar-card { background: var(--bg); border: 1px solid var(--border); border-radius: 10px; padding: .85rem 1rem; display: flex; align-items: center; gap: .75rem; }
.pelajar-card-icon { width: 40px; height: 40px; border-radius: 10px; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.pelajar-card-nama { font-weight: 600; font-size: .9rem; }
.pelajar-card-sub  { font-size: .77rem; color: var(--text-muted); margin-top: 2px; }
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
<div class="ka-stats">
    <div class="ka-stat">
        <div class="ka-stat-icon blue"><i class="ti ti-home-heart"></i></div>
        <div>
            <div class="ka-stat-label">Jumlah Keluarga</div>
            <div class="ka-stat-value">{{ $stats['jumlah'] ?? 0 }}</div>
        </div>
    </div>
    <div class="ka-stat">
        <div class="ka-stat-icon green"><i class="ti ti-circle-check"></i></div>
        <div>
            <div class="ka-stat-label">Tajaan Aktif</div>
            <div class="ka-stat-value">{{ $stats['aktif'] ?? 0 }}</div>
        </div>
    </div>
    <div class="ka-stat">
        <div class="ka-stat-icon amber"><i class="ti ti-clock-exclamation"></i></div>
        <div>
            <div class="ka-stat-label">Hampir Tamat</div>
            <div class="ka-stat-value">{{ $stats['hampir_tamat'] ?? 0 }}</div>
        </div>
    </div>
    <div class="ka-stat">
        <div class="ka-stat-icon gray"><i class="ti ti-user-off"></i></div>
        <div>
            <div class="ka-stat-label">Belum Ditugaskan</div>
            <div class="ka-stat-value">{{ $stats['belum_ditugaskan'] ?? 0 }}</div>
        </div>
    </div>
</div>

{{-- FILTER --}}
<form method="GET" action="{{ route('keluarga.index') }}" class="filter-bar" id="filterForm">
    <div class="search-box">
        <i class="ti ti-search"></i>
        <input type="text" name="cari" placeholder="Cari nama keluarga..." value="{{ request('cari') }}"
               oninput="cariLive(this.value)">
    </div>
    <select name="status" onchange="document.getElementById('filterForm').submit()">
        <option value="">Semua Status</option>
        <option value="Aktif"            {{ request('status') === 'Aktif'            ? 'selected' : '' }}>Aktif</option>
        <option value="Hampir Tamat"     {{ request('status') === 'Hampir Tamat'     ? 'selected' : '' }}>Hampir Tamat</option>
        <option value="Tamat"            {{ request('status') === 'Tamat'            ? 'selected' : '' }}>Tamat</option>
        <option value="Belum Ditugaskan" {{ request('status') === 'Belum Ditugaskan' ? 'selected' : '' }}>Belum Ditugaskan</option>
    </select>
    @if(request('cari') || request('status'))
        <a href="{{ route('keluarga.index') }}" class="btn-reset"><i class="ti ti-x"></i> Reset</a>
    @endif
</form>

{{-- TABLE --}}
<div class="table-wrap">
    <div class="table-header">
        <div class="section-title">
            <i class="ti ti-home-heart"></i> Senarai Keluarga Angkat
            <span style="font-weight:400;color:var(--text-muted);font-size:.85rem;">({{ count($keluarga ?? []) }} rekod)</span>
        </div>
    </div>

    <table id="kaTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Keluarga</th>
                <th>Pelajar Ditugaskan</th>
                <th>Tempoh Tajaan</th>
                <th>Alamat</th>
                <th>Status</th>
                <th>Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($keluarga ?? [] as $i => $k)
            <tr>
                <td style="color:var(--text-muted);font-size:.8rem;">{{ $i + 1 }}</td>

                <td>
                    <div class="family-cell">
                        <div class="family-nama">
                            {{ $k['nama_keluarga_angkat'] }}
                            @if($k['hide_identity'] ?? false)
                            <span class="badge amber" style="font-size:.65rem;margin-left:.3rem;" title="Identiti disorok daripada pelajar">
                                <i class="ti ti-eye-off" style="font-size:.65rem;"></i> Disorok
                            </span>
                            @endif
                        </div>
                        @if($k['no_telefon'] ?? null)
                        <div class="family-sub"><i class="ti ti-phone" style="font-size:.7rem;"></i> {{ $k['no_telefon'] }}</div>
                        @endif
                    </div>
                </td>

                <td>
                    @if($k['pelajar_nama'] ?? null)
                    <div>
                        <div style="font-weight:500;">{{ $k['pelajar_nama'] }}</div>
                        @if($k['pelajar_matrik'] ?? null)
                        <div class="pelajar-matrik">{{ $k['pelajar_matrik'] }}</div>
                        @endif
                    </div>
                    @else
                    <span style="color:var(--text-muted);font-style:italic;">—</span>
                    @endif
                </td>

                <td>
                    @if($k['tarikh_tamat_tajaan'] ?? null)
                    <span style="font-size:.83rem;">
                        Hingga {{ \Carbon\Carbon::parse($k['tarikh_tamat_tajaan'])->format('M Y') }}
                    </span>
                    @else
                    <span style="color:var(--text-muted);">—</span>
                    @endif
                </td>

                <td style="max-width:170px;">
                    <span style="font-size:.82rem;color:var(--text-muted);">
                        {{ \Illuminate\Support\Str::limit($k['alamat'] ?? '—', 45) }}
                    </span>
                </td>

                <td>
                    @php $st = $k['status_display'] ?? 'Aktif'; @endphp
                    @if($st === 'Aktif')
                        <span class="badge green"><i class="ti ti-circle-check" style="font-size:.7rem;"></i> Aktif</span>
                    @elseif($st === 'Hampir Tamat')
                        <span class="badge amber"><i class="ti ti-clock" style="font-size:.7rem;"></i> Hampir Tamat</span>
                    @elseif($st === 'Tamat')
                        <span class="badge red"><i class="ti ti-x" style="font-size:.7rem;"></i> Tamat</span>
                    @else
                        <span class="badge gray"><i class="ti ti-user-off" style="font-size:.7rem;"></i> Belum Ditugaskan</span>
                    @endif
                </td>

                <td>
                    <div class="actions">
                        {{-- VIEW --}}
                        <button onclick="lihatDetail({{ json_encode($k) }})" class="btn-icon view" title="Lihat Detail">
                            <i class="ti ti-eye"></i>
                        </button>

                        @if(session('peranan') === 'admin')
                        {{-- EDIT --}}
                        <button onclick="editKeluarga({{ json_encode($k) }})" class="btn-icon edit" title="Kemaskini">
                            <i class="ti ti-edit"></i>
                        </button>

                        {{-- TUGASKAN (hanya jika belum ada pelajar) --}}
                        @if(!($k['pelajar_nama'] ?? null))
                        <button onclick="bukaModalTugaskan('{{ $k['id_keluarga_angkat'] }}','{{ addslashes($k['nama_keluarga_angkat']) }}')"
                                class="btn-icon assign" title="Tugaskan Pelajar">
                            <i class="ti ti-user-plus"></i>
                        </button>
                        @endif

                        {{-- PADAM --}}
                        <button onclick="confirmPadam('{{ $k['id_keluarga_angkat'] }}','{{ addslashes($k['nama_keluarga_angkat']) }}')"
                                class="btn-icon danger" title="Padam">
                            <i class="ti ti-trash"></i>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    <div class="empty-state">
                        <i class="ti ti-home-heart"></i>
                        <p>Tiada rekod keluarga angkat ditemui.</p>
                        @if(session('peranan') === 'admin')
                        <button class="btn primary" onclick="bukaModalTambah()" style="margin-top:.8rem;">
                            <i class="ti ti-plus"></i> Tambah Sekarang
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ===== MODAL VIEW DETAIL ===== --}}
<div class="modal-overlay" id="modal-view">
    <div class="modal modal-lg">
        <div class="modal-head">
            <h3><i class="ti ti-eye"></i> Detail Keluarga Angkat</h3>
            <button class="modal-close" onclick="closeModal('modal-view')"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body">
            {{-- Pelajar card --}}
            <div id="view-pelajar-card" style="margin-bottom:1.1rem;display:none;">
                <div class="pelajar-card">
                    <div class="pelajar-card-icon"><i class="ti ti-user-graduate"></i></div>
                    <div>
                        <div class="pelajar-card-nama" id="view-pelajar-nama">—</div>
                        <div class="pelajar-card-sub" id="view-pelajar-sub">—</div>
                    </div>
                    <div style="margin-left:auto;">
                        <span id="view-pelajar-status" class="badge gray">—</span>
                    </div>
                </div>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <label>Nama Keluarga Angkat</label>
                    <span id="view-nama">—</span>
                </div>
                <div class="detail-item">
                    <label>No. Telefon</label>
                    <span id="view-tel">—</span>
                </div>
                <div class="detail-item">
                    <label>Jabatan</label>
                    <span id="view-jabatan">—</span>
                </div>
                <div class="detail-item">
                    <label>No. Kad Pengenalan</label>
                    <span id="view-no-ic">—</span>
                </div>
                <div class="detail-item" style="grid-column:1/-1;">
                    <label>Alamat</label>
                    <span id="view-alamat">—</span>
                </div>
                <div class="detail-item" style="grid-column:1/-1;">
                    <label>Status Identiti</label>
                    <span id="view-hide-identity">—</span>
                </div>
                <div class="detail-item">
                    <label>Tempoh Tajaan Tamat</label>
                    <span id="view-tamat">—</span>
                </div>
                <div class="detail-item">
                    <label>Status Tajaan</label>
                    <span id="view-status">—</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal('modal-view')">Tutup</button>
            @if(session('peranan') === 'admin')
            <button class="btn primary" id="view-edit-btn" onclick="">
                <i class="ti ti-edit"></i> Kemaskini
            </button>
            @endif
        </div>
    </div>
</div>

@if(session('peranan') === 'admin')

{{-- ===== MODAL TAMBAH / EDIT ===== --}}
<div class="modal-overlay" id="modal-tambah">
    <div class="modal">
        <div class="modal-head">
            <h3 id="modal-title"><i class="ti ti-home-plus"></i> Tambah Keluarga Angkat</h3>
            <button class="modal-close" onclick="tutupModalTambah()"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" id="form-keluarga" action="{{ route('keluarga.store') }}">
        @csrf
        <input type="hidden" name="_method" id="form-method" value="POST">

        <div class="modal-body">
            {{-- Maklumat Asas --}}
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Penuh <span class="req">*</span></label>
                    <input type="text" name="nama_keluarga_angkat" id="k-nama" placeholder="cth: Encik Mohd Ismail" required>
                </div>
                <div class="form-group">
                    <label>No. Telefon</label>
                    <input type="tel" name="no_telefon" id="k-tel" placeholder="cth: 012-3456789">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Jabatan</label>
                    <input type="text" name="jabatan" id="k-jabatan" placeholder="cth: Jabatan Pendidikan">
                </div>
                <div class="form-group">
                    <label>No. Kad Pengenalan</label>
                    <input type="text" name="no_ic" id="k-no-ic" placeholder="cth: 800101-10-1234">
                </div>
            </div>

            <div class="form-row one">
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" id="k-alamat" placeholder="Alamat penuh keluarga angkat..."></textarea>
                </div>
            </div>

            <div class="form-row one">
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                        <input type="checkbox" name="hide_identity" id="k-hide-identity" value="1" style="width:auto;">
                        <i class="ti ti-eye-off"></i> Sorok Identiti Daripada Pelajar
                    </label>
                    <span class="form-hint">Bila diaktifkan, pelajar TIDAK akan nampak nama, no. IC, telefon, jabatan &amp; alamat keluarga angkat ini — hanya paparan "Penderma Tanpa Nama". Admin tetap nampak maklumat penuh.</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Pelajar Ditugaskan</label>
                    <select name="id_pelajar" id="k-pelajar" onchange="syncTarikhDariPelajar(this)">
                        <option value="">— Pilih Pelajar —</option>
                        @foreach($pelajarList ?? [] as $p)
                        <option value="{{ $p['id_pelajar'] }}"
                                data-tamat="{{ $p['tarikh_tamat_tajaan'] ?? '' }}">
                            {{ $p['nama_pelajar'] }} ({{ $p['no_matrik'] }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Tarikh Tamat Tajaan</label>
                    <input type="date" name="tarikh_tamat_tajaan" id="k-tamat">
                    <span class="form-hint">Auto-isi dari rekod pelajar jika pelajar dipilih</span>
                </div>
            </div>

            <div class="form-row one">
                <div class="form-group">
                    <label>Status Tajaan</label>
                    <select name="status_tajaan" id="k-status">
                        <option value="Aktif">Aktif</option>
                        <option value="Hampir Tamat">Hampir Tamat</option>
                        <option value="Tamat">Tamat</option>
                    </select>
                </div>
            </div>

            {{-- Akaun Login (optional) --}}
            <div class="form-divider" id="div-akaun">
                <i class="ti ti-lock"></i> Akaun Login (Pilihan)
            </div>
            <p style="font-size:.8rem;color:var(--text-muted);margin:-0.3rem 0 .85rem;">
                Isi email & kata laluan jika keluarga angkat perlu login ke sistem.
            </p>
            <div class="form-row">
                <div class="form-group">
                    <label>Emel</label>
                    <input type="email" name="email" id="k-email" placeholder="cth: keluarga@email.com">
                </div>
                <div class="form-group">
                    <label>Kata Laluan</label>
                    <input type="text" name="password" id="k-password" placeholder="Min. 8 aksara" autocomplete="new-password">
                    <span class="form-hint">Kosongkan jika tidak perlu akaun</span>
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

{{-- ===== MODAL TUGASKAN ===== --}}
<div class="modal-overlay" id="modal-tugaskan">
    <div class="modal modal-sm">
        <div class="modal-head">
            <h3><i class="ti ti-user-plus"></i> Tugaskan Pelajar</h3>
            <button class="modal-close" onclick="closeModal('modal-tugaskan')"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" id="form-tugaskan">
        @csrf
        <div class="modal-body">
            <p style="margin:0 0 1rem;font-size:.875rem;color:var(--text-muted);">
                Keluarga: <strong id="tugaskan-nama"></strong>
            </p>
            <div class="form-row one">
                <div class="form-group">
                    <label>Pilih Pelajar <span class="req">*</span></label>
                    <select name="pelajar_id" id="tugaskan-pelajar" required
                            onchange="autoisiTarikhTamat(this)">
                        <option value="">— Pilih Pelajar —</option>
                        @foreach($pelajarList ?? [] as $p)
                        <option value="{{ $p['id_pelajar'] }}"
                                data-tamat="{{ $p['tarikh_tamat_tajaan'] ?? '' }}">
                            {{ $p['nama_pelajar'] }} ({{ $p['no_matrik'] }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row one">
                <div class="form-group">
                    <label>Tarikh Tamat Tajaan</label>
                    <input type="date" name="tarikh_tamat" id="tugaskan-tamat">
                    <span class="form-hint">Auto-isi dari rekod pelajar</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" onclick="closeModal('modal-tugaskan')">Batal</button>
            <button type="submit" class="btn primary"><i class="ti ti-check"></i> Tugaskan</button>
        </div>
        </form>
    </div>
</div>

{{-- ===== MODAL PADAM ===== --}}
<div class="modal-overlay" id="modal-padam">
    <div class="modal modal-sm">
        <div class="modal-head">
            <h3 style="color:#b91c1c;"><i class="ti ti-alert-triangle"></i> Sahkan Pemadaman</h3>
            <button class="modal-close" onclick="closeModal('modal-padam')"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body">
            <p style="margin:0;font-size:.9rem;">
                Padam rekod <strong id="padam-nama"></strong>? Tindakan ini tidak boleh dibatalkan.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" onclick="closeModal('modal-padam')">Batal</button>
            <form method="POST" id="form-padam">
            @csrf @method('DELETE')
            <button type="submit" class="btn" style="background:#ef4444;color:#fff;border-color:#ef4444;">
                <i class="ti ti-trash"></i> Ya, Padam
            </button>
            </form>
        </div>
    </div>
</div>

@endif
@endsection

@push('scripts')
<script>
/* ===== MODAL HELPERS ===== */
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); });
});

/* ===== LIVE SEARCH ===== */
function cariLive(q) {
    document.querySelectorAll('#kaTable tbody tr').forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none';
    });
}

/* ===== SYNC TARIKH DARI DROPDOWN PELAJAR ===== */
function syncTarikhDariPelajar(sel) {
    const opt   = sel.options[sel.selectedIndex];
    const tamat = opt.dataset.tamat ?? '';
    const inp   = document.getElementById('k-tamat');
    if (tamat && inp) inp.value = tamat;
}
function autoisiTarikhTamat(sel) {
    const opt   = sel.options[sel.selectedIndex];
    const tamat = opt.dataset.tamat ?? '';
    const inp   = document.getElementById('tugaskan-tamat');
    if (tamat && inp) inp.value = tamat;
}

/* ===== MODAL TAMBAH ===== */
function bukaModalTambah() {
    const f = document.getElementById('form-keluarga');
    if (!f) return;
    f.action = '{{ route("keluarga.store") }}';
    document.getElementById('form-method').value = 'POST';
    document.getElementById('modal-title').innerHTML = '<i class="ti ti-home-plus"></i> Tambah Keluarga Angkat';
    f.reset();
    openModal('modal-tambah');
}
function tutupModalTambah() {
    closeModal('modal-tambah');
}

/* ===== EDIT ===== */
function editKeluarga(k) {
    const f = document.getElementById('form-keluarga');
    if (!f) return;
    f.action = `/keluarga/${k.id_keluarga_angkat}`;
    document.getElementById('form-method').value = 'PUT';
    document.getElementById('modal-title').innerHTML = '<i class="ti ti-edit"></i> Kemaskini Keluarga Angkat';

    document.getElementById('k-nama').value        = k.nama_keluarga_angkat ?? '';
    document.getElementById('k-tel').value         = k.no_telefon ?? '';
    document.getElementById('k-jabatan').value     = k.jabatan ?? '';
    document.getElementById('k-no-ic').value       = k.no_ic ?? '';
    document.getElementById('k-alamat').value      = k.alamat ?? '';
    document.getElementById('k-tamat').value       = k.tarikh_tamat_tajaan ?? '';
    document.getElementById('k-status').value      = k.status_tajaan ?? 'Aktif';
    document.getElementById('k-hide-identity').checked = !!k.hide_identity;

    const selP = document.getElementById('k-pelajar');
    if (selP) selP.value = k.id_pelajar ?? '';

    // Sorok bahagian akaun semasa edit (akaun dah wujud)
    const divAkaun = document.getElementById('div-akaun');
    if (divAkaun) {
        divAkaun.nextElementSibling.style.display = 'none';
        document.getElementById('k-email').closest('.form-row').style.display = 'none';
    }

    openModal('modal-tambah');
}

/* ===== VIEW DETAIL ===== */
function lihatDetail(k) {
    document.getElementById('view-nama').textContent    = k.nama_keluarga_angkat ?? '—';
    document.getElementById('view-tel').textContent     = k.no_telefon ?? '—';
    document.getElementById('view-jabatan').textContent = k.jabatan ?? '—';
    document.getElementById('view-no-ic').textContent   = k.no_ic ?? '—';
    document.getElementById('view-alamat').textContent  = k.alamat ?? '—';
    document.getElementById('view-hide-identity').innerHTML = k.hide_identity
        ? '<span class="badge amber"><i class="ti ti-eye-off" style="font-size:.7rem;"></i> Disorok daripada pelajar</span>'
        : '<span class="badge green"><i class="ti ti-eye" style="font-size:.7rem;"></i> Ditunjukkan kepada pelajar</span>';

    const tamat = k.tarikh_tamat_tajaan;
    document.getElementById('view-tamat').textContent = tamat
        ? formatTarikh(tamat)
        : '—';

    // Status badge
    const st   = k.status_display ?? 'Aktif';
    const stEl = document.getElementById('view-status');
    const cls  = { 'Aktif': 'green', 'Hampir Tamat': 'amber', 'Tamat': 'red', 'Belum Ditugaskan': 'gray' };
    stEl.innerHTML = `<span class="badge ${cls[st] ?? 'gray'}">${st}</span>`;

    // Pelajar card
    const pelajarCard = document.getElementById('view-pelajar-card');
    if (k.pelajar_nama) {
        document.getElementById('view-pelajar-nama').textContent = k.pelajar_nama;
        const sub = [k.pelajar_matrik, k.pelajar_semester].filter(Boolean).join(' · ');
        document.getElementById('view-pelajar-sub').textContent  = sub || '—';
        document.getElementById('view-pelajar-status').textContent = k.pelajar_status ?? '—';
        pelajarCard.style.display = 'block';
    } else {
        pelajarCard.style.display = 'none';
    }

    // Butang edit dalam modal view
    const editBtn = document.getElementById('view-edit-btn');
    if (editBtn) {
        editBtn.onclick = () => { closeModal('modal-view'); editKeluarga(k); };
    }

    openModal('modal-view');
}

function formatTarikh(d) {
    const bulan = ['Jan','Feb','Mac','Apr','Mei','Jun','Jul','Ogos','Sep','Okt','Nov','Dis'];
    const dt = new Date(d);
    return `${dt.getDate()} ${bulan[dt.getMonth()]} ${dt.getFullYear()}`;
}

/* ===== TUGASKAN ===== */
function bukaModalTugaskan(id, nama) {
    document.getElementById('tugaskan-nama').textContent = nama;
    document.getElementById('form-tugaskan').action = `/keluarga/${id}/tugaskan`;
    document.getElementById('tugaskan-pelajar').value = '';
    document.getElementById('tugaskan-tamat').value   = '';
    openModal('modal-tugaskan');
}

/* ===== PADAM ===== */
function confirmPadam(id, nama) {
    document.getElementById('padam-nama').textContent = nama;
    document.getElementById('form-padam').action = `/keluarga/${id}`;
    openModal('modal-padam');
}
</script>
@endpush
