@extends('layouts.app')

@section('content')
<style>
    .page-title { font-size: clamp(1.25rem, 4vw, 1.75rem); }

    .card-reporte {
        border: none;
        border-radius: 20px;
        transition: transform 0.3s cubic-bezier(0.165, 0.84, 0.44, 1),
                    box-shadow  0.3s ease;
    }
    @media (hover: hover) {
        .card-reporte:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 32px rgba(0,0,0,0.1) !important;
        }
    }

    .card-reporte .card-body {
        padding: 32px 24px;
    }
    @media (min-width: 768px) {
        .card-reporte .card-body { padding: 40px; }
    }

    .icon-wrap {
        width: 72px; height: 72px;
        display: inline-flex;
        align-items: center; justify-content: center;
        border-radius: 50%;
        margin-bottom: 20px;
    }
    @media (min-width: 768px) {
        .icon-wrap { width: 80px; height: 80px; }
    }

    .card-reporte h4 {
        font-size: clamp(1rem, 3vw, 1.25rem);
    }
    .card-reporte p {
        font-size: clamp(0.85rem, 2vw, 0.95rem);
    }
    .card-reporte .btn {
        font-size: 0.875rem;
        padding: 10px 22px;
    }
</style>

<div class="container py-3 py-md-4">

    <div class="mb-4">
        <h2 class="page-title fw-bold text-dark mb-0">Reportes y Consultas</h2>
        <p class="text-muted small mb-0">Seleccione el tipo de reporte que desea generar.</p>
    </div>

    <div class="row g-3 g-md-4">

        {{-- Stock de Inventario --}}
        <div class="col-12 col-sm-6">
            <div class="card card-reporte shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="icon-wrap bg-success bg-opacity-10">
                        <i class="bi bi-box-seam text-success fs-1"></i>
                    </div>
                    <h4 class="fw-bold">Stock de Inventario</h4>
                    <p class="text-muted mb-4">Consulte la cantidad actual de todos los EPPs registrados en el almacén, incluyendo estado y categorías.</p>
                    <a href="{{ route('reportes.stock') }}" class="btn btn-success rounded-pill fw-bold">
                        Ver Reporte de Stock <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Asignaciones por Área --}}
        <div class="col-12 col-sm-6">
            <div class="card card-reporte shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="icon-wrap bg-primary bg-opacity-10">
                        <i class="bi bi-people text-primary fs-1"></i>
                    </div>
                    <h4 class="fw-bold">Asignaciones por Área</h4>
                    <p class="text-muted mb-4">Genere listados detallados por departamento para ver qué docentes tienen equipos asignados.</p>
                    <a href="{{ route('reportes.departamento') }}" class="btn btn-primary rounded-pill fw-bold">
                        Ver Reporte por Área <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Incidencias y Bajas --}}
        <div class="col-12 col-sm-6">
            <div class="card card-reporte shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="icon-wrap bg-danger bg-opacity-10">
                        <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
                    </div>
                    <h4 class="fw-bold">Incidencias y Bajas</h4>
                    <p class="text-muted mb-4">Consulte el listado de todos los EPPs que han sido reportados como dañados, perdidos o dados de baja.</p>
                    <a href="{{ route('reportes.incidencias') }}" class="btn btn-danger rounded-pill fw-bold">
                        Ver Reporte de Incidencias <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Planificación de Vida Útil --}}
        <div class="col-12 col-sm-6">
            <div class="card card-reporte shadow-sm h-100" style="background: linear-gradient(145deg, #ffffff, #f0f7ff);">
                <div class="card-body text-center">
                    <div class="icon-wrap bg-info bg-opacity-10">
                        <i class="bi bi-calendar-check text-info fs-1"></i>
                    </div>
                    <h4 class="fw-bold">Planificación de Vida Útil</h4>
                    <span class="badge bg-info text-dark mb-3 d-inline-block">Cronograma a Largo Plazo</span>
                    <p class="text-muted mb-4">Proyecte los vencimientos por año y mes. Ideal para planificar compras y renovaciones futuras (2026-2030).</p>
                    <a href="{{ route('reportes.vida_util') }}" class="btn btn-info text-white rounded-pill fw-bold">
                        Ver Cronograma Futuro <i class="bi bi-calendar3 ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection