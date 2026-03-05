<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - EPP Sistema</title>
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
            background-color: #003366 !important;
            border: none;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-primary:hover {
            background-color: #002244 !important;
            transform: translateY(-1px);
        }

        .form-control {
            background-color: #f8f9fa !important;
            border: 1px solid #e9ecef !important;
        }
        .form-control:focus {
            border-color: #003366 !important;
            box-shadow: 0 0 0 0.25rem rgba(0, 51, 102, 0.1) !important;
        }
        .form-control.is-invalid {
            border-color: #dc3545 !important;
        }

        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: #003366;
        }

        @media (max-width: 400px) {
            .login-card { border-radius: 12px; }
        }
    </style>
</head>
<body>
    <div class="card login-card p-3 p-sm-4">

        <!-- Encabezado -->
        <div class="text-center mb-4">
            <div class="mb-2" style="color: #003366; font-size: clamp(1.75rem, 6vw, 2.25rem);">
                <i class="bi bi-envelope-paper-fill"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">Recuperar Contraseña</h4>
            <p class="text-muted small mb-0">Ingresa tu correo para recibir el enlace de recuperación</p>
        </div>

        <!-- Mensaje de éxito -->
        @if (session('status'))
            <div class="alert alert-success small border-0 shadow-sm mb-4 d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email -->
            <div class="mb-4">
                <label class="form-label fw-semibold text-secondary small text-uppercase mb-1">
                    Correo Electrónico
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" name="email"
                           class="form-control border-start-0 @error('email') is-invalid @enderror"
                           placeholder="admin@tecsup.edu.pe"
                           value="{{ old('email') }}"
                           required autofocus
                           autocomplete="email">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 shadow-sm rounded-pill mb-3">
                <i class="bi bi-send me-2"></i>Enviar Enlace de Recuperación
            </button>

            <div class="text-center">
                <a href="{{ route('login') }}" class="text-decoration-none small text-muted">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Login
                </a>
            </div>
        </form>

        <p class="text-center text-muted small mt-3 mb-0">
            <i class="bi bi-shield-check me-1 opacity-50"></i>
            Conexión segura — tus datos están protegidos
        </p>
    </div>
</body>
</html>