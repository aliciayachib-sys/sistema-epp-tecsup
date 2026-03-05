@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    <!-- Encabezado -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-0 fs-4">Registrar Departamento</h2>
            <p class="text-muted mb-0 small">Completa los datos para crear un nuevo departamento</p>
        </div>
        <a href="{{ route('departamentos.index') }}" class="btn btn-outline-secondary rounded-pill px-4 flex-shrink-0">
            <i class="bi bi-arrow-left me-2"></i>Volver al listado
        </a>
    </div>

    <!-- Errores de validación -->
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Por favor corrige los siguientes errores:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Formulario -->
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm p-3 p-sm-4" style="border-radius: 20px;">

                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                         style="width: 52px; height: 52px;">
                        <i class="bi bi-building text-primary fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">Nuevo Departamento</h5>
                        <small class="text-muted">Datos del departamento</small>
                    </div>
                </div>

                <form action="{{ route('departamentos.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Nombre</label>
                        <input type="text" name="nombre"
                               class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre') }}"
                               placeholder="Ej: Recursos Humanos"
                               required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted">Descripción</label>
                        <textarea name="descripcion"
                                  class="form-control @error('descripcion') is-invalid @enderror"
                                  rows="4"
                                  placeholder="Describe brevemente las funciones del departamento...">{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-bold py-2"
                                style="background-color: #003366; border: none; border-radius: 12px;">
                            <i class="bi bi-check-circle me-2"></i>Guardar Departamento
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection