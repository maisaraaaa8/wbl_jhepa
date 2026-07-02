<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akaun — Protege Bitara UPSI</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon">P</div>
            <h1>Daftar Akaun</h1>
            <p>Protege Bitara UPSI — Sistem Pengurusan Pelajar</p>
        </div>

        @if($errors->any())
        <div class="flash flash-error" style="margin-bottom:14px">
            <i class="ti ti-alert-circle"></i>
            <div class="flash-body">{{ $errors->first() }}</div>
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf
            <div class="form-group">
                <label>Nama Penuh <span class="req">*</span></label>
                <input type="text" name="nama" value="{{ old('nama') }}"
                    placeholder="cth: Ahmad Faris bin Razali" required
                    class="{{ $errors->has('nama') ? 'is-invalid' : '' }}">
            </div>
            <div class="form-group">
                <label>E-mel <span class="req">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}"
                    placeholder="nama@upsi.edu.my" required
                    class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
            </div>
            <div class="form-group">
                <label>Kata Laluan <span class="req">*</span></label>
                <input type="password" name="password"
                    placeholder="Minimum 6 aksara" required>
            </div>
            <div class="form-group">
                <label>Sahkan Kata Laluan <span class="req">*</span></label>
                <input type="password" name="password_confirmation"
                    placeholder="Ulang kata laluan" required>
            </div>
            <button type="submit" class="btn primary" style="width:100%;justify-content:center;padding:9px;margin-top:4px">
                <i class="ti ti-user-plus"></i> Daftar Akaun
            </button>
        </form>

        <hr class="auth-divider">
        <div class="auth-footer">
            Sudah ada akaun? <a href="{{ route('login') }}">Log masuk</a>
        </div>
    </div>
</div>
</body>
</html>
