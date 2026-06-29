@extends('layouts.app')

@section('title', 'Connexion - SATIC')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center"
     style="background: linear-gradient(135deg, #1a3a5c 0%, #2e86de 100%);">
    <div class="card shadow-lg" style="width: 420px; border-radius: 16px;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <div class="mb-3" style="font-size: 3rem; color: #1a3a5c;">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <h4 class="fw-bold" style="color: #1a3a5c;">UFR SATIC</h4>
                <p class="text-muted small">Système de Gestion des Absences<br>par Reconnaissance Faciale</p>
            </div>

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Adresse email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="votre@email.com" required autofocus>
                    </div>
                    @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                               placeholder="••••••••" required>
                        <button type="button" class="input-group-text" id="togglePwd">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label small" for="remember">Se souvenir de moi</label>
                </div>

                <button type="submit" class="btn w-100 text-white fw-semibold py-2"
                        style="background: linear-gradient(135deg, #1a3a5c, #2e86de); border-radius: 8px;">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                </button>
            </form>

            <p class="text-center text-muted small mt-4 mb-0">
                © {{ date('Y') }} UFR SATIC — Université
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('togglePwd').addEventListener('click', function () {
    const pwd  = document.querySelector('input[name="password"]');
    const icon = document.getElementById('eyeIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});
</script>
@endpush
@endsection