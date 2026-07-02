@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('topbar-actions')
    <a href="{{ route('import.index') }}" class="topbar-btn">
        <i class="ti ti-file-import"></i> Import Excel
    </a>
    <a href="{{ route('pelajar.create') }}" class="topbar-btn primary">
        <i class="ti ti-plus"></i> Tambah Pelajar
    </a>
@endsection

@push('styles')
<style>
.charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1.2fr 1fr;
    gap: 16px;
    margin-bottom: 20px;
}
.chart-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    transition: box-shadow .18s, transform .18s;
}
.chart-card:hover { box-shadow: 0 6px 18px rgba(0,0,0,.07); transform: translateY(-2px); }
.chart-card-title {
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 7px;
    margin-bottom: 4px;
}
.chart-card-title i { color: var(--text-muted); font-size: 15px; }
.chart-card-sub { font-size: 11.5px; color: var(--text-muted); margin-bottom: 10px; }
.chart-canvas-wrap { position: relative; flex: 1; min-height: 190px; }
.chart-legend-custom {
    display: flex; flex-wrap: wrap; gap: 8px 14px; margin-top: 10px; font-size: 11.5px;
}
.chart-legend-custom span { display: inline-flex; align-items: center; gap: 6px; color: var(--text-2); }
.chart-legend-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.chart-empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    flex: 1; color: var(--text-muted); font-size: 12px; gap: 6px;
}
.chart-empty i { font-size: 26px; opacity: .45; }

.table-header-row2 {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    padding: 0 18px 14px; border-bottom: 1px solid var(--border);
}
.filter-select {
    border: 1px solid var(--border-strong); border-radius: var(--radius);
    padding: 7px 10px; font-size: 12.5px; background: var(--surface); color: var(--text);
    outline: none; cursor: pointer;
}

@media (max-width: 1300px) { .charts-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 720px)  { .charts-grid { grid-template-columns: 1fr; } }

/* ── TEASER BAR "Pelajar Terkini" — footer ringkas, klik untuk pop-out ── */
.pelajar-teaser {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 14px 20px;
    display: flex; align-items: center; justify-content: space-between;
    gap: 14px; flex-wrap: wrap;
    cursor: pointer;
    transition: box-shadow .18s, transform .18s, border-color .18s;
}
.pelajar-teaser:hover {
    box-shadow: 0 6px 18px rgba(0,0,0,.07);
    transform: translateY(-2px);
    border-color: var(--border-strong);
}
.pelajar-teaser-left { display: flex; align-items: center; gap: 14px; min-width: 0; }
.teaser-avatars { display: flex; flex-shrink: 0; }
.teaser-avatars .t-av {
    width: 34px; height: 34px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: #fff;
    border: 2px solid var(--surface);
    margin-left: -10px;
}
.teaser-avatars .t-av:first-child { margin-left: 0; }
.teaser-avatars .t-more {
    width: 34px; height: 34px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: var(--text-2);
    background: var(--surface-2); border: 2px solid var(--surface);
    margin-left: -10px;
}
.teaser-text-title { font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 7px; }
.teaser-text-title i { color: var(--text-muted); font-size: 15px; }
.teaser-text-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.teaser-btn {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--primary); color: #fff; border: none;
    padding: 9px 16px; border-radius: var(--radius);
    font-size: 12.5px; font-weight: 500; cursor: pointer;
    flex-shrink: 0; transition: opacity .15s;
}
.teaser-btn:hover { opacity: .9; }
.teaser-btn i { transition: transform .18s; }
.pelajar-teaser:hover .teaser-btn i { transform: translateX(3px); }

/* ── Modal "Pelajar Terkini" — lebih lebar untuk muat jadual ── */
#modalPelajarTerkini .modal { max-width: 1080px; }
#modalPelajarTerkini .modal-body { padding: 0; }
#modalPelajarTerkini .table-wrap { border: none; border-radius: 0; margin-bottom: 0; }

.stat-card { position: relative; overflow: hidden; transition: box-shadow .18s, transform .18s; }
.stat-card:hover { box-shadow: 0 6px 18px rgba(0,0,0,.07); transform: translateY(-2px); }
.stat-icon-badge {
    position: absolute; top: 14px; right: 14px;
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px;
}
.stat-icon-badge.blue   { background: #e6f1fb; color: #185fa5; }
.stat-icon-badge.violet { background: #f3effe; color: #6234a1; }
.stat-icon-badge.green  { background: #eaf3de; color: #3b6d11; }
.stat-icon-badge.amber  { background: #faeeda; color: #854f0b; }

.leaderboard-list { display: flex; flex-direction: column; gap: 10px; flex: 1; justify-content: center; }
.lb-row { display: flex; align-items: center; gap: 10px; }
.lb-rank {
    width: 24px; height: 24px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: #fff;
}
.lb-rank.gold   { background: #e8a020; }
.lb-rank.silver { background: #9aa0a6; }
.lb-rank.bronze { background: #a9754f; }
.lb-info { flex: 1; min-width: 0; }
.lb-name { font-size: 12.5px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.lb-prog { font-size: 11px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.lb-gpa { font-size: 13px; font-weight: 700; color: #3b6d11; flex-shrink: 0; }
</style>
@endpush

@section('content')

{{-- ALERT TAJAAN HAMPIR TAMAT --}}
@if(($hampirTamat ?? 0) > 0)
<div class="alert-banner">
    <i class="ti ti-alert-triangle"></i>
    <div class="alert-banner-text">
        <strong>⚠ Peringatan Tamat Tajaan — {{ $hampirTamat }} Pelajar</strong>
        — tajaan tamat dalam tempoh 2 bulan.
    </div>
    <a href="{{ route('notifikasi.index') }}" class="alert-btn">Lihat →</a>
</div>
@endif

{{-- STAT CARDS --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon-badge blue"><i class="ti ti-users"></i></div>
        <div class="stat-label">Jumlah Pelajar</div>
        <div class="stat-value">{{ $jumlahPelajar ?? 0 }}</div>
        <div class="stat-sub">Aktif dalam program</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-badge violet"><i class="ti ti-home-heart"></i></div>
        <div class="stat-label">Keluarga Angkat</div>
        <div class="stat-value">{{ $jumlahKeluarga ?? 0 }}</div>
        <div class="stat-sub">{{ $belumBerpasangan ?? 0 }} pelajar belum berpasangan</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-badge green"><i class="ti ti-cash"></i></div>
        <div class="stat-label">Sumbangan Bulanan</div>
        <div class="stat-value">
            @php $s = $jumlahSumbangan ?? 0; @endphp
            {{ $s >= 1000 ? 'RM'.number_format($s/1000,1).'K' : 'RM'.number_format($s) }}
        </div>
        <div class="stat-sub">Purata RM{{ number_format($purataSumbangan ?? 0) }}/pelajar</div>
    </div>
    <div class="stat-card warn">
        <div class="stat-icon-badge amber"><i class="ti ti-clock-exclamation"></i></div>
        <div class="stat-label">Tajaan Hampir Tamat</div>
        <div class="stat-value">{{ $hampirTamat ?? 0 }}</div>
        <div class="stat-sub">Dalam 2 bulan</div>
    </div>
</div>

{{-- CARTA / STATISTIK --}}
<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-card-title"><i class="ti ti-building-bank"></i> Pelajar Ikut Fakulti</div>
        <div class="chart-card-sub">Taburan {{ $jumlahPelajar ?? 0 }} pelajar mengikut fakulti</div>
        @if(empty($fakultiChart['labels']))
            <div class="chart-empty"><i class="ti ti-chart-donut"></i> Tiada data fakulti lagi.</div>
        @else
            <div class="chart-canvas-wrap"><canvas id="chartFakulti"></canvas></div>
            <div class="chart-legend-custom" id="legendFakulti"></div>
        @endif
    </div>

    <div class="chart-card">
        <div class="chart-card-title"><i class="ti ti-chart-bar"></i> Status Prestasi</div>
        <div class="chart-card-sub">Berdasarkan GPA terkini semua pelajar</div>
        @if(($jumlahPelajar ?? 0) === 0)
            <div class="chart-empty"><i class="ti ti-chart-donut"></i> Tiada data prestasi lagi.</div>
        @else
            <div class="chart-canvas-wrap"><canvas id="chartStatus"></canvas></div>
            <div class="chart-legend-custom" id="legendStatus"></div>
        @endif
    </div>

    <div class="chart-card">
        <div class="chart-card-title"><i class="ti ti-trending-up"></i> Trend Sumbangan</div>
        <div class="chart-card-sub">Jumlah sumbangan 6 bulan terakhir (RM)</div>
        @if(collect($sumbanganTrend['data'] ?? [])->sum() == 0)
            <div class="chart-empty"><i class="ti ti-chart-line"></i> Tiada sumbangan direkodkan lagi.</div>
        @else
            <div class="chart-canvas-wrap"><canvas id="chartSumbangan"></canvas></div>
        @endif
    </div>

    <div class="chart-card">
        <div class="chart-card-title"><i class="ti ti-trophy"></i> Pelajar Cemerlang</div>
        <div class="chart-card-sub">Top 3 GPA terkini</div>
        @if(empty($topPelajar))
            <div class="chart-empty"><i class="ti ti-trophy"></i> Tiada rekod GPA lagi.</div>
        @else
            <div class="leaderboard-list">
                @php $medals = ['gold', 'silver', 'bronze']; @endphp
                @foreach($topPelajar as $i => $p)
                <div class="lb-row">
                    <div class="lb-rank {{ $medals[$i] ?? 'bronze' }}">{{ $i + 1 }}</div>
                    <div class="lb-info">
                        <div class="lb-name">{{ $p['nama_pelajar'] ?? '—' }}</div>
                        <div class="lb-prog">{{ $p['fakulti'] ?? '—' }}</div>
                    </div>
                    <div class="lb-gpa">{{ number_format($p['latest_gpa'], 2) }}</div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- PELAJAR TERKINI — bar ringkas, klik untuk pop-out senarai penuh --}}
<div class="pelajar-teaser" onclick="openModal('modalPelajarTerkini')">
    <div class="pelajar-teaser-left">
        <div class="teaser-avatars">
            @php $previewPelajar = array_slice($recentPelajar ?? [], 0, 4); @endphp
            @foreach($previewPelajar as $i => $p)
                @php
                    $namaP = $p['nama_pelajar'] ?? '?';
                    $initial = strtoupper(collect(explode(' ', $namaP))->map(fn($w) => $w[0] ?? '')->take(2)->implode(''));
                    $hue = crc32($namaP) % 360;
                @endphp
                <div class="t-av" style="background:hsl({{ $hue }},55%,45%)">{{ $initial }}</div>
            @endforeach
            @if(count($recentPelajar ?? []) > 4)
                <div class="t-more">+{{ count($recentPelajar) - 4 }}</div>
            @endif
        </div>
        <div>
            <div class="teaser-text-title"><i class="ti ti-users"></i> Pelajar Terkini</div>
            <div class="teaser-text-sub">{{ count($recentPelajar ?? []) }} pelajar didaftarkan terbaru — klik untuk lihat senarai</div>
        </div>
    </div>
    <button type="button" class="teaser-btn" onclick="event.stopPropagation(); openModal('modalPelajarTerkini')">
        Lihat Semua <i class="ti ti-chevron-right"></i>
    </button>
</div>

{{-- MODAL — senarai penuh Pelajar Terkini (pop-out, tak ganggu carta di atas) --}}
<div class="modal-overlay" id="modalPelajarTerkini">
    <div class="modal">
        <div class="modal-head">
            <h3><i class="ti ti-users"></i> Pelajar Terkini</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalPelajarTerkini')"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body">
            <div class="table-wrap">
                <div class="table-header">
                    <div class="section-title">
                        <i class="ti ti-users"></i> Senarai Pelajar
                    </div>
                    <div class="section-actions">
                        <div class="search-box" style="width:200px">
                            <i class="ti ti-search"></i>
                            <input type="text" placeholder="Cari pelajar..."
                                oninput="cariDashboard(this.value)">
                        </div>
                        <a href="{{ route('pelajar.index') }}" class="topbar-btn primary">
                            Lihat Semua
                        </a>
                    </div>
                </div>

                @if(!empty($fakultiList) && count($fakultiList) > 1)
                <div class="table-header-row2">
                    <label style="font-size:12px;color:var(--text-muted)">Tapis Fakulti:</label>
                    <select class="filter-select" id="filterFakulti" onchange="tapisFakulti(this.value)">
                        <option value="">Semua Fakulti</option>
                        @foreach($fakultiList as $f)
                            <option value="{{ $f }}">{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <table id="dashTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pelajar</th>
                            <th>Program</th>
                            <th>Fakulti</th>
                            <th>Sem</th>
                            <th>GPA</th>
                            <th>Keluarga Angkat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPelajar ?? [] as $i => $p)
                        @php $pid = $p['id_pelajar'] ?? null; @endphp
                        @if(!$pid) @continue @endif
                        <tr onclick="window.location='{{ route('pelajar.show', $pid) }}'" style="cursor:pointer" data-fakulti="{{ $p['fakulti'] ?? 'Tidak Dinyatakan' }}">
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <div class="student-name">{{ $p['nama_pelajar'] ?? '—' }}</div>
                                <div class="student-id">{{ $p['no_matrik'] ?? '' }}</div>
                            </td>
                            <td>{{ $p['program'] ?? $p['program_pengajian'] ?? '—' }}</td>
                            <td><span style="font-size:12px;color:var(--text-2)">{{ $p['fakulti'] ?? '—' }}</span></td>
                            <td>
                                <span class="badge blue">{{ $p['semester'] ?? '—' }}</span>
                            </td>
                            <td>
                                @php $gpa = floatval($p['latest_gpa'] ?? 0); @endphp
                                <div class="gpa-cell">
                                    <div class="gpa-bar-wrap">
                                        <div class="gpa-bar" style="width:{{ $gpa > 0 ? min(($gpa/4)*100, 100) : 0 }}%"></div>
                                    </div>
                                    {{ $gpa > 0 ? number_format($gpa, 2) : '—' }}
                                </div>
                            </td>
                            <td>{{ $p['keluarga_nama'] ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">
                                <i class="ti ti-users" style="font-size:32px;display:block;margin-bottom:8px"></i>
                                Tiada data pelajar. <a href="{{ route('pelajar.create') }}" style="color:#1e3a5f">Tambah pelajar</a>
                                atau <a href="{{ route('import.index') }}" style="color:#1e3a5f">import Excel</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function cariDashboard(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#dashTable tbody tr').forEach(r => {
        const matchText = r.textContent.toLowerCase().includes(q);
        const fakultiSelect = document.getElementById('filterFakulti');
        const matchFakulti = !fakultiSelect || !fakultiSelect.value || r.dataset.fakulti === fakultiSelect.value;
        r.style.display = (matchText && matchFakulti) ? '' : 'none';
    });
}

function tapisFakulti(val) {
    document.querySelectorAll('#dashTable tbody tr').forEach(r => {
        if (!r.dataset.fakulti) return; // baris "tiada data"
        r.style.display = (!val || r.dataset.fakulti === val) ? '' : 'none';
    });
}

// ── Modal "Pelajar Terkini" dibuka guna openModal('modalPelajarTerkini')
// (fungsi openModal/closeModal global sedia ada dalam layouts/app.blade.php) ──
</script>

<script>
// Chart.js — guna jsdelivr dahulu (CDN yang sama macam ikon Tabler, terbukti tak diblok
// oleh rangkaian anda). Kalau gagal, cuba cdnjs sebagai sandaran.
function loadScript(src, onload, onerror) {
    const s = document.createElement('script');
    s.src = src;
    s.onload = onload;
    s.onerror = onerror;
    document.head.appendChild(s);
}

function showChartLoadError() {
    document.querySelectorAll('.chart-canvas-wrap').forEach(wrap => {
        wrap.innerHTML = '<div class="chart-empty" style="height:100%"><i class="ti ti-plug-connected-x"></i>Gagal muatkan pustaka carta (Chart.js).<br>Semak sambungan internet / firewall rangkaian.</div>';
    });
}

loadScript(
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js',
    initCharts,
    () => loadScript(
        'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js',
        initCharts,
        showChartLoadError
    )
);

function initCharts() {
    if (typeof Chart === 'undefined') { showChartLoadError(); return; }

    const chartColors = ['#1e3a5f', '#e8a020', '#3b6d11', '#a32d2d', '#6234a1', '#185fa5', '#854f0b', '#5f5e5a'];

    @if(!empty($fakultiChart['labels']))
    (function () {
        const ctx = document.getElementById('chartFakulti');
        if (!ctx) return;
        const labels = @json($fakultiChart['labels']);
        const data   = @json($fakultiChart['data']);
        const colors = labels.map((_, i) => chartColors[i % chartColors.length]);

        new Chart(ctx, {
            type: 'doughnut',
            data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '65%',
                plugins: { legend: { display: false } }
            }
        });

        const legend = document.getElementById('legendFakulti');
        labels.forEach((l, i) => {
            const el = document.createElement('span');
            el.innerHTML = `<span class="chart-legend-dot" style="background:${colors[i]}"></span>${l} (${data[i]})`;
            legend.appendChild(el);
        });
    })();
    @endif

    @if(($jumlahPelajar ?? 0) > 0)
    (function () {
        const ctx = document.getElementById('chartStatus');
        if (!ctx) return;
        const labels = @json($statusChart['labels']);
        const data   = @json($statusChart['data']);
        const colors = ['#3b6d11', '#e8a020', '#a32d2d', '#c9c7c0'];

        new Chart(ctx, {
            type: 'doughnut',
            data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '65%',
                plugins: { legend: { display: false } }
            }
        });

        const legend = document.getElementById('legendStatus');
        labels.forEach((l, i) => {
            if (data[i] === 0) return;
            const el = document.createElement('span');
            el.innerHTML = `<span class="chart-legend-dot" style="background:${colors[i]}"></span>${l} (${data[i]})`;
            legend.appendChild(el);
        });
    })();
    @endif

    @if(collect($sumbanganTrend['data'] ?? [])->sum() > 0)
    (function () {
        const ctx = document.getElementById('chartSumbangan');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($sumbanganTrend['labels']),
                datasets: [{
                    label: 'Sumbangan (RM)',
                    data: @json($sumbanganTrend['data']),
                    borderColor: '#1e3a5f',
                    backgroundColor: 'rgba(30,58,95,0.08)',
                    fill: true,
                    tension: .35,
                    pointBackgroundColor: '#1e3a5f',
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: v => 'RM' + v } },
                    x: { grid: { display: false } }
                }
            }
        });
    })();
    @endif
}
</script>
@endpush
