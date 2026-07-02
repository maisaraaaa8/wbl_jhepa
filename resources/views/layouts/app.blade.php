<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Protege Bitara UPSI</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
<div class="app-layout">

    {{-- ===== SIDEBAR ===== --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="{{ asset('images/logobitara.jpg') }}" alt="Logo" style="width:36px;height:36px;border-radius:8px;object-fit:cover;flex-shrink:0;">
                <div>
                    <div class="logo-text">Protege Bitara UPSI</div>
                    <div class="logo-sub">UPSI · {{ date('Y') }}</div>
                </div>
            </div>
        </div>

        <nav>
            <div class="nav-section">
                <div class="nav-label">Utama</div>
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="ti ti-layout-dashboard"></i> Dashboard
                </a>
                <a href="{{ route('pelajar.index') }}" class="nav-item {{ request()->routeIs('pelajar.*') ? 'active' : '' }}">
                    <i class="ti ti-users"></i> Pelajar
                </a>
                <a href="{{ route('prestasi.index') }}" class="nav-item {{ request()->routeIs('prestasi.*') ? 'active' : '' }}">
                    <i class="ti ti-chart-bar"></i> Prestasi
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-label">Pengurusan</div>
                <a href="{{ route('keluarga.index') }}" class="nav-item {{ request()->routeIs('keluarga.*') ? 'active' : '' }}">
                    <i class="ti ti-home-heart"></i> Keluarga Angkat
                </a>
                <a href="{{ route('sumbangan.index') }}" class="nav-item {{ request()->routeIs('sumbangan.*') ? 'active' : '' }}">
                    <i class="ti ti-cash"></i> Sumbangan
                </a>
                <a href="{{ route('meeting.index') }}" class="nav-item {{ request()->routeIs('meeting.*') ? 'active' : '' }}">
                    <i class="ti ti-calendar-event"></i> Meeting Record
                </a>
                <a href="{{ route('import.index') }}" class="nav-item {{ request()->routeIs('import.*') ? 'active' : '' }}">
                    <i class="ti ti-file-import"></i> Import Excel
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-label">Sistem</div>
                <a href="{{ route('laporan.index') }}" class="nav-item {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                    <i class="ti ti-report"></i> Laporan
                </a>
                <a href="{{ route('notifikasi.index') }}" class="nav-item {{ request()->routeIs('notifikasi.*') ? 'active' : '' }}">
                    <i class="ti ti-bell"></i> Notifikasi
                    @if(isset($bilNotif) && $bilNotif > 0)
                        <span class="nav-badge">{{ $bilNotif }}</span>
                    @endif
                </a>
                <a href="{{ route('tetapan.maklum-balas') }}" class="nav-item {{ request()->routeIs('tetapan.maklum-balas') ? 'active' : '' }}">
                    <i class="ti ti-message-dots"></i> Maklum Balas
                </a>
                @if(session('peranan') === 'admin')
                <a href="{{ route('tetapan.index') }}" class="nav-item {{ request()->routeIs('tetapan.index') ? 'active' : '' }}">
                    <i class="ti ti-settings"></i> Tetapan & Akses
                </a>
                @endif
            </div>
        </nav>
    </aside>

    {{-- ===== MAIN ===== --}}
    <div class="main-content">

        {{-- TOPBAR --}}
        <header class="topbar">
            <button class="topbar-menu" id="menu-btn" onclick="toggleSidebar()" type="button">
                <i class="ti ti-menu-2"></i>
            </button>
            <div class="topbar-title">@yield('page-title', 'Dashboard')</div>

            <div class="topbar-actions">
                @yield('topbar-actions')

                {{-- Avatar + Dropdown --}}
                <div class="avatar" onclick="toggleAvatarMenu()" id="avatar-btn" style="background:#1e3a5f;border:1.5px solid rgba(255,255,255,0.2);"title="{{ session('nama') }}">
                    <i class="ti ti-user" style="font-size:18px"></i>
                    <div class="avatar-menu" id="avatar-menu">
                        <div class="avatar-info">
                            <div class="avatar-info-name">{{ session('nama', 'Pengguna') }}</div>
                            <div class="avatar-info-role">{{ session('peranan') === 'admin' ? 'Admin Penuh' : 'Baca Sahaja' }}</div>
                        </div>
                        <a href="{{ route('profile.index') }}" class="avatar-menu-item">
                            <i class="ti ti-user"></i> Profil Saya
                        </a>
                        @if(session('peranan') === 'admin')
                        <a href="{{ route('tetapan.index') }}" class="avatar-menu-item">
                            <i class="ti ti-settings"></i> Tetapan
                        </a>
                        @endif
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

        {{-- FLASH MESSAGES --}}
        <div class="flash-container">
            @if(session('success'))
            <div class="flash flash-success">
                <i class="ti ti-circle-check"></i>
                <div class="flash-body">{{ session('success') }}</div>
                <button class="flash-close" onclick="this.closest('.flash').remove()"><i class="ti ti-x"></i></button>
            </div>
            @endif
            @if(session('error'))
            <div class="flash flash-error">
                <i class="ti ti-alert-circle"></i>
                <div class="flash-body">{{ session('error') }}</div>
                <button class="flash-close" onclick="this.closest('.flash').remove()"><i class="ti ti-x"></i></button>
            </div>
            @endif
            @if(session('warning'))
            <div class="flash flash-warn">
                <i class="ti ti-alert-triangle"></i>
                <div class="flash-body">{{ session('warning') }}</div>
                <button class="flash-close" onclick="this.closest('.flash').remove()"><i class="ti ti-x"></i></button>
            </div>
            @endif
            @if($errors->any())
            <div class="flash flash-error">
                <i class="ti ti-alert-circle"></i>
                <div class="flash-body">
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
                <button class="flash-close" onclick="this.closest('.flash').remove()"><i class="ti ti-x"></i></button>
            </div>
            @endif
        </div>

        {{-- PAGE CONTENT --}}
        <main class="page-content">
            @yield('content')
        </main>
    </div>
</div>

{{-- MOBILE OVERLAY --}}
<div id="mobile-overlay" onclick="closeSidebar()"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:199"></div>

<script>
function toggleSidebar() {
    const sb = document.getElementById('sidebar');
    const ov = document.getElementById('mobile-overlay');
    if (window.innerWidth <= 640) {
        sb.classList.toggle('mobile-open');
        ov.style.display = sb.classList.contains('mobile-open') ? 'block' : 'none';
    } else {
        sb.classList.toggle('hidden');
        document.querySelector('.main-content').style.marginLeft =
            sb.classList.contains('hidden') ? '0' : 'var(--sidebar-w)';
    }
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('mobile-open');
    document.getElementById('mobile-overlay').style.display = 'none';
}
function toggleAvatarMenu() {
    document.getElementById('avatar-menu').classList.toggle('open');
}
document.addEventListener('click', e => {
    if (!e.target.closest('#avatar-btn'))
        document.getElementById('avatar-menu')?.classList.remove('open');
});

// Auto-dismiss flash
setTimeout(() => {
    document.querySelectorAll('.flash').forEach(el => el.remove());
}, 5000);

// Modal helpers
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('open');
    }
});
</script>
@stack('scripts')
</body>
</html>
