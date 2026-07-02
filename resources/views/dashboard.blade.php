<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protege Bitara UPSI</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <div class="topbar">
    <i class="ti ti-menu-2" aria-hidden="true" style="font-size:18px;color:var(--text-muted);cursor:pointer"></i>
    <div class="topbar-title" id="page-title">Dashboard</div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <button class="topbar-btn" onclick="document.getElementById('import-modal').classList.add('open')">
        <i class="ti ti-file-import"></i> Import Excel
    </button>
    <button class="topbar-btn primary" onclick="nav('pelajar',null);openAddModal()">
        <i class="ti ti-plus"></i> Tambah Pelajar
    </button>

    <button class="topbar-btn" title="Log Keluar" onclick="window.location.href='/logout'">
        <i class="ti ti-logout"></i>
    </button>

    <div class="avatar" title="Profil Admin" onclick="nav('tetapan',null)">
        A
    </div>
</div>
    <style>
        /* Masukkan CSS dari fail .html anda di sini */
        :root { --font-sans: sans-serif; --surface-0: #f8f9fa; --surface-1: #f9f9f9; --surface-2: #ffffff; --text-primary: #333; --text-muted: #999; --border: #ddd; --border-strong: #ccc; --radius: 6px; --border-accent: #1e3a5f; --text-secondary: #666; }
        /* ... (tampal semua CSS anda di sini) ... */
    </style>
</head>
<body>

    <div class="app">
        </div>

    <script>
        // ... (tampal semua script anda di sini) ...
    </script>
</body>
</html>