@extends('layouts.app-pelajar')

@section('title', 'Meeting Record Saya')
@section('page-title', 'Meeting Record')

@section('topbar-actions')
<button class="btn primary" onclick="bukaModalTambah()">
    <i class="ti ti-plus"></i> Tambah Rekod
</button>
@endsection

@php $pid = $pelajar['id_pelajar'] ?? null; @endphp

@section('content')

{{-- ═══ RINGKASAN ═══ --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:20px">
    <div class="stat-card">
        <div class="stat-label">Jumlah Pertemuan</div>
        <div class="stat-value">{{ $stats['jumlah_pertemuan'] }}</div>
        <div class="stat-sub">rekod keseluruhan</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Jumlah Sesi</div>
        <div class="stat-value">{{ $stats['jumlah_sesi'] }}</div>
        <div class="stat-sub">sesi direkodkan</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Bersemuka</div>
        <div class="stat-value">{{ $stats['bersemuka'] }}</div>
        <div class="stat-sub">pertemuan bersemuka</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pertemuan Terkini</div>
        <div class="stat-value" style="font-size:16px">
            {{ $stats['terkini'] ? \Carbon\Carbon::parse($stats['terkini'])->format('d M Y') : '—' }}
        </div>
        <div class="stat-sub">tarikh terakhir</div>
    </div>
</div>

{{-- ═══ SENARAI REKOD PERTEMUAN ═══ --}}
<div class="table-wrap">
    <div class="table-header">
        <div class="section-title">
            <i class="ti ti-calendar-event"></i> Sejarah Rekod Pertemuan
            <span class="rec-count">({{ count($meetings) }} rekod)</span>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Tarikh</th>
                <th>Jenis Pertemuan</th>
                <th style="text-align:center">Jumlah Sesi</th>
                <th>Keluarga Angkat</th>
                <th>Catatan</th>
                <th style="text-align:right">Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($meetings as $m)
            @php
                $jenis = $m['jenis_pertemuan'] ?? 'Bersemuka';
                $jenisClr = strtolower($jenis) === 'bersemuka' ? 'green' : 'blue';
            @endphp
            <tr>
                <td>{{ !empty($m['tarikh_pertemuan']) ? \Carbon\Carbon::parse($m['tarikh_pertemuan'])->format('d M Y') : '—' }}</td>
                <td><span class="badge {{ $jenisClr }}">{{ $jenis }}</span></td>
                <td style="text-align:center;font-weight:600">{{ $m['jumlah_sesi'] ?? 1 }}</td>
                <td>{{ $m['keluarga_nama'] ?? '—' }}</td>
                <td style="color:var(--text-muted);max-width:260px">{{ $m['catatan'] ?? '—' }}</td>
                <td style="text-align:right;white-space:nowrap">
                    <button onclick="editMeeting({{ json_encode($m) }})" class="btn sm" title="Kemaskini">
                        <i class="ti ti-edit"></i>
                    </button>
                    <button onclick="confirmPadam('{{ $m['id'] }}')" class="btn sm danger" title="Padam">
                        <i class="ti ti-trash"></i>
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px;font-size:13px">
                    <i class="ti ti-calendar-event" style="font-size:28px;display:block;margin-bottom:8px"></i>
                    Tiada rekod pertemuan lagi.
                    <div style="margin-top:10px">
                        <button class="btn primary sm" onclick="bukaModalTambah()">
                            <i class="ti ti-plus"></i> Tambah Rekod Pertama
                        </button>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ═══ MODAL TAMBAH / EDIT ═══ --}}
<div class="modal-overlay" id="modal-meeting">
    <div class="modal">
        <div class="modal-head">
            <h3 id="modal-title"><i class="ti ti-calendar-plus"></i> Tambah Rekod Pertemuan</h3>
            <button class="modal-close" onclick="closeModal('modal-meeting')"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" id="form-meeting" action="{{ route('pelajar.meeting.store') }}">
        @csrf
        <input type="hidden" name="_method" id="form-method" value="POST">

        <div class="modal-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Tarikh Pertemuan <span class="req">*</span></label>
                    <input type="date" name="tarikh_pertemuan" id="m-tarikh" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label>Jenis Pertemuan <span class="req">*</span></label>
                    <select name="jenis_pertemuan" id="m-jenis" required>
                        <option value="Bersemuka">Bersemuka</option>
                        <option value="Dalam Talian">Dalam Talian</option>
                        <option value="Telefon">Telefon</option>
                    </select>
                </div>
            </div>

            <div class="form-row one">
                <div class="form-group">
                    <label>Jumlah Sesi <span class="req">*</span></label>
                    <input type="number" name="jumlah_sesi" id="m-sesi" min="1" value="1" placeholder="cth: 1" required>
                </div>
            </div>

            <div class="form-row one">
                <div class="form-group">
                    <label>Nota / Catatan</label>
                    <textarea name="catatan" id="m-nota" placeholder="Ringkasan perbincangan bersama keluarga angkat..."></textarea>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn" onclick="closeModal('modal-meeting')">Batal</button>
            <button type="submit" class="btn primary"><i class="ti ti-device-floppy"></i> Simpan Rekod</button>
        </div>
        </form>
    </div>
</div>

{{-- ═══ MODAL PADAM ═══ --}}
<div class="modal-overlay" id="modal-padam">
    <div class="modal" style="max-width:400px">
        <div class="modal-head">
            <h3 style="color:#b91c1c"><i class="ti ti-alert-triangle"></i> Sahkan Pemadaman</h3>
            <button class="modal-close" onclick="closeModal('modal-padam')"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body">
            <p style="margin:0;font-size:.9rem">
                Adakah anda pasti mahu memadam rekod pertemuan ini? Tindakan ini tidak boleh dibatalkan.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" onclick="closeModal('modal-padam')">Batal</button>
            <form method="POST" id="form-padam">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn danger">
                <i class="ti ti-trash"></i> Ya, Padam
            </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});

function bukaModalTambah() {
    document.getElementById('modal-title').innerHTML = '<i class="ti ti-calendar-plus"></i> Tambah Rekod Pertemuan';
    document.getElementById('form-method').value = 'POST';
    document.getElementById('form-meeting').action = '{{ route("pelajar.meeting.store") }}';
    document.getElementById('form-meeting').reset();
    document.getElementById('m-tarikh').value = '{{ now()->format("Y-m-d") }}';
    document.getElementById('m-sesi').value = 1;
    openModal('modal-meeting');
}

function editMeeting(m) {
    document.getElementById('modal-title').innerHTML = '<i class="ti ti-edit"></i> Kemaskini Rekod Pertemuan';
    document.getElementById('form-method').value = 'PUT';
    document.getElementById('form-meeting').action = `/saya/meeting/${m.id}`;

    document.getElementById('m-tarikh').value = m.tarikh_pertemuan ? m.tarikh_pertemuan.substring(0, 10) : '';
    document.getElementById('m-jenis').value = m.jenis_pertemuan || 'Bersemuka';
    document.getElementById('m-sesi').value = m.jumlah_sesi || 1;
    document.getElementById('m-nota').value = m.catatan || '';

    openModal('modal-meeting');
}

function confirmPadam(id) {
    document.getElementById('form-padam').action = `/saya/meeting/${id}`;
    openModal('modal-padam');
}
</script>
@endpush
