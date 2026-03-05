@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-0 fs-4 fs-md-2">Detalles del EPP</h2>
            <p class="text-muted mb-0 small">Información completa de {{ $epp->nombre }}</p>
        </div>
        <a href="{{ route('epps.index') }}" class="btn btn-outline-secondary shadow-sm flex-shrink-0">
            <i class="bi bi-arrow-left"></i> Volver al catálogo
        </a>
    </div>

    <div class="row g-4">
        <!-- Columna de Imagen -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="epp-image-container" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); display: flex; align-items: center; justify-content: center; min-height: 260px; max-height: 400px; height: 35vw;">
                    @if($epp->imagen)
                        <img src="{{ asset('storage/' . $epp->imagen) }}"
                             class="img-fluid"
                             style="max-height: 100%; max-width: 100%; object-fit: contain; padding: 20px;"
                             onerror="this.src='https://via.placeholder.com/400x400?text=Sin+imagen'">
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-box-seam" style="font-size: clamp(3rem, 8vw, 6rem); color: #ddd;"></i>
                            <p class="text-muted mt-3 mb-0">Sin imagen disponible</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ficha Técnica -->
            @if($epp->ficha_tecnica)
            <div class="mt-3">
                <a href="{{ asset('storage/' . $epp->ficha_tecnica) }}" target="_blank"
                   class="btn btn-primary w-100"
                   style="background-color: #003366; border: none;">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Descargar Ficha Técnica
                </a>
            </div>
            @endif
        </div>

        <!-- Columna de Detalles -->
        <div class="col-12 col-md-8">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-3 p-sm-4">

                    <!-- Encabezado -->
                    <div class="mb-4 pb-3" style="border-bottom: 2px solid #003366;">
                        <h3 class="fw-bold mb-2 fs-5 fs-sm-4" style="color: #333;">{{ Str::ucfirst($epp->nombre) }}</h3>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge" style="background-color: #003366;">{{ $epp->tipo }}</span>
                            @if($epp->departamento_id)
                                <span class="badge bg-info">{{ $epp->departamento->nombre ?? 'Departamento desconocido' }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Descripción -->
                    @if($epp->descripcion)
                    <div class="mb-4">
                        <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size: 0.8rem;">Descripción</h6>
                        <p class="mb-0">{{ $epp->descripcion }}</p>
                    </div>
                    @endif

                    <!-- Código y Marca -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <h6 class="fw-bold text-uppercase text-muted mb-1" style="font-size: 0.8rem;">Código Logística</h6>
                            <p class="fw-bold mb-0" style="color: #003366; font-size: clamp(0.95rem, 2vw, 1.1rem);">{{ $epp->codigo_logistica ?? '—' }}</p>
                        </div>
                        <div class="col-6">
                            <h6 class="fw-bold text-uppercase text-muted mb-1" style="font-size: 0.8rem;">Marca/Modelo</h6>
                            <p class="fw-bold mb-0">{{ $epp->marca_modelo ?? '—' }}</p>
                        </div>
                    </div>

                    <!-- Precio y Stock -->
                    <div class="row g-3 mb-4 rounded-3 mx-0 py-3" style="background: #f0f7ff;">
                        <div class="col-6">
                            <h6 class="fw-bold text-uppercase text-muted mb-1" style="font-size: 0.8rem;">Precio Unitario</h6>
                            <p class="text-success fw-bold mb-0" style="font-size: clamp(1rem, 3vw, 1.3rem);">${{ number_format($epp->precio ?? 0, 2) }}</p>
                        </div>
                        <div class="col-6">
                            <h6 class="fw-bold text-uppercase text-muted mb-1" style="font-size: 0.8rem;">Stock Disponible</h6>
                            <p class="fw-bold mb-0" style="font-size: clamp(1rem, 3vw, 1.3rem);">{{ $epp->cantidad ?? 0 }} unidades</p>
                        </div>
                    </div>

                    <!-- Vida útil y Frecuencia -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <h6 class="fw-bold text-uppercase text-muted mb-1" style="font-size: 0.8rem;">Vida Útil</h6>
                            <p class="fw-bold mb-0">{{ $epp->vida_util_meses }} meses</p>
                        </div>
                        <div class="col-6">
                            <h6 class="fw-bold text-uppercase text-muted mb-1" style="font-size: 0.8rem;">Frecuencia de Entrega</h6>
                            <p class="fw-bold mb-0">{{ $epp->frecuencia_entrega ?? '—' }}</p>
                        </div>
                    </div>

                    <!-- Estado -->
                    @if($epp->estado)
                    <div class="mb-4">
                        <h6 class="fw-bold text-uppercase text-muted mb-1" style="font-size: 0.8rem;">Estado</h6>
                        @php
                            $estadoColors = [
                                'disponible'    => ['bg' => '#d4edda', 'text' => '#155724'],
                                'agotado'       => ['bg' => '#f8d7da', 'text' => '#721c24'],
                                'descontinuado' => ['bg' => '#e2e3e5', 'text' => '#383d41'],
                            ];
                            $colors = $estadoColors[$epp->estado] ?? ['bg' => '#e2e3e5', 'text' => '#383d41'];
                        @endphp
                        <span class="badge"
                              style="background-color: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; font-size: 0.875rem; padding: 7px 12px;">
                            {{ ucfirst($epp->estado) }}
                        </span>
                    </div>
                    @endif

                    <!-- Fechas -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <h6 class="fw-bold text-uppercase text-muted mb-1" style="font-size: 0.8rem;">Fecha de Registro</h6>
                            <p class="fw-bold mb-0">{{ $epp->created_at ? $epp->created_at->format('d/m/Y') : '—' }}</p>
                        </div>
                        <div class="col-6">
                            <h6 class="fw-bold text-uppercase text-muted mb-1" style="font-size: 0.8rem;">Fecha de Vencimiento</h6>
                            <p class="fw-bold mb-0">{{ $epp->vencimiento_real ? $epp->vencimiento_real->format('d/m/Y') : '—' }}</p>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Botones de Acción -->
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                        <a href="{{ route('epps.edit', $epp->id) }}"
                           class="btn btn-primary d-flex align-items-center justify-content-center"
                           style="background-color: #003366; border: none;">
                            <i class="bi bi-pencil me-2"></i> Editar
                        </a>
                        <button type="button"
                                class="btn btn-danger d-flex align-items-center justify-content-center"
                                data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash me-2"></i> Eliminar
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mx-3 mx-sm-auto">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger bg-opacity-10 border-0">
                <h5 class="modal-title fw-bold text-danger" id="deleteModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-0">
                    ¿Estás seguro de que deseas eliminar el EPP <strong>"{{ $epp->nombre }}"</strong>?
                </p>
                <p class="text-muted small mt-2 mb-0">
                    Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="modal-footer border-0 flex-column flex-sm-row gap-2">
                <button type="button" class="btn btn-light w-100 w-sm-auto" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancelar
                </button>
                <form action="{{ route('epps.destroy', $epp->id) }}" method="POST" class="w-100 w-sm-auto">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-trash me-1"></i> Sí, Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Imagen adaptable por dispositivo */
    .epp-image-container {
        height: clamp(220px, 35vw, 400px) !important;
    }

    /* Botones full-width en móvil */
    @media (max-width: 575.98px) {
        .btn {
            width: 100%;
            justify-content: center;
        }
        .w-sm-auto { width: auto !important; }
    }

    /* Tablet */
    @media (min-width: 576px) {
        .w-sm-auto { width: auto !important; }
        .fs-md-2 { font-size: 1.5rem !important; }
    }

    /* Badge responsive */
    .badge {
        word-break: break-word;
        white-space: normal;
        text-align: left;
    }
</style>
@endsection