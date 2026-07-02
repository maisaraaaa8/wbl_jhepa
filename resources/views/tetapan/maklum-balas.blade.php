@php
    $roleSemasa = session('role', session('peranan'));
    $layoutIkutPeranan = match($roleSemasa) {
        'pelajar'         => 'layouts.app-pelajar',
        'keluarga_angkat' => 'layouts.app-keluarga',
        default           => 'layouts.app',
    };
@endphp
@extends($layoutIkutPeranan)

@section('title', 'Maklum Balas')
@section('page-title', 'Maklum Balas')

@section('topbar-actions')
<button class="topbar-btn primary" onclick="openModal('modal-hantar')">
    <i class="ti ti-message-plus"></i> Hantar Maklum Balas
</button>
@endsection

@push('styles')
<style>
/* ── Stats ── */
.mb-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.mb-stat {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1rem 1.1rem;
    display: flex;
    align-items: center;
    gap: .8rem;
}
.mb-stat-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}
.mb-stat-icon.blue   { background:#eff6ff; color:#2563eb; }
.mb-stat-icon.green  { background:#dcfce7; color:#16a34a; }
.mb-stat-icon.amber  { background:#fffbeb; color:#d97706; }
.mb-stat-icon.purple { background:#f5f3ff; color:#7c3aed; }
.mb-stat-icon.red    { background:#fef2f2; color:#dc2626; }
.mb-stat-label { font-size:.68rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
.mb-stat-value { font-size:1.4rem; font-weight:700; color:var(--text); line-height:1.1; }

/* ── Jenis badge ── */
.jenis-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}
.jenis-badge.cadangan { background:#eff6ff; color:#2563eb; }
.jenis-badge.aduan    { background:#fef2f2; color:#dc2626; }
.jenis-badge.pujian   { background:#dcfce7; color:#16a34a; }
.jenis-badge.lain     { background:#f5f3ff; color:#7c3aed; }

/* ── Mesej card ── */
.mb-item {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1rem 1.1rem;
    margin-bottom: .75rem;
    transition: box-shadow .18s;
}
.mb-item:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); }
.mb-item-head {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: .5rem;
    flex-wrap: wrap;
}
.mb-item-meta {
    font-size: 11px;
    color: var(--text-muted);
    margin-left: auto;
}
.mb-item-mesej {
    font-size: 13px;
    color: var(--text);
    line-height: 1.55;
    white-space: pre-wrap;
    word-break: break-word;
}

/* ── Filter bar ── */
.filter-bar {
    display: flex;
    gap: .6rem;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}
.filter-bar select,
.filter-bar input[type="search"] {
    height: 36px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--surface);
    color: var(--text);
    font-size: 13px;
    padding: 0 .75rem;
}
.filter-bar select { cursor: pointer; }

/* ── Empty state ── */
.empty-mb {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--text-muted);
}
.empty-mb i { font-size: 2.5rem; display:block; margin-bottom:.6rem; }
.empty-mb p { font-size: 13px; }
</style>
@endpush

@section('content')

<div style="margin-bottom:20px">
    <h2 style="font-size:20px;font-weight:500;margin-bottom:4px">Maklum Balas</h2>
    <p style="font-size:13px;color:var(--text-secondary)">
        {{ $isAdmin ? 'Semua maklum balas yang diterima daripada pengguna sistem' : 'Hantar dan semak maklum balas anda' }}
    </p>
</div>

{{-- ── Statistik ── --}}
<div class="mb-stats">
    <div class="mb-stat">
        <div class="mb-stat-icon blue"><i class="ti ti-messages"></i></div>
        <div>
            <div class="mb-stat-label">Jumlah</div>
            <div class="mb-stat-value">{{ $stat['jumlah'] }}</div>
        </div>
    </div>
    <div class="mb-stat">
        <div class="mb-stat-icon green"><i class="ti ti-bulb"></i></div>
        <div>
            <div class="mb-stat-label">Cadangan</div>
            <div class="mb-stat-value">{{ $stat['cadangan'] }}</div>
        </div>
    </div>
    <div class="mb-stat">
        <div class="mb-stat-icon red"><i class="ti ti-alert-circle"></i></div>
        <div>
            <div class="mb-stat-label">Aduan</div>
            <div class="mb-stat-value">{{ $stat['aduan'] }}</div>
        </div>
    </div>
    <div class="mb-stat">
        <div class="mb-stat-icon amber"><i class="ti ti-star"></i></div>
        <div>
            <div class="mb-stat-label">Pujian</div>
            <div class="mb-stat-value">{{ $stat['pujian'] }}</div>
        </div>
    </div>
    <div class="mb-stat">
        <div class="mb-stat-icon purple"><i class="ti ti-dots-circle-horizontal"></i></div>
        <div>
            <div class="mb-stat-label">Lain-lain</div>
            <div class="mb-stat-value">{{ $stat['lain'] }}</div>
        </div>
    </div>
</div>

{{-- ── Filter ── --}}
<div class="filter-bar">
    <select id="filter-jenis" onchange="filterMaklumBalas()">
        <option value="">Semua Jenis</option>
        <option value="cadangan">Cadangan</option>
        <option value="aduan">Aduan</option>
        <option value="pujian">Pujian</option>
        <option value="lain">Lain-lain</option>
    </select>
    <input type="search" id="cari-mesej" placeholder="Cari dalam mesej…" oninput="filterMaklumBalas()" style="min-width:200px">
</div>

{{-- ── Senarai Maklum Balas ── --}}
<div id="senarai-mb">
    @forelse($senarai as $mb)
    @php
        $jenis = $mb['jenis'] ?? 'lain';
        $tarikh = $mb['created_at']
            ? \Carbon\Carbon::parse($mb['created_at'])->locale('ms')->translatedFormat('d M Y, g:ia')
            : '—';
        $ikonJenis = match($jenis) {
            'cadangan' => 'ti-bulb',
            'aduan'    => 'ti-alert-circle',
            'pujian'   => 'ti-star',
            default    => 'ti-dots-circle-horizontal',
        };
    @endphp
    <div class="mb-item" data-jenis="{{ $jenis }}" data-mesej="{{ strtolower($mb['mesej'] ?? '') }}">
        <div class="mb-item-head">
            <span class="jenis-badge {{ $jenis }}">
                <i class="ti {{ $ikonJenis }}"></i>
                {{ ucfirst($jenis) }}
            </span>
            <span class="mb-item-meta">{{ $tarikh }}</span>

            @if($isAdmin)
            <form method="POST" action="{{ route('tetapan.padam-maklum-balas', $mb['id']) }}"
                  style="display:inline;margin-left:4px"
                  onsubmit="return confirm('Padam maklum balas ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn danger" style="padding:2px 8px;font-size:11px" title="Padam">
                    <i class="ti ti-trash"></i>
                </button>
            </form>
            @endif
        </div>
        <div class="mb-item-mesej">{{ $mb['mesej'] }}</div>
    </div>
    @empty
    <div class="empty-mb" id="empty-default">
        <i class="ti ti-message-off"></i>
        <p>Tiada maklum balas lagi.</p>
        <button class="btn primary" onclick="openModal('modal-hantar')" style="margin-top:8px">
            <i class="ti ti-message-plus"></i> Hantar Maklum Balas Pertama
        </button>
    </div>
    @endforelse
</div>

{{-- Mesej tiada hasil carian --}}
<div id="empty-cari" style="display:none" class="empty-mb">
    <i class="ti ti-search-off"></i>
    <p>Tiada maklum balas sepadan dengan carian anda.</p>
</div>

{{-- ── Modal Hantar Maklum Balas ── --}}
<div class="modal-overlay" id="modal-hantar">
    <div class="modal" style="max-width:500px">
        <div class="modal-head">
            <h3><i class="ti ti-message-plus"></i> Hantar Maklum Balas</h3>
            <button class="modal-close" onclick="closeModal('modal-hantar')"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" action="{{ route('tetapan.hantar-maklum-balas') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:14px">
                    <label>Jenis Maklum Balas <span class="req">*</span></label>
                    <select name="jenis" required>
                        <option value="" disabled selected>-- Pilih jenis --</option>
                        <option value="cadangan" {{ old('jenis') == 'cadangan' ? 'selected' : '' }}>💡 Cadangan</option>
                        <option value="aduan"    {{ old('jenis') == 'aduan'    ? 'selected' : '' }}>⚠️ Aduan</option>
                        <option value="pujian"   {{ old('jenis') == 'pujian'   ? 'selected' : '' }}>⭐ Pujian</option>
                        <option value="lain"     {{ old('jenis') == 'lain'     ? 'selected' : '' }}>📝 Lain-lain</option>
                    </select>
                    @error('jenis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label>Mesej <span class="req">*</span></label>
                    <textarea name="mesej" rows="5" required
                        placeholder="Tulis maklum balas anda di sini (minimum 10 aksara)…"
                        style="width:100%;resize:vertical">{{ old('mesej') }}</textarea>
                    @error('mesej')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px">Maksimum 1,000 aksara</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeModal('modal-hantar')">Batal</button>
                <button type="submit" class="btn primary"><i class="ti ti-send"></i> Hantar</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Buka modal kalau ada error validasi
@if($errors->any())
    openModal('modal-hantar');
@endif

// Filter & cari
function filterMaklumBalas() {
    const jenis = document.getElementById('filter-jenis').value.toLowerCase();
    const cari  = document.getElementById('cari-mesej').value.toLowerCase();
    const items = document.querySelectorAll('#senarai-mb .mb-item');
    let nampak  = 0;

    items.forEach(el => {
        const elJenis = el.dataset.jenis || '';
        const elMesej = el.dataset.mesej || '';
        const cocokJenis = !jenis || elJenis === jenis;
        const cocokCari  = !cari  || elMesej.includes(cari);
        const tunjuk = cocokJenis && cocokCari;
        el.style.display = tunjuk ? '' : 'none';
        if (tunjuk) nampak++;
    });

    const emptyDefault = document.getElementById('empty-default');
    const emptyCari    = document.getElementById('empty-cari');

    if (emptyDefault) emptyDefault.style.display = 'none';
    if (emptyCari) emptyCari.style.display = nampak === 0 ? 'block' : 'none';
}

// Kira aksara dalam textarea
document.querySelector('textarea[name="mesej"]')?.addEventListener('input', function() {
    const max  = 1000;
    const guna = this.value.length;
    const info = this.nextElementSibling?.nextElementSibling
               || this.parentElement.querySelector('div[style*="11px"]');
    if (info) {
        info.textContent = `${guna} / ${max} aksara`;
        info.style.color = guna > max * 0.9 ? '#dc2626' : 'var(--text-muted)';
    }
});
</script>
@endpush
