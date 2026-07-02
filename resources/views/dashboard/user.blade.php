<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Protege Bitara UPSI</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="app-layout">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="logo-icon"><img src="{{ asset('images/logobitara.jpg') }}" alt="Logo Bitara" style="width:100%;height:100%;object-fit:cover;border-radius:6px;"></div>
                <div>
                    <div class="logo-text">Protege Bitara UPSI</div>
                    <div class="logo-sub">UPSI · {{ date('Y') }}</div>
                </div>
            </div>
        </div>
        <nav>
            <div class="nav-section">
                <div class="nav-label">Utama</div>
                <a href="{{ route('dashboard') }}" class="nav-item active">
                    <i class="ti ti-layout-dashboard"></i> Dashboard
                </a>
                <a href="{{ route('pelajar.index') }}" class="nav-item">
                    <i class="ti ti-users"></i> Pelajar
                </a>
                <a href="{{ route('prestasi.index') }}" class="nav-item">
                    <i class="ti ti-chart-bar"></i> Prestasi
                </a>
            </div>
            <div class="nav-section">
                <div class="nav-label">Pengurusan</div>
                <a href="{{ route('keluarga.index') }}" class="nav-item">
                    <i class="ti ti-home-heart"></i> Keluarga Angkat
                </a>
                <a href="{{ route('sumbangan.index') }}" class="nav-item">
                    <i class="ti ti-cash"></i> Sumbangan
                </a>
                <a href="{{ route('meeting.index') }}" class="nav-item">
                    <i class="ti ti-calendar-event"></i> Meeting Record
                </a>
            </div>
            <div class="nav-section">
                <div class="nav-label">Sistem</div>
                <a href="{{ route('laporan.index') }}" class="nav-item">
                    <i class="ti ti-report"></i> Laporan
                </a>
                <a href="{{ route('notifikasi.index') }}" class="nav-item">
                    <i class="ti ti-bell"></i> Notifikasi
                </a>
            </div>
        </nav>
    </aside>

    <div class="main-content">
        <header class="topbar">
            <button class="topbar-menu" onclick="toggleSidebar()" type="button">
                <i class="ti ti-menu-2"></i>
            </button>
            <div class="topbar-title">Dashboard</div>
            <div class="topbar-actions">
                <div class="avatar" onclick="toggleAvatarMenu()" id="avatar-btn">
                    <i class="ti ti-user" style="font-size:18px;color:#fff;"></i>
                    <div class="avatar-menu" id="avatar-menu">
                        <div class="avatar-info">
                            <div class="avatar-info-name">{{ session('nama', 'Pengguna') }}</div>
                            <div class="avatar-info-role">Baca Sahaja</div>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="avatar-menu-item">
                            <i class="ti ti-user"></i> Profil Saya
                        </a>
                        <div class="avatar-divider"></div>
                        <form method="POST" action="{{ route('logout') }}" style="display:contents">
                            @csrf
                            <button type="submit" class="avatar-menu-item danger">
                                <i class="ti ti-logout"></i> Log Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="page-content">

            @if(isset($hampirTamat) && count($hampirTamat) > 0)
            <div class="alert-banner">
                <i class="ti ti-alert-triangle"></i>
                <div class="alert-banner-text">
                    <strong>⚠ Peringatan Tamat Tajaan — {{ count($hampirTamat) }} Pelajar</strong>
                    {{ collect($hampirTamat)->pluck('pelajar_nama')->take(3)->implode(', ') }}
                    — tajaan tamat dalam tempoh 2 bulan.
                </div>
                <a href="{{ route('notifikasi.index') }}" class="alert-btn">Lihat →</a>
            </div>
            @endif

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Jumlah Pelajar</div>
                    <div class="stat-value">{{ $jumlahPelajar ?? 0 }}</div>
                    <div class="stat-sub">Aktif dalam program</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Keluarga Angkat</div>
                    <div class="stat-value">{{ $jumlahKeluarga ?? 0 }}</div>
                    <div class="stat-sub">{{ $belumBerpasangan ?? 0 }} belum berpasangan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Sumbangan Bulanan</div>
                    <div class="stat-value">
                        @php $s = $jumlahSumbangan ?? 0; @endphp
                        {{ $s >= 1000 ? 'RM'.number_format($s/1000,1).'K' : 'RM'.number_format($s) }}
                    </div>
                    <div class="stat-sub">Purata RM{{ number_format($purataSumbangan ?? 0) }}/pelajar</div>
                </div>
                <div class="stat-card warn">
                    <div class="stat-label">Tajaan Hampir Tamat</div>
                    <div class="stat-value">{{ isset($hampirTamat) ? count($hampirTamat) : 0 }}</div>
                    <div class="stat-sub">Dalam 2 bulan</div>
                </div>
            </div>

            <div class="table-wrap">
                <div class="table-header">
                    <div class="section-title"><i class="ti ti-users"></i> Pelajar Terkini</div>
                    <div class="section-actions">
                        <div class="search-box" style="width:200px">
                            <i class="ti ti-search"></i>
                            <input type="text" placeholder="Cari pelajar..." oninput="cariDashboard(this.value)">
                        </div>
                        <a href="{{ route('pelajar.index') }}" class="topbar-btn primary">Lihat Semua</a>
                    </div>
                </div>
                <table id="dashTable">
                    <thead>
                        <tr>
                            <th>#</th><th>Pelajar</th><th>Program</th>
                            <th>Sem</th><th>GPA</th><th>Keluarga Angkat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPelajar ?? [] as $i => $p)
                        @php $pid = $p['id_pelajar'] ?? null; @endphp
                        @if(!$pid) @continue @endif
                        <tr onclick="window.location='{{ route('pelajar.show', $pid) }}'" style="cursor:pointer">
                            <td>{{ $i+1 }}</td>
                            <td>
                                <div class="student-name">{{ $p['nama_pelajar'] ?? '—' }}</div>
                                <div class="student-id">{{ $p['no_matrik'] ?? '' }}</div>
                            </td>
                            <td>{{ $p['program'] ?? $p['program_pengajian'] ?? '—' }}</td>
                            <td><span class="badge blue">{{ $p['semester'] ?? '—' }}</span></td>
                            <td>
                                @php $gpa = floatval($p['latest_gpa'] ?? 0); @endphp
                                <div class="gpa-cell">
                                    <div class="gpa-bar-wrap">
                                        <div class="gpa-bar" style="width:{{ $gpa > 0 ? min(($gpa/4)*100,100) : 0 }}%"></div>
                                    </div>
                                    {{ $gpa > 0 ? number_format($gpa,2) : '—' }}
                                </div>
                            </td>
                            <td>{{ $p['keluarga_nama'] ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">Tiada data pelajar</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<div id="mobile-overlay" onclick="closeSidebar()"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:199"></div>

<script>
function toggleSidebar(){const sb=document.getElementById('sidebar');sb.classList.toggle('hidden');document.querySelector('.main-content').style.marginLeft=sb.classList.contains('hidden')?'0':'var(--sidebar-w)';}
function closeSidebar(){document.getElementById('sidebar').classList.remove('mobile-open');document.getElementById('mobile-overlay').style.display='none';}
function toggleAvatarMenu(){document.getElementById('avatar-menu').classList.toggle('open');}
document.addEventListener('click',e=>{if(!e.target.closest('#avatar-btn'))document.getElementById('avatar-menu')?.classList.remove('open');});
function cariDashboard(q){q=q.toLowerCase();document.querySelectorAll('#dashTable tbody tr').forEach(r=>{r.style.display=r.textContent.toLowerCase().includes(q)?'':'none';});}
</script>
</body>
</html>
