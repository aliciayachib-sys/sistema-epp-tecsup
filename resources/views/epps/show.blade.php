@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Detalles del EPP</h2>
            <p class="text-muted">Información completa de {{ $epp->nombre }}</p>
        </div>
        <a href="{{ route('epps.index') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left"></i> Volver al catálogo
        </a>
    </div>

    <div class="row">
        <!-- Columna de Imagen -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="epp-image-container" style="height: 400px; background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); display: flex; align-items: center; justify-content: center;">
                    @if($epp->imagen)
                        <img src="{{ asset('storage/' . $epp->imagen) }}" 
                             class="img-fluid"
                             style="max-height: 100%; max-width: 100%; object-fit: contain; padding: 20px;"
                             onerror="this.src='https://via.placeholder.com/400x400?text=Sin+imagen'">
                    @else
                        <div class="text-center">
                            <i class="bi bi-box-seam" style="font-size: 6rem; color: #ddd;"></i>
                            <p class="text-muted mt-3">Sin imagen disponible</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ficha Técnica -->
            @if($epp->ficha_tecnica)
            <div class="mt-3">
                <a href="{{ asset('storage/' . $epp->ficha_tecnica) }}" target="_blank" class="btn btn-primary w-100" style="background-color: #003366; border: none;">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Descargar Ficha Técnica
                </a>
            </div>
            @endif
        </div>

        <!-- Columna de Detalles -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 12px;">
                <div class="card-body">
                    <!-- Encabezado -->
                    <div class="mb-4 pb-3" style="border-bottom: 2px solid #003366;">
                        <h3 class="fw-bold mb-2" style="color: #333;">{{ Str::ucfirst($epp->nombre) }}</h3>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="badge" style="background-color: #003366;">{{ $epp->tipo }}</span>
                            @if($epp->departamento_id)
                                <span class="badge bg-info">{{ $epp->departamento->nombre ?? 'Departamento desconocido' }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Descripción -->
                    @if($epp->descripcion)
                    <div class="mb-4">
                        <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Descripción</h6>
                        <p>{{ $epp->descripcion }}</p>
                    </div>
                    @endif

                    <!-- Información de Código y Marca -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Código Logística</h6>
                            <p class="fw-bold" style="color: #003366; font-size: 1.1rem;">{{ $epp->codigo_logistica ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Marca/Modelo</h6>
                            <p class="fw-bold">{{ $epp->marca_modelo ?? '—' }}</p>
                        </div>
                    </div>

                    <!-- Precio y Stock -->
                    <div class="row mb-4" style="background: #f0f7ff; padding: 16px; border-radius: 8px;">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Precio Unitario</h6>
                            <p class="text-success fw-bold" style="font-size: 1.3rem;">${{ number_format($epp->precio ?? 0, 2) }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Stock Disponible</h6>
                            <p class="fw-bold" style="font-size: 1.3rem;">{{ $epp->cantidad ?? 0 }} unidades</p>
                        </div>
                    </div>

                    <!-- Información de Uso -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Vida Útil</h6>
                            <p class="fw-bold">{{ $epp->vida_util_meses }} meses</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Frecuencia de Entrega</h6>
                            <p class="fw-bold">{{ $epp->frecuencia_entrega ?? '—' }}</p>
                        </div>
                    </div>

                    <!-- Estado -->
                    @if($epp->estado)
                    <div class="mb-4">
                        <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Estado</h6>
                        <p>
                            @php
                                $estadoColors = [
                                    'disponible' => ['bg' => '#d4edda', 'text' => '#155724'],
                                    'agotado' => ['bg' => '#f8d7da', 'text' => '#721c24'],
                                    'descontinuado' => ['bg' => '#e2e3e5', 'text' => '#383d41'],
                                ];
                                $colors = $estadoColors[$epp->estado] ?? ['bg' => '#e2e3e5', 'text' => '#383d41'];
                            @endphp
                            <span class="badge" style="background-color: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; font-size: 0.9rem; padding: 8px 12px;">
                                {{ ucfirst($epp->estado) }}
                            </span>
                        </p>
                    </div>
                    @endif

                    <!-- Fechas -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Fecha de Registro</h6>
                            <p class="fw-bold">{{ $epp->created_at ? $epp->created_at->format('d/m/Y') : '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Fecha de Vencimiento</h6>
                            <p class="fw-bold">{{ $epp->vencimiento_real ? $epp->vencimiento_real->format('d/m/Y') : '—' }}</p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Botones de Acción -->
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('epps.edit', $epp->id) }}" class="btn btn-primary d-flex align-items-center" style="background-color: #003366; border: none;">
                            <i class="bi bi-pencil me-2"></i> Editar
                        </a>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
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
    <div class="modal-dialog modal-dialog-centered">
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
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancelar
                </button>
                <form action="{{ route('epps.destroy', $epp->id) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Sí, Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .badge-role {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.85rem;
    }
</style>
@endsection
