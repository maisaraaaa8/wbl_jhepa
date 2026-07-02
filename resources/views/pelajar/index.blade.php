@extends('layouts.app')

@section('title', 'Pelajar')
@section('page-title', 'Pengurusan Pelajar')

@section('topbar-actions')
    <a href="{{ route('pelajar.create') }}" class="topbar-btn primary">
        <i class="ti ti-plus"></i> Tambah Pelajar
    </a>
@endsection

@push('styles')
<style>
/* ── TAJAAN BADGE ─────────────────────────────── */
.tajaan-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 20px;
    white-space: nowrap;
}
.tajaan-badge.aktif      { background: #eaf3de; color: #3b6d11; }
.tajaan-badge.hampir     { background: #faeeda; color: #854f0b; }
.tajaan-badge.tamat      { background: #fcebeb; color: #a32d2d; }
.tajaan-badge.tiada      { background: #f1efe8; color: #9b9892; }

/* ── GPA ─────────────────────────────────────── */
.gpa-wrap { display: flex; align-items: center; gap: 6px; }
.gpa-bar-bg {
    width: 50px; height: 5px;
    background: var(--border);
    border-radius: 4px; overflow: hidden; flex-shrink: 0;
}
.gpa-bar-fill {
    height: 100%; border-radius: 4px;
    background: var(--primary);
    transition: width .3s;
}
.gpa-bar-fill.high  { background: #48bb78; }
.gpa-bar-fill.mid   { background: #ed8936; }
.gpa-bar-fill.low   { background: #fc8181; }
.gpa-num { font-size: 12px; font-variant-numeric: tabular-nums; }

/* ── TABLE COMPACT ───────────────────────────── */
.table-wrap table th { white-space: nowrap; }
.table-wrap table td { vertical-align: middle; }
.prog-text { font-size: 12px; color: var(--text-2); max-width: 220px; line-height: 1.3; }
.tel-text  { font-size: 12px; color: var(--text-2); }

/* ── FILTER ROW ──────────────────────────────── */
.filter-row {
    display: flex;
    gap: 8px;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    margin-bottom: 16px;
}
.filter-left { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.count-text { font-size: 12px; color: var(--text-muted); white-space: nowrap; }
</style>
@endpush

@section('content')

{{-- ═══ FILTER & CARIAN ═══ --}}
<form method="GET" action="{{ route('pelajar.index') }}" id="filter-form">
    <div class="filter-row">
        <div class="filter-left">

            {{-- Carian --}}
            <div class="search-box" style="width:260px">
                <i class="ti ti-search"></i>
                <input
                    name="cari"
                    placeholder="Cari nama atau no. matrik..."
                    value="{{ request('cari') }}"
                    onchange="this.form.submit()"
                    style="border:none;background:transparent;outline:none;font-size:13px;width:100%;color:var(--text)"
                >
            </div>

            {{-- Filter Semester --}}
            <select name="semester" class="topbar-btn" style="cursor:pointer" onchange="this.form.submit()">
                <option value="">Semua Semester</option>
                @foreach(['Semester 1','Semester 2','Semester 3','Semester 4','Semester 5','Semester 6'] as $sem)
                    <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                @endforeach
            </select>

            {{-- Filter Status --}}
            <select name="status" class="topbar-btn" style="cursor:pointer" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="Aktif"   {{ request('status') == 'Aktif'   ? 'selected' : '' }}>Aktif</option>
                <option value="Tamat"   {{ request('status') == 'Tamat'   ? 'selected' : '' }}>Tamat</option>
                <option value="Tangguh" {{ request('status') == 'Tangguh' ? 'selected' : '' }}>Tangguh</option>
            </select>

            {{-- Filter Status Kelulusan Mesyuarat (baharu) --}}
            <select name="kelulusan" class="topbar-btn" style="cursor:pointer" onchange="this.form.submit()">
                <option value="">Semua Kelulusan</option>
                <option value="diluluskan" {{ request('kelulusan') == 'diluluskan' ? 'selected' : '' }}>Sudah Diluluskan</option>
                <option value="belum"      {{ request('kelulusan') == 'belum'      ? 'selected' : '' }}>Belum Diluluskan</option>
            </select>

            @if(request()->hasAny(['cari','semester','status','kelulusan']))
                <a href="{{ route('pelajar.index') }}" class="topbar-btn">
                    <i class="ti ti-x"></i> Reset
                </a>
            @endif
        </div>

        <div class="count-text">{{ count($pelajar) }} rekod</div>
    </div>
</form>

{{-- ═══ JADUAL PELAJAR ═══ --}}
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th width="36">#</th>
                <th>Pelajar</th>
                <th>Program</th>
                <th>No. Tel</th>
                <th>Sem</th>
                <th>GPA</th>
                <th>Kelulusan Mesyuarat</th>
                <th>Keluarga Angkat</th>
                <th>Tarikh Tamat Tajaan</th>
                <th width="90">Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pelajar as $i => $p)
            @php
                $pid      = $p['id_pelajar'];
                $gpa      = floatval($p['latest_gpa'] ?? 0);
                $gpaClass = $gpa >= 3.50 ? 'high' : ($gpa >= 3.00 ? 'mid' : ($gpa > 0 ? 'low' : ''));
                $pct      = $gpa > 0 ? min(($gpa / 4) * 100, 100) : 0;

                // Tajaan badge
                $tajaanLabel = '—';
                $tajaanClass = 'tiada';
                $tajaanIcon  = '';

                if (!empty($p['tarikh_tamat_ka'])) {
                    $tamat    = \Carbon\Carbon::parse($p['tarikh_tamat_ka']);
                    $bulanLagi = now()->diffInMonths($tamat, false);

                    if ($bulanLagi < 0) {
                        $tajaanLabel = 'Tamat';
                        $tajaanClass = 'tamat';
                        $tajaanIcon  = '';
                    } elseif ($bulanLagi <= 2) {
                        $tajaanLabel = $tamat->format('M Y');
                        $tajaanClass = 'hampir';
                        $tajaanIcon  = '<i class="ti ti-alert-triangle" style="font-size:10px"></i>';
                    } else {
                        $tajaanLabel = $tamat->format('M Y');
                        $tajaanClass = 'aktif';
                        $tajaanIcon  = '';
                    }
                } elseif (!empty($p['tarikh_tamat_tajaan'])) {
                    $tamat    = \Carbon\Carbon::parse($p['tarikh_tamat_tajaan']);
                    $bulanLagi = now()->diffInMonths($tamat, false);

                    if ($bulanLagi < 0) {
                        $tajaanLabel = 'Tamat';
                        $tajaanClass = 'tamat';
                    } elseif ($bulanLagi <= 2) {
                        $tajaanLabel = $tamat->format('M Y');
                        $tajaanClass = 'hampir';
                        $tajaanIcon  = '<i class="ti ti-alert-triangle" style="font-size:10px"></i>';
                    } else {
                        $tajaanLabel = $tamat->format('M Y');
                        $tajaanClass = 'aktif';
                    }
                }
            @endphp
            <tr onclick="window.location='{{ route('pelajar.show', $pid) }}'" style="cursor:pointer">

                {{-- # --}}
                <td style="color:var(--text-muted);font-size:12px">{{ $i + 1 }}</td>

                {{-- Nama + Matrik --}}
                <td>
                    <div class="student-name">{{ $p['nama_pelajar'] }}</div>
                    <div class="student-id">{{ $p['no_matrik'] ?? '—' }}</div>
                </td>

                {{-- Program --}}
                <td>
                    <div class="prog-text">{{ $p['program'] ?? $p['program_pengajian'] ?? '—' }}</div>
                </td>

                {{-- No. Tel (dari keluarga angkat) --}}
                <td>
                    <span class="tel-text">
                        {{ !empty($p['no_telefon_ka']) ? $p['no_telefon_ka'] : '—' }}
                    </span>
                </td>

                {{-- Semester --}}
                <td>
                    @if(!empty($p['semester']))
                        <span class="badge blue">{{ $p['semester'] }}</span>
                    @else
                        <span style="color:var(--text-muted)">—</span>
                    @endif
                </td>

                {{-- GPA dengan progress bar --}}
                <td>
                    @if($gpa > 0)
                    <div class="gpa-wrap">
                        <div class="gpa-bar-bg">
                            <div class="gpa-bar-fill {{ $gpaClass }}" style="width:{{ $pct }}%"></div>
                        </div>
                        <span class="gpa-num">{{ number_format($gpa, 2) }}</span>
                    </div>
                    @else
                        <span style="color:var(--text-muted);font-size:12px">—</span>
                    @endif
                </td>

                {{-- Kelulusan Mesyuarat (baharu) --}}
                <td>
                    @if(!empty($p['tarikh_mesyuarat_diluluskan']))
                        <span class="badge green">
                            {{ \Carbon\Carbon::parse($p['tarikh_mesyuarat_diluluskan'])->format('d M Y') }}
                        </span>
                    @else
                        <span class="badge gray">Belum</span>
                    @endif
                </td>

                {{-- Keluarga Angkat --}}
                <td>
                    @if(!empty($p['nama_keluarga']))
                        <div style="font-size:12px;font-weight:500;color:var(--text)">{{ $p['nama_keluarga'] }}</div>
                        @if(!empty($p['status_tajaan']))
                            <div style="font-size:10px;color:var(--text-muted)">{{ $p['status_tajaan'] }}</div>
                        @endif
                    @else
                        <span style="color:var(--text-muted);font-size:12px">—</span>
                    @endif
                </td>

                {{-- Tajaan (tarikh tamat) --}}
                <td>
                    <span class="tajaan-badge {{ $tajaanClass }}">
                        {!! $tajaanIcon !!}
                        {{ $tajaanLabel }}
                    </span>
                </td>

                {{-- Tindakan --}}
                <td onclick="event.stopPropagation()">
                    <div style="display:flex;gap:5px">
                        <a href="{{ route('pelajar.show', $pid) }}"
                           class="btn sm" title="Lihat">
                            <i class="ti ti-eye"></i>
                        </a>
                        <a href="{{ route('pelajar.edit', $pid) }}"
                           class="btn sm" title="Edit">
                            <i class="ti ti-pencil"></i>
                        </a>
                        <button
                            onclick="bukaPadam('{{ $pid }}', '{{ addslashes($p['nama_pelajar']) }}')"
                            class="btn sm danger" title="Padam">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align:center;padding:48px 20px">
                    <div style="color:var(--text-muted)">
                        <i class="ti ti-users-off" style="font-size:40px;display:block;margin-bottom:10px;opacity:.3"></i>
                        <p style="font-size:13px">Tiada rekod pelajar ditemui.</p>
                        @if(request()->hasAny(['cari','semester','status','kelulusan']))
                            <a href="{{ route('pelajar.index') }}"
                               style="color:var(--primary);font-size:13px;margin-top:6px;display:inline-block">
                                Reset carian
                            </a>
                        @else
                            <a href="{{ route('pelajar.create') }}"
                               style="color:var(--primary);font-size:13px;margin-top:6px;display:inline-block">
                                + Tambah Pelajar Pertama
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ═══ MODAL PADAM ═══ --}}
<div class="modal-overlay" id="modal-padam">
    <div class="modal" style="max-width:400px">
        <div class="modal-head">
            <h3>Sahkan Pemadaman</h3>
            <button class="modal-close" onclick="closeModal('modal-padam')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p style="font-size:13px;margin-bottom:14px;line-height:1.6">
                Anda pasti mahu memadam rekod pelajar
                <strong id="padam-nama"></strong>?
            </p>
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:var(--radius);
                        padding:10px 12px;font-size:12px;color:#991b1b">
                <i class="ti ti-alert-triangle"></i>
                Tindakan ini tidak boleh dibatalkan.
            </div>
        </div>
        <div class="modal-footer">
            <form method="POST" id="form-padam" style="display:contents">
                @csrf @method('DELETE')
                <button type="button" class="btn" onclick="closeModal('modal-padam')">Batal</button>
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
function bukaPadam(id, nama) {
    document.getElementById('padam-nama').textContent = nama;
    document.getElementById('form-padam').action = '/pelajar/' + id;
    openModal('modal-padam');
}
</script>
@endpush
