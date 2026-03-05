@extends('layouts.app')

@section('content')
<style>
    @media (max-width: 575.98px) {
        .perfil-avatar { width: 48px !important; height: 48px !important; }
        .perfil-avatar i { font-size: 1.4rem !important; }
        .stat-number { font-size: 1.6rem !important; }
        .ayuda-card .d-flex { flex-direction: column; gap: 0.75rem; }
        .ayuda-footer { flex-direction: column; gap: 0.75rem; align-items: flex-start !important; }
        .ayuda-footer .btn { width: 100%; text-align: center; }
    }
</style>

<div class="container-fluid px-3 px-md-4 py-4">

    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark fs-4 fs-md-2">Perfil del Responsable</h2>
        <p class="text-muted mb-0 small">Gestión de cuenta y resumen de actividad del Centro de Seguridad</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> Hay errores en el formulario. Por favor revísalos.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">

        <!-- COLUMNA IZQUIERDA -->
        <div class="col-12 col-lg-5">

            <!-- 1️⃣ INFORMACIÓN DEL RESPONSABLE -->
            <div class="card border-0 shadow-sm p-3 p-sm-4 mb-4" style="border-radius: 20px;">
                <form action="{{ route('perfil.actualizar-datos') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="d-flex align-items-center mb-4">
                        <div class="perfil-avatar bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3 overflow-hidden flex-shrink-0"
                             style="width: 60px; height: 60px;">
                            @if($usuario->imagen_url)
                                <img src="{{ asset($usuario->imagen_url) }}" alt="Perfil" class="w-100 h-100 object-fit-cover">
                            @else
                                <i class="bi bi-person-vcard text-primary fs-3"></i>
                            @endif
                        </div>
                        <div class="overflow-hidden">
                            <h5 class="fw-bold mb-0 text-truncate">Información del Responsable</h5>
                            <small class="text-muted">Datos de identificación</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Foto de Perfil</label>
                        <input type="file" name="imagen"
                               class="form-control form-control-sm @error('imagen') is-invalid @enderror">
                        @error('imagen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase">Nombre Completo</label>
                        <input type="text" name="name" class="form-control fw-bold text-dark"
                               value="{{ old('name', $usuario->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase">DNI</label>
                        <input type="text" name="dni" class="form-control fw-bold text-dark"
                               value="{{ old('dni', $usuario->dni) }}">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase">Cargo</label>
                            <p class="fw-bold text-dark mb-0">Responsable del Centro de Seguridad</p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase">Sede</label>
                            <p class="fw-bold text-dark mb-0">Tecsup Norte</p>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-sm">Guardar Cambios</button>
                    </div>
                </form>

                <hr class="my-3">

                <!-- Formulario Email -->
                <form action="{{ route('perfil.actualizar-email') }}" method="POST">
                    @csrf
                    <label class="text-muted small fw-bold text-uppercase mb-2 d-block">Correo Electrónico</label>
                    <div class="input-group mb-2 flex-nowrap">
                        <input type="email" name="email"
                               class="form-control bg-light border-0"
                               value="{{ old('email', $usuario->email) }}" required>
                        <button class="btn btn-outline-primary flex-shrink-0" type="submit">Actualizar</button>
                    </div>
                </form>
            </div>

            <!-- 2️⃣ SEGURIDAD -->
            <div class="card border-0 shadow-sm p-3 p-sm-4" style="border-radius: 20px;">
                <div class="d-flex align-items-center mb-4">
                    <div class="perfil-avatar bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                         style="width: 60px; height: 60px;">
                        <i class="bi bi-shield-lock text-danger fs-3"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">Seguridad</h5>
                        <small class="text-muted">Acceso y contraseña</small>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-muted small fw-bold text-uppercase">Último Inicio de Sesión</label>
                    @if($ultimoAcceso)
                        <p class="fw-bold text-dark mb-0">
                            {{ $ultimoAcceso->created_at->format('d/m/Y') }}
                            <span class="text-muted fw-normal">a las {{ $ultimoAcceso->created_at->format('H:i A') }}</span>
                        </p>
                    @else
                        <p class="text-muted fst-italic mb-0">No registrado</p>
                    @endif
                </div>

                <button class="btn btn-outline-danger w-100 rounded-pill" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapsePassword">
                    <i class="bi bi-key me-2"></i>Cambiar Contraseña
                </button>

                <div class="collapse mt-3" id="collapsePassword">
                    <div class="card card-body bg-light border-0 rounded-3">
                        <form action="{{ route('perfil.cambiar-contrasena') }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <input type="password" name="password_actual"
                                       class="form-control border-0" placeholder="Contraseña Actual" required>
                            </div>
                            <div class="mb-2">
                                <input type="password" name="password_nueva"
                                       class="form-control border-0" placeholder="Nueva Contraseña" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" name="password_nueva_confirmation"
                                       class="form-control border-0" placeholder="Confirmar Nueva" required>
                            </div>
                            <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold">
                                Guardar Nueva Contraseña
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA -->
        <div class="col-12 col-lg-7">

            <!-- 3️⃣ RESUMEN DE ACTIVIDAD -->
            <div class="card border-0 shadow-sm p-3 p-sm-4 mb-4" style="border-radius: 20px;">
                <div class="d-flex align-items-center mb-4">
                    <div class="perfil-avatar bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                         style="width: 60px; height: 60px;">
                        <i class="bi bi-activity text-success fs-3"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">Resumen de Actividad</h5>
                        <small class="text-muted">Indicadores generales del sistema</small>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-6 col-md-6">
                        <div class="p-3 rounded-3 bg-light h-100">
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Total EPP Registrados</small>
                            <h2 class="fw-bold text-dark mt-2 mb-0 stat-number" style="font-size: clamp(1.5rem, 4vw, 2rem);">{{ $totalEppRegistrados }}</h2>
                            <small class="text-muted">Tipos de equipos en catálogo</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="p-3 rounded-3 bg-light h-100">
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Total EPP Asignados</small>
                            <h2 class="fw-bold text-primary mt-2 mb-0 stat-number" style="font-size: clamp(1.5rem, 4vw, 2rem);">{{ $totalEppAsignados }}</h2>
                            <small class="text-muted">Entregas realizadas</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="p-3 rounded-3 bg-light h-100">
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Total EPP Dados de Baja</small>
                            <h2 class="fw-bold text-danger mt-2 mb-0 stat-number" style="font-size: clamp(1.5rem, 4vw, 2rem);">{{ $totalEppBaja }}</h2>
                            <small class="text-muted">Unidades deterioradas/perdidas</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="p-3 rounded-3 bg-light h-100">
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Movimientos Sistema</small>
                            <h2 class="fw-bold text-info mt-2 mb-0 stat-number" style="font-size: clamp(1.5rem, 4vw, 2rem);">{{ $inventariadosRealizados }}</h2>
                            <small class="text-muted">Acciones registradas</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4️⃣ AYUDA Y SOPORTE -->
            <div class="card border-0 shadow-sm p-3 p-sm-4 ayuda-card"
                 style="border-radius: 20px; background: linear-gradient(135deg, #003a70 0%, #0056b3 100%);">
                <div class="d-flex align-items-start align-items-sm-center gap-3 text-white">
                    <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 50px; height: 50px; min-width: 50px;">
                        <i class="bi bi-life-preserver fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">¿Necesitas ayuda?</h5>
                        <p class="mb-0 opacity-75 small">Contacta con soporte técnico interno si tienes problemas con el sistema.</p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 text-white ayuda-footer">
                        <a href="mailto:soporte@tecsup.edu.pe" class="text-white text-decoration-none small">
                            <i class="bi bi-envelope me-2"></i>soporte@tecsup.edu.pe
                        </a>
                        <a href="{{ asset('manual/guia_usuario.pdf') }}" target="_blank"
                           class="btn btn-sm btn-light rounded-pill fw-bold px-3">
                            Ver Guía de Uso
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection