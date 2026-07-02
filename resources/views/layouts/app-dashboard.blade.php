<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Protege Bitara UPSI</title>
    <style>
        /* Salin semua CSS dari <style> dalam protege_bitara_upsi_system.html ke sini */
        {!! file_get_contents(base_path('resources/views/css/dashboard-style.css')) !!} 
        /* Atau letak terus di sini */
    </style>
</head>
<body>
    <div class="app">
        <div class="sidebar">
            <div class="sidebar-header">...</div>
            </div>
        
        <div class="main">
            <div class="topbar">...</div>
            
            <div class="content">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>