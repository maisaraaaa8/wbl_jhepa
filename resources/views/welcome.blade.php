<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Protege Bitara UPSI</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Figtree', sans-serif; }

    body {
        min-height: 100vh;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 40px 6vw;
        overflow: hidden;
        background:
            linear-gradient(120deg, rgba(0,28,75,.42) 0%, rgba(0,70,160,.30) 55%, rgba(0,28,75,.15) 100%),
            url('{{ asset("images/background.jpg") }}') center/cover no-repeat fixed;
    }

    /* ── TEKS "SELAMAT DATANG" TERUS ATAS BACKGROUND (KIRI) ── */
    .welcome-text {
        position: absolute;
        left: 6vw;
        top: 50%;
        transform: translateY(-50%);
        max-width: 480px;
        color: #fff;
        z-index: 2;
        animation: fadeRight .6s ease;
    }
    @keyframes fadeRight {
        from { opacity: 0; transform: translateY(-50%) translateX(-20px); }
        to   { opacity: 1; transform: translateY(-50%) translateX(0); }
    }

    .welcome-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: rgba(255,255,255,.14);
        border: 1px solid rgba(255,255,255,.28);
        backdrop-filter: blur(6px);
        border-radius: 20px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .02em;
        margin-bottom: 22px;
    }

    .welcome-text h1 {
        font-size: clamp(34px, 4.2vw, 52px);
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -.5px;
        text-shadow: 0 4px 24px rgba(0,10,40,.35);
        margin-bottom: 16px;
    }
    .welcome-text h1 span { color: #ffcf6b; }

    .welcome-text p {
        font-size: 16px;
        line-height: 1.75;
        color: rgba(255,255,255,.88);
        text-shadow: 0 2px 12px rgba(0,10,40,.3);
        margin-bottom: 30px;
    }

    .welcome-stats {
        display: flex;
        gap: 34px;
    }
    .welcome-stats div { text-align: left; }
    .welcome-stats .n { font-size: 22px; font-weight: 800; margin-bottom: 2px; }
    .welcome-stats .l { font-size: 11.5px; color: rgba(255,255,255,.72); text-transform: uppercase; letter-spacing: .04em; }

    /* ── KAD LOG MASUK — DUDUK TEPI (KANAN) ── */
    .card {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 360px;
        margin-right: clamp(20px, 4vw, 70px);
        background: rgba(255,255,255,0.98);
        border-radius: 22px;
        box-shadow: 0 24px 64px rgba(0,0,0,0.28), 0 4px 16px rgba(0,0,0,0.12);
        overflow: hidden;
        animation: fadeUp .5s ease;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .header {
        background: linear-gradient(180deg, #ffffff 0%, #f4f7fc 100%);
        padding: 30px 28px 22px;
        text-align: center;
        position: relative;
        border-bottom: 1px solid #eef1f7;
    }
    .header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, #001c4b 0%, #0056cc 50%, #f0b429 100%);
    }

    .logo-row { display: flex; align-items: center; justify-content: center; gap: 14px; margin-bottom: 14px; }
    .logo-divider { width: 1px; height: 36px; background: #e3e8f0; }
    .logo-img { height: 44px; width: auto; object-fit: contain; transition: transform .2s; }
    .logo-img:hover { transform: scale(1.05); }

    .title { font-size: 19px; font-weight: 800; color: #001c4b; margin-bottom: 3px; letter-spacing: -.2px; }
    .subtitle { font-size: 12px; color: #6b7280; font-weight: 500; }

    .badge {
        display: inline-flex; align-items: center; gap: 5px;
        background: #eaf1ff; border: 1px solid #d6e4fb; border-radius: 20px;
        padding: 4px 11px; font-size: 10px; color: #003b8e; font-weight: 600;
        margin-top: 11px;
    }

    .body { padding: 24px 28px 24px; text-align: center; }

    .btn-login {
        width: 100%; padding: 13px;
        background: linear-gradient(135deg, #001c4b 0%, #003b8e 100%);
        color: #fff; border: none; border-radius: 11px;
        font-size: 15px; font-weight: 700; text-decoration: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        letter-spacing: .01em;
        transition: transform .15s, box-shadow .2s;
        box-shadow: 0 10px 22px rgba(0, 59, 142, .25);
    }
    .btn-login:hover { transform: translateY(-2px); box-shadow: 0 14px 28px rgba(0, 59, 142, .32); }
    .btn-login:active { transform: translateY(0); }
    .btn-login i { font-size: 17px; }

    .footer {
        text-align: center; padding: 13px 28px 18px;
        border-top: 1px solid #f3f4f6; font-size: 11px; color: #9ca3af;
    }

    /* ── RESPONSIVE: susun menegak bila skrin sempit ── */
    @media (max-width: 900px) {
        body { justify-content: center; padding: 24px; }
        .card { margin-right: 0; }
        .welcome-text {
            position: static;
            transform: none;
            max-width: 100%;
            text-align: center;
            margin-bottom: 28px;
        }
        .welcome-stats { justify-content: center; }
        .welcome-text h1 { font-size: 30px; }
        body { flex-direction: column; }
    }
    @media (max-width: 420px) {
        .header, .body { padding-left: 20px; padding-right: 20px; }
        .welcome-stats { gap: 22px; }
    }
</style>
</head>

<body>

    {{-- TEKS SELAMAT DATANG — TERUS ATAS BACKGROUND --}}
    <div class="welcome-text">
        <div class="welcome-eyebrow">
            <i class="ti ti-shield-check" style="font-size:13px"></i>
            Sistem Pengurusan Pelajar Anak Angkat
        </div>
        <h1>Selamat Datang ke <span>Protege Bitara</span> UPSI</h1>
        <p>
            Urus profil pelajar, kemajuan akademik, dan hubungan keluarga
            angkat dalam satu platform yang mudah dan telus.
        </p>
        <div class="welcome-stats">
            <div>
                <div class="n">100%</div>
                <div class="l">Telus</div>
            </div>
            <div>
                <div class="n">24/7</div>
                <div class="l">Boleh Diakses</div>
            </div>
            <div>
                <div class="n">1</div>
                <div class="l">Platform Bersepadu</div>
            </div>
        </div>
    </div>

    {{-- KAD LOG MASUK --}}
    <div class="card">

        <div class="header">
            <div class="logo-row">
                <img src="{{ asset('images/logoupsi.png') }}" alt="Logo UPSI" class="logo-img">
                <div class="logo-divider"></div>
                <img src="{{ asset('images/logobitara.jpg') }}" alt="Logo Bitara" class="logo-img">
            </div>
            <h1 class="title">Protege Bitara UPSI</h1>
            <p class="subtitle">Universiti Pendidikan Sultan Idris</p>
            <div class="badge">
                <i class="ti ti-lock" style="font-size:10px"></i>
                Akses Terhad &bull; Pengguna Berdaftar
            </div>
        </div>

        <div class="body">
            <a href="{{ route('login') }}" class="btn-login">
                <i class="ti ti-login"></i>
                Log Masuk
            </a>
        </div>

        <div class="footer">
            © {{ date('Y') }} UPSI &bull; Protege Bitara UPSI System
        </div>
    </div>

</body>
</html>
