<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Log Masuk — Protege Bitara UPSI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
/* ── BACKGROUND ── */
.auth-bg {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    position: relative;
    background: url('{{ asset("images/background.jpg") }}') center/cover no-repeat fixed;
    font-family: 'Figtree', sans-serif;
    overflow: hidden;
}
/* glow lembut di belakang kad supaya ada depth */
.auth-bg::before {
    content: '';
    position: absolute;
    width: 620px; height: 620px;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    background: radial-gradient(circle, rgba(240,180,41,0.16) 0%, rgba(0,86,204,0.10) 45%, transparent 72%);
    pointer-events: none;
    z-index: 0;
}

/* ── CARD ── */
.auth-card {
    width: 100%;
    max-width: 420px;
    background: rgba(255,255,255,0.98);
    border: 1px solid rgba(255,255,255,0.5);
    border-radius: 20px;
    box-shadow:
        0 28px 64px rgba(0,10,40,0.38),
        0 4px 16px rgba(0,0,0,0.14),
        0 0 0 1px rgba(255,255,255,0.06) inset;
    overflow: hidden;
    animation: fadeUp .4s ease;
    position: relative;
    z-index: 1;
}
@keyframes fadeUp {
    from { opacity:0; transform:translateY(24px); }
    to   { opacity:1; transform:translateY(0); }
}

/* ── HEADER (terang, sepadan dengan logo putih) ── */
.auth-header {
    background: linear-gradient(180deg, #ffffff 0%, #f4f7fc 100%);
    padding: 24px 32px 18px;
    text-align: center;
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid #eef1f7;
}
.auth-header::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, #001c4b 0%, #0056cc 45%, #f0b429 100%);
    box-shadow: 0 1px 8px rgba(0,86,204,0.35);
}

/* ── LOGO ROW ── */
.logo-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin-bottom: 13px;
}
.logo-divider {
    width: 1px;
    height: 36px;
    background: linear-gradient(180deg, transparent, #d3ddec, transparent);
}
.logo-img {
    height: 44px;
    width: auto;
    object-fit: contain;
    transition: transform .2s;
}
.logo-img:hover { transform: scale(1.05); }

/* ── TAJUK ── */
.auth-title {
    font-size: 19px;
    font-weight: 700;
    color: #001c4b;
    margin: 0 0 3px;
    letter-spacing: -.3px;
}
.auth-subtitle {
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
    margin: 0;
}
.auth-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: linear-gradient(135deg, #eaf1ff 0%, #dceafe 100%);
    border: 1px solid #c7dbfa;
    border-radius: 20px;
    padding: 3px 11px;
    font-size: 10.5px;
    color: #003b8e;
    font-weight: 600;
    margin-top: 9px;
}
.auth-badge i { color: #d99a1f; }

/* ── BODY ── */
.auth-body {
    padding: 22px 32px 20px;
}
.auth-body-title {
    font-size: 15px;
    font-weight: 600;
    color: #1a1a2e;
    margin-bottom: 3px;
}
.auth-body-sub {
    font-size: 12.5px;
    color: #6b7280;
    margin-bottom: 16px;
}

/* ── FORM FIELDS ── */
.field-group {
    margin-bottom: 14px;
}
.field-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 5px;
    letter-spacing: .02em;
}
.field-label .req { color: #ef4444; margin-left: 2px; }
.field-wrap {
    position: relative;
}
.field-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 16px;
    pointer-events: none;
    transition: color .2s;
}
.field-wrap:focus-within .field-icon { color: #0056cc; }
.field-input {
    width: 100%;
    padding: 9.5px 12px 9.5px 38px;
    border: 1.5px solid #dde3ec;
    border-radius: 10px;
    font-size: 13.5px;
    color: #1f2937;
    background: #f8f9fc;
    transition: border-color .2s, box-shadow .2s, background .2s;
    font-family: 'Figtree', sans-serif;
    outline: none;
}
.field-input:hover { border-color: #c7d2e0; }
.field-input:focus {
    border-color: #0056cc;
    background: #ffffff;
    box-shadow: 0 0 0 3.5px rgba(0,86,204,0.12);
}
.field-input.is-invalid {
    border-color: #ef4444;
    background: #fff5f5;
}
.field-input.is-invalid:focus {
    box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
}

/* butang show/hide password */
.toggle-pw {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #9ca3af;
    font-size: 18px;
    padding: 2px;
    border-radius: 4px;
    transition: color .15s;
    display: flex;
    align-items: center;
}
.toggle-pw:hover { color: #0056cc; }

.field-error {
    font-size: 12px;
    color: #ef4444;
    margin-top: 5px;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* ── REMEMBER + LUPA ── */
.auth-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 8px;
}
.remember-wrap {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 13px;
    color: #4b5563;
    cursor: pointer;
}
.remember-wrap input[type=checkbox] {
    width: 15px;
    height: 15px;
    accent-color: #0056cc;
    cursor: pointer;
}
.lupa-link {
    font-size: 13px;
    color: #0056cc;
    text-decoration: none;
    font-weight: 500;
    transition: color .15s;
}
.lupa-link:hover { color: #001c4b; text-decoration: underline; }

/* ── SUBMIT BUTTON ── */
.btn-login {
    width: 100%;
    padding: 11.5px;
    background: linear-gradient(135deg, #001c4b 0%, #0056cc 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 14.5px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-family: 'Figtree', sans-serif;
    letter-spacing: .01em;
    transition: opacity .2s, transform .1s, box-shadow .2s;
    position: relative;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(0,86,204,0.30);
}
.btn-login::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0);
    transition: background .2s;
}
.btn-login:hover::before { background: rgba(255,255,255,0.08); }
.btn-login:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(0,86,204,0.38); }
.btn-login:active { transform: translateY(0); }
.btn-login i { font-size: 18px; }

/* ── ALERT ── */
.auth-alert {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    padding: 10px 13px;
    border-radius: 10px;
    font-size: 12.5px;
    margin-bottom: 14px;
    animation: fadeUp .2s ease;
}
.auth-alert.error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #b91c1c;
}
.auth-alert i { font-size: 16px; flex-shrink: 0; margin-top: 1px; }

/* ── FOOTER ── */
.auth-footer {
    text-align: center;
    padding: 12px 32px 16px;
    border-top: 1px solid #f3f4f6;
    font-size: 11.5px;
    color: #9ca3af;
}
.auth-footer a { color: #0056cc; text-decoration: none; font-weight: 500; }
.auth-footer a:hover { text-decoration: underline; }

/* ── RESPONSIVE ── */
@media (max-width: 480px) {
    .auth-card { border-radius: 16px; }
    .auth-header, .auth-body { padding-left: 22px; padding-right: 22px; }
    .auth-title { font-size: 17px; }
    .logo-img { height: 38px; }
}
</style>
</head>
<body>
<div class="auth-bg">
    <div class="auth-card">

        {{-- HEADER --}}
        <div class="auth-header">
            <div class="logo-row">
                <img src="{{ asset('images/logoupsi.png') }}" alt="Logo UPSI" class="logo-img">
                <div class="logo-divider"></div>
                <img src="{{ asset('images/logobitara.jpg') }}" alt="Logo Bitara" class="logo-img">
            </div>
            <h1 class="auth-title">Protege Bitara UPSI</h1>
            <p class="auth-subtitle">Universiti Pendidikan Sultan Idris</p>
            <div class="auth-badge">
                <i class="ti ti-shield-check" style="font-size:12px"></i>
                Sistem Pengurusan Pelajar Anak Angkat
            </div>
        </div>

        {{-- BODY --}}
        <div class="auth-body">
            <div class="auth-body-title">Selamat Datang</div>
            <div class="auth-body-sub">Sila log masuk untuk meneruskan</div>

            {{ $slot }}
        </div>

        {{-- FOOTER --}}
        <div class="auth-footer">
            © {{ date('Y') }} UPSI &bull; Protege Bitara UPSI System
        </div>
    </div>
</div>
</body>
</html>
