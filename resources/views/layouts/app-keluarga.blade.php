<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Protege Bitara UPSI</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    {{-- Tema warna khusus untuk peranan Keluarga Angkat — navy & kuning keemasan --}}
    <style>
        :root {
            --primary:       #0f2a52;
            --primary-hover: #0b2140;
            --accent:        #e8a020;
        }
    </style>
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
                    <div class="logo-sub">Portal Keluarga Angkat · {{ date('Y') }}</div>
                </div>
            </div>
        </div>

        <nav>
            <div class="nav-section">
                <div class="nav-label">Utama</div>
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="ti ti-layout-dashboard"></i> Dashboard
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-label">Akaun</div>
                <a href="{{ route('keluarga-portal.pelajar') }}" class="nav-item {{ request()->routeIs('keluarga-portal.pelajar') ? 'active' : '' }}">
        <i class="ti ti-school"></i> Pelajar Saya
    </a>
    <a href="{{ route('keluarga-portal.sumbangan') }}" class="nav-item {{ request()->routeIs('keluarga-portal.sumbangan') ? 'active' : '' }}">
        <i class="ti ti-cash"></i> Sumbangan
    </a>
    <a href="{{ route('keluarga-portal.meeting') }}" class="nav-item {{ request()->routeIs('keluarga-portal.meeting') ? 'active' : '' }}">
        <i class="ti ti-calendar-event"></i> Meeting
    </a>
    <a href="{{ route('keluarga-portal.prestasi') }}" class="nav-item {{ request()->routeIs('keluarga-portal.prestasi') ? 'active' : '' }}">
                    <i class="ti ti-chart-bar"></i> Prestasi
                </a>
                <a href="{{ route('tetapan.maklum-balas') }}" class="nav-item {{ request()->routeIs('tetapan.maklum-balas') ? 'active' : '' }}">
                    <i class="ti ti-message-dots"></i> Maklum Balas
                </a>
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
                <div class="avatar" onclick="toggleAvatarMenu()" id="avatar-btn" style="background:var(--primary);border:1.5px solid rgba(255,255,255,0.2);" title="{{ session('nama') }}">
                    <i class="ti ti-user" style="font-size:18px"></i>
                    <div class="avatar-menu" id="avatar-menu">
                        <div class="avatar-info">
                            <div class="avatar-info-name">{{ session('nama', 'Keluarga Angkat') }}</div>
                            <div class="avatar-info-role">Keluarga Angkat</div>
                        </div>
                        <a href="{{ route('profile.index') }}" class="avatar-menu-item">
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

setTimeout(() => {
    document.querySelectorAll('.flash').forEach(el => el.remove());
}, 5000);

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
