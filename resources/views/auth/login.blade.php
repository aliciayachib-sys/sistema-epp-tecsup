<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EPP Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            padding: 1rem;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            background: white;
        }

        .btn-primary {
            background-color: #003366;
            border: none;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-primary:hover {
            background-color: #002244;
            transform: translateY(-1px);
        }

        .form-control:focus {
            border-color: #003366;
            box-shadow: 0 0 0 0.25rem rgba(0, 51, 102, 0.1);
        }

        .brand-logo {
            color: #003366;
            font-size: clamp(1.75rem, 6vw, 2.25rem);
        }

        /* Inputs sin borde doble al hacer focus */
        .input-group:focus-within .input-group-text {
            border-color: #003366;
        }
        .input-group:focus-within .form-control {
            border-color: #003366;
        }

        @media (max-width: 400px) {
            .login-card { border-radius: 12px; }
            .remember-row {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start !important;
            }
        }
    </style>
</head>
<body>
    <div class="card login-card p-3 p-sm-4">

        <!-- Logo y título -->
        <div class="text-center mb-4">
            <div class="brand-logo mb-2">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1">EPP Sistema</h3>
            <p class="text-muted small mb-0">Tecsup Norte — Acceso Administrativo</p>
        </div>

        <!-- Errores -->
        @if ($errors->any())
            <div class="alert alert-danger py-2 border-0 shadow-sm mb-4" style="font-size: 0.85rem;">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <!-- Email -->
            <div class="mb-3">
                <label class="form-label fw-semibold text-secondary small text-uppercase mb-1">
                    Correo Electrónico
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" name="email"
                           class="form-control bg-light border-start-0"
                           placeholder="admin@tecsup.edu.pe"
                           value="{{ old('email') }}"
                           required autofocus
                           autocomplete="email">
                </div>
            </div>

            <!-- Contraseña -->
            <div class="mb-4">
                <label class="form-label fw-semibold text-secondary small text-uppercase mb-1">
                    Contraseña
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-key"></i>
                    </span>
                    <input type="password" name="password"
                           class="form-control bg-light border-start-0"
                           placeholder="••••••••"
                           required
                           autocomplete="current-password">
                </div>
            </div>

            <!-- Recordarme + Olvidé clave -->
            <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2 remember-row">
                <div class="form-check mb-0">
                    <input type="checkbox" class="form-check-input" id="remember"
                           name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label small text-muted" for="remember">Recordarme</label>
                </div>
                <a href="{{ route('password.request') }}"
                   class="small text-decoration-none"
                   style="color: #003366;">
                    ¿Olvidaste tu clave?
                </a>
            </div>

            <!-- Botón -->
            <button type="submit" class="btn btn-primary w-100 py-2 shadow-sm">
                Iniciar Sesión <i class="bi bi-arrow-right-short ms-1"></i>
            </button>
        </form>

        <div class="text-center mt-4">
            <p class="small text-muted mb-0">
                <i class="bi bi-lock-fill me-1 opacity-50"></i>Solo personal autorizado
            </p>
        </div>
    </div>
</body>
</html>