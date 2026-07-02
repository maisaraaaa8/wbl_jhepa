@extends('layouts.app-keluarga')

@section('title', 'Meeting')
@section('page-title', 'Meeting')

@push('styles')
<style>
.ps-hero {
    background: linear-gradient(135deg, var(--primary) 0%, #1a3f73 55%, #c9891a 100%);
    border-radius: var(--radius-lg);
    padding: 22px 26px;
    color: #fff;
    margin-bottom: 18px;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px;
}
.ps-hero-eyebrow { font-size: 11px; opacity: .8; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px; }
.ps-hero-title { font-size: 19px; font-weight: 600; }
.ps-hero-sub { font-size: 12.5px; opacity: .85; margin-top: 3px; }
.ps-hero-icon {
    width: 46px; height: 46px; border-radius: 50%;
    background: rgba(255,255,255,.16);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; flex-shrink: 0;
}

.ps-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 18px; }
.ps-stat-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-lg); padding: 15px 16px;
    display: flex; align-items: center; gap: 12px;
}
.ps-stat-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.ps-stat-icon.violet { background: #f3effe; color: #6234a1; }
.ps-stat-icon.green  { background: #eaf3de; color: #3b6d11; }
.ps-stat-icon.blue   { background: #e6f1fb; color: #185fa5; }
.ps-stat-value { font-size: 20px; font-weight: 600; line-height: 1.15; }
.ps-stat-label { font-size: 11.5px; color: var(--text-muted); margin-top: 1px; }

.ps-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-lg); overflow: hidden;
}
.ps-card-head {
    display: flex; align-items: center; justify-content: space-between;
    gap: 10px; flex-wrap: wrap;
    padding: 16px 18px; border-bottom: 1px solid var(--border);
}
.ps-card-head-title { font-size: 14px; font-weight: 600; }
.ps-card-head-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

.ps-empty {
    background: var(--surface-2); border: 1px dashed var(--border-strong);
    border-radius: var(--radius-lg); padding: 48px 20px; text-align: center;
    color: var(--text-muted);
}
.ps-empty i { font-size: 40px; display: block; margin-bottom: 12px; opacity: .45; }

.jenis-badge { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 500; }
.jenis-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.jenis-dot.bersemuka { background: #185fa5; }
.jenis-dot.panggilan  { background: #3b6d11; }
.jenis-dot.dalamtalian { background: #6234a1; }
.jenis-dot.lain { background: #898781; }

.sesi-pill {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 26px; height: 26px; padding: 0 6px;
    border-radius: 50%; background: var(--surface-2); border: 1px solid var(--border);
    font-size: 12px; font-weight: 700; color: var(--text);
}

.row-actions { display: flex; gap: 6px; justify-content: flex-end; }
.row-actions .btn.sm { padding: 5px 8px; }
</style>
@endpush

@section('content')

@php
    $nama = session('nama', 'Keluarga Angkat');
    $adaPelajar = count($pelajarSaya ?? []) > 0;
@endphp

{{-- ═══ HERO ═══ --}}
<div class="ps-hero">
    <div>
        <div class="ps-hero-eyebrow">Portal Keluarga Angkat</div>
        <div class="ps-hero-title">Meeting</div>
        <div class="ps-hero-sub">Rekod pertemuan &amp; perbincangan dengan pelajar di bawah jagaan {{ $nama }}</div>
    </div>
    <div class="ps-hero-icon"><i class="ti ti-calendar-event"></i></div>
</div>

{{-- ═══ KAD RINGKASAN ═══ --}}
<div class="ps-stats">
    <div class="ps-stat-card">
        <div class="ps-stat-icon violet"><i class="ti ti-calendar-event"></i></div>
        <div>
            <div class="ps-stat-value">{{ $jumlahPertemuan }}</div>
            <div class="ps-stat-label">Jumlah Pertemuan</div>
        </div>
    </div>
    <div class="ps-stat-card">
        <div class="ps-stat-icon green"><i class="ti ti-list-numbers"></i></div>
        <div>
            <div class="ps-stat-value">{{ $jumlahSesi }}</div>
            <div class="ps-stat-label">Jumlah Sesi</div>
        </div>
    </div>
    <div class="ps-stat-card">
        <div class="ps-stat-icon blue"><i class="ti ti-calendar-month"></i></div>
        <div>
            <div class="ps-stat-value">{{ $bulanIniCount }}</div>
            <div class="ps-stat-label">Bulan Ini</div>
        </div>
    </div>
</div>

{{-- ═══ SENARAI PERTEMUAN ═══ --}}
<div class="ps-card">
    <div class="ps-card-head">
        <div>
            <div class="ps-card-head-title">Sejarah Pertemuan</div>
            <div class="ps-card-head-sub">Rekod pertemuan untuk pelajar di bawah jagaan anda</div>
        </div>
        @if($adaPelajar)
        <button type="button" class="btn primary" onclick="bukaModalTambah()">
            <i class="ti ti-plus"></i> Tambah Rekod
        </button>
        @endif
    </div>

    @if(!$adaPelajar)
        <div style="padding:18px">
            <div class="ps-empty">
                <i class="ti ti-user-off"></i>
                <p style="font-size:13.5px;font-weight:500;color:var(--text)">Tiada pelajar ditugaskan kepada anda lagi.</p>
                <p style="font-size:12px;margin-top:5px">Sila hubungi pihak pentadbir untuk maklumat lanjut.</p>
            </div>
        </div>
    @elseif(count($meetingList) === 0)
        <div style="padding:18px">
            <div class="ps-empty">
                <i class="ti ti-calendar-off"></i>
                <p style="font-size:13.5px;font-weight:500;color:var(--text)">Tiada rekod pertemuan lagi.</p>
                <p style="font-size:12px;margin-top:5px">Klik "Tambah Rekod" untuk daftar pertemuan pertama.</p>
            </div>
        </div>
    @else
    <div class="table-wrap" style="border:none;border-radius:0;margin-bottom:0">
        <table>
            <thead>
                <tr>
                    <th width="36">#</th>
                    <th>Tarikh</th>
                    <th>Pelajar</th>
                    <th>Jenis</th>
                    <th style="text-align:center">Sesi</th>
                    <th>Catatan</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($meetingList as $i => $m)
                @php
                    $jenis = $m['jenis_pertemuan'] ?? 'Bersemuka';
                    $dotClass = match(strtolower($jenis)) {
                        'bersemuka'      => 'bersemuka',
                        'panggilan telefon', 'panggilan' => 'panggilan',
                        'dalam talian', 'online' => 'dalamtalian',
                        default => 'lain',
                    };
                @endphp
                <tr>
                    <td style="color:var(--text-muted);font-size:12px">{{ $i + 1 }}</td>
                    <td style="white-space:nowrap">
                        <div>{{ !empty($m['tarikh_pertemuan']) ? \Carbon\Carbon::parse($m['tarikh_pertemuan'])->format('d M Y') : '—' }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">
                            {{ !empty($m['tarikh_pertemuan']) ? \Carbon\Carbon::parse($m['tarikh_pertemuan'])->diffForHumans() : '' }}
                        </div>
                    </td>
                    <td>{{ $m['nama_pelajar'] ?? '—' }}</td>
                    <td>
                        <span class="jenis-badge">
                            <span class="jenis-dot {{ $dotClass }}"></span>
                            {{ $jenis }}
                        </span>
                    </td>
                    <td style="text-align:center"><span class="sesi-pill">{{ $m['jumlah_sesi'] ?? 1 }}</span></td>
                    <td style="color:var(--text-muted)">
                        {{ !empty($m['catatan']) ? \Illuminate\Support\Str::limit($m['catatan'], 60) : '—' }}
                    </td>
                    <td>
                        <div class="row-actions">
                            <button type="button" class="btn sm" title="Kemaskini" onclick='editMeeting(@json($m))'>
                                <i class="ti ti-edit"></i>
                            </button>
                            <button type="button" class="btn sm danger" title="Padam" onclick="confirmPadam({{ $m['id'] }})">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@if($adaPelajar)
{{-- ═══ MODAL TAMBAH / EDIT ═══ --}}
<div class="modal-overlay" id="modal-meeting">
    <div class="modal">
        <form method="POST" id="form-meeting" action="{{ route('keluarga-portal.meeting.store') }}">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">

            <div class="modal-head">
                <h3 id="modal-title"><i class="ti ti-calendar-plus"></i> Tambah Rekod Pertemuan</h3>
                <button type="button" class="modal-close" onclick="closeModal('modal-meeting')"><i class="ti ti-x"></i></button>
            </div>

            <div class="modal-body">
                <div class="form-row one">
                    <div class="form-group">
                        <label>Pelajar <span class="req">*</span></label>
                        <select name="id_pelajar" id="m-pelajar" required>
                            @foreach($pelajarSaya as $p)
                                <option value="{{ $p['id_pelajar'] }}">{{ $p['nama_pelajar'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Tarikh Pertemuan <span class="req">*</span></label>
                        <input type="date" name="tarikh_pertemuan" id="m-tarikh" required>
                    </div>
                    <div class="form-group">
                        <label>Jenis Pertemuan <span class="req">*</span></label>
                        <select name="jenis_pertemuan" id="m-jenis" required>
                            <option value="Bersemuka">Bersemuka</option>
                            <option value="Panggilan Telefon">Panggilan Telefon</option>
                            <option value="Dalam Talian">Dalam Talian</option>
                            <option value="Lain-lain">Lain-lain</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Jumlah Sesi <span class="req">*</span></label>
                        <input type="number" name="jumlah_sesi" id="m-sesi" min="1" value="1" required>
                    </div>
                </div>

                <div class="form-row one">
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="catatan" id="m-nota" placeholder="Ringkasan perbincangan..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeModal('modal-meeting')">Batal</button>
                <button type="submit" class="btn primary"><i class="ti ti-check"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══ MODAL PADAM ═══ --}}
<div class="modal-overlay" id="modal-padam">
    <div class="modal" style="max-width:420px">
        <div class="modal-head">
            <h3><i class="ti ti-alert-triangle"></i> Padam Rekod</h3>
            <button type="button" class="modal-close" onclick="closeModal('modal-padam')"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body">
            <p style="font-size:13px;color:var(--text-2)">
                Adakah anda pasti mahu memadam rekod pertemuan ini? Tindakan ini tidak boleh dibatalkan.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" onclick="closeModal('modal-padam')">Batal</button>
            <form method="POST" id="form-padam">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn danger"><i class="ti ti-trash"></i> Ya, Padam</button>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
function bukaModalTambah() {
    document.getElementById('modal-title').innerHTML = '<i class="ti ti-calendar-plus"></i> Tambah Rekod Pertemuan';
    document.getElementById('form-method').value = 'POST';
    document.getElementById('form-meeting').action = '{{ route("keluarga-portal.meeting.store") }}';
    document.getElementById('form-meeting').reset();
    document.getElementById('m-pelajar').disabled = false;
    document.getElementById('m-tarikh').value = '{{ now()->format("Y-m-d") }}';
    document.getElementById('m-sesi').value = 1;
    openModal('modal-meeting');
}

function editMeeting(m) {
    document.getElementById('modal-title').innerHTML = '<i class="ti ti-edit"></i> Kemaskini Rekod Pertemuan';
    document.getElementById('form-method').value = 'PUT';
    document.getElementById('form-meeting').action = '/keluarga-portal/meeting/' + m.id;

    document.getElementById('m-pelajar').value = m.id_pelajar;
    document.getElementById('m-pelajar').disabled = true; // pelajar tak boleh ditukar semasa edit
    document.getElementById('m-tarikh').value = m.tarikh_pertemuan ? m.tarikh_pertemuan.substring(0, 10) : '';
    document.getElementById('m-jenis').value = m.jenis_pertemuan || 'Bersemuka';
    document.getElementById('m-sesi').value = m.jumlah_sesi || 1;
    document.getElementById('m-nota').value = m.catatan || '';

    openModal('modal-meeting');
}

function confirmPadam(id) {
    document.getElementById('form-padam').action = '/keluarga-portal/meeting/' + id;
    openModal('modal-padam');
}
</script>
@endpush
