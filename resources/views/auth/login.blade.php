<x-guest-layout>

    {{-- STATUS (reset password dll) --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- ERROR --}}
    @if ($errors->any())
    <div class="auth-alert error">
        <i class="ti ti-alert-circle"></i>
        <div>{{ $errors->first() }}</div>
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        {{-- EMEL --}}
        <div class="field-group">
            <label class="field-label" for="email">
                Emel <span class="req">*</span>
            </label>
            <div class="field-wrap">
                <i class="ti ti-mail field-icon"></i>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="field-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    placeholder="nama@upsi.edu.my"
                    required
                    autofocus
                    autocomplete="username"
                >
            </div>
            @error('email')
            <div class="field-error">
                <i class="ti ti-alert-circle" style="font-size:13px"></i> {{ $message }}
            </div>
            @enderror
        </div>

        {{-- KATA LALUAN --}}
        <div class="field-group">
            <label class="field-label" for="password">
                Kata Laluan <span class="req">*</span>
            </label>
            <div class="field-wrap">
                <i class="ti ti-lock field-icon"></i>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="field-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    placeholder="Masukkan kata laluan"
                    required
                    autocomplete="current-password"
                    style="padding-right: 42px"
                >
                {{-- Butang tunjuk/sembunyi password --}}
                <button
                    type="button"
                    class="toggle-pw"
                    id="togglePw"
                    onclick="togglePassword()"
                    title="Tunjuk/Sembunyi Kata Laluan"
                >
                    <i class="ti ti-eye" id="pw-icon"></i>
                </button>
            </div>
            @error('password')
            <div class="field-error">
                <i class="ti ti-alert-circle" style="font-size:13px"></i> {{ $message }}
            </div>
            @enderror
        </div>

        {{-- INGAT SAYA + LUPA KATA LALUAN --}}
        <div class="auth-options">
            <label class="remember-wrap">
                <input type="checkbox" name="remember" id="remember_me">
                Ingat Saya
            </label>
            @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="lupa-link">
                Lupa kata laluan?
            </a>
            @endif
        </div>

        {{-- BUTANG LOG MASUK --}}
        <button type="submit" class="btn-login" id="loginBtn">
            <i class="ti ti-login"></i>
            Log Masuk
        </button>
    </form>
</x-guest-layout>

<script>
// Tunjuk / sembunyi kata laluan
function togglePassword() {
    const input  = document.getElementById('password');
    const icon   = document.getElementById('pw-icon');
    const isHide = input.type === 'password';

    input.type = isHide ? 'text' : 'password';
    icon.className = isHide ? 'ti ti-eye-off' : 'ti ti-eye';
}

// Loading state masa submit
document.getElementById('loginForm').addEventListener('submit', function () {
    const btn = document.getElementById('loginBtn');
    btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin 1s linear infinite"></i> Sedang masuk...';
    btn.disabled = true;
});
</script>

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
</style>
