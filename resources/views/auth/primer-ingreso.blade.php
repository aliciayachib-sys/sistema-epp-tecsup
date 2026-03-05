<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activar Cuenta - EPP Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #e9ecef;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            padding: 1rem;
        }

        .card-setup {
            width: 100%;
            max-width: 460px;
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            background: white;
            overflow: hidden;
        }

        .header-setup {
            background: #003366;
            padding: clamp(1.25rem, 4vw, 1.875rem) clamp(1rem, 5vw, 1.875rem);
            text-align: center;
            color: white;
        }

        .header-setup i {
            font-size: clamp(2rem, 8vw, 3rem);
        }

        .btn-primary {
            background-color: #003366 !important;
            border: none;
            transition: 0.3s;
        }
        .btn-primary:hover {
            background-color: #002244 !important;
            transform: translateY(-1px);
        }

        .form-control:focus {
            border-color: #003366;
            box-shadow: 0 0 0 0.25rem rgba(0, 51, 102, 0.1);
        }

        @media (max-width: 400px) {
            .card-setup { border-radius: 14px; }
            .card-body-inner { padding: 1rem !important; }
        }
    </style>
</head>
<body>
    <div class="card card-setup">

        <!-- Header -->
        <div class="header-setup">
            <i class="bi bi-shield-lock-fill mb-2 d-block"></i>
            <h4 class="fw-bold mb-1">Activación de Cuenta</h4>
            <p class="small opacity-75 mb-0">Hola, {{ Auth::user()->name }}</p>
        </div>

        <!-- Body -->
        <div class="p-3 p-sm-4 card-body-inner">

            <!-- Aviso primer ingreso -->
            <div class="alert alert-warning border-0 d-flex align-items-start gap-2 mb-4" role="alert">
                <i class="bi bi-exclamation-circle-fill fs-5 flex-shrink-0 mt-1"></i>
                <div class="small">
                    Por seguridad, detectamos que es tu <strong>primer ingreso</strong>.
                    Debes cambiar tu contraseña temporal para continuar.
                </div>
            </div>

            <!-- Errores -->
            @if ($errors->any())
                <div class="alert alert-danger small border-0 shadow-sm mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Formulario -->
            <form action="{{ route('primer.ingreso.update') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Nueva Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" name="password"
                               class="form-control bg-light border-start-0"
                               placeholder="Mínimo 6 caracteres"
                               autocomplete="new-password"
                               required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted text-uppercase">Confirmar Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input type="password" name="password_confirmation"
                               class="form-control bg-light border-start-0"
                               placeholder="Repite la contraseña"
                               autocomplete="new-password"
                               required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 py-sm-3 rounded-pill fw-bold">
                    <i class="bi bi-check-circle me-2"></i>Guardar y Acceder al Sistema
                </button>
            </form>

            <p class="text-center text-muted small mt-3 mb-0">
                <i class="bi bi-info-circle me-1 opacity-50"></i>
                Esta pantalla solo aparece en tu primer inicio de sesión.
            </p>
        </div>
    </div>
</body>
</html>