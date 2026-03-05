@extends('layouts.app')

@section('content')
<style>
    :root {
        --dark-primary: #0f172a;
        --accent-blue: #2563eb;
    }

    /* ── TYPOGRAPHY ── */
    .display-year {
        font-size: clamp(1.8rem, 6vw, 2.5rem);
        font-weight: 800;
        color: var(--dark-primary);
        letter-spacing: -2px;
    }
    .month-badge {
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        letter-spacing: 0.5px;
    }
    .fw-black { font-weight: 900; }

    /* ── YEAR MARKER ── */
    .year-marker {
        position: relative;
        padding-left: 1.25rem;
    }
    @media (min-width: 768px) {
        .year-marker { padding-left: 2.5rem; }
    }
    .year-marker::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 4px;
        background: linear-gradient(to bottom, var(--accent-blue), transparent);
        border-radius: 4px;
    }

    /* ── TIMELINE CARD ── */
    .timeline-card {
        border: none;
        border-radius: 16px;
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
        background: rgba(255,255,255,0.95);
    }
    @media (hover: hover) {
        .timeline-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
        }
    }
    .table tbody tr:last-child { border-bottom: none !important; }

    /* ── MOBILE CARDS ── */
    .card-asignacion-vida {
        border: none;
        border-radius: 14px;
    }
    .info-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #f1f3f5;
        border-radius: 8px;
        padding: 2px 8px;
        font-size: 0.73rem;
        color: #495057;
    }

    /* ── HEADER ── */
    .page-header-row {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-bottom: 40px;
    }
    @media (min-width: 992px) {
        .page-header-row {
            flex-direction: row;
            align-items: flex-start;
            justify-content: space-between;
        }
    }
    .header-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    @media (min-width: 576px) {
        .header-actions { align-items: flex-end; }
    }
    .search-form { width: 100%; max-width: 420px; }

    /* ── STATUS CHIPS ── */
    .status-chip {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 10px;
        font-size: 0.78rem;
    }
</style>

<div class="container py-4 py-md-5">

    {{-- ── PAGE HEADER ── --}}
    <div class="page-header-row">
        <div>
            <h6 class="text-primary fw-bold text-uppercase mb-2" style="letter-spacing:2px; font-size:.75rem;">
                Gestión de Activos
            </h6>
            <h1 class="fw-black text-dark mb-2" style="font-size: clamp(1.5rem, 5vw, 2.5rem); letter-spacing:-1px;">
                Master Plan de Vida Útil
            </h1>
            <p class="text-muted" style="font-size:.92rem; max-width:420px;">
                Proyección de renovaciones basada en las asignaciones actuales al personal docente y administrativo.
            </p>
        </div>

        <div class="header-actions">
            {{-- Search --}}
            <form action="{{ route('reportes.vida_util') }}" method="GET" class="search-form">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search"
                           class="form-control border-start-0 ps-0"
                           placeholder="Buscar docente o DNI..."
                           value="{{ $search ?? '' }}">
                    @if(request('search'))
                        <a href="{{ route('reportes.vida_util') }}"
                           class="btn btn-light border" title="Limpiar">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                    <button class="btn btn-primary" type="submit">Buscar</button>
                </div>
            </form>

            {{-- Nav buttons --}}
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('reportes.index') }}" class="btn btn-white border px-4 fw-bold rounded-pill">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Menú
                </a>
                <button onclick="window.print()" class="btn btn-dark px-4 fw-bold rounded-pill">
                    <i class="bi bi-printer me-2"></i>Exportar
                </button>
            </div>
        </div>
    </div>

    {{-- ── EMPTY STATE ── --}}
    @if($proyeccionPorAnio->isEmpty())
        <div class="card p-5 text-center border-0 shadow-sm rounded-4">
            <div class="py-4">
                <i class="bi bi-calendar-x display-1 text-light"></i>
                <h3 class="mt-4 fw-bold">Sin datos para proyectar</h3>
                @if(request('search'))
                    <p class="text-muted">No se encontraron asignaciones para "<strong>{{ request('search') }}</strong>".</p>
                @else
                    <p class="text-muted">No hay asignaciones activas para calcular renovaciones.</p>
                @endif
            </div>
        </div>

    @else
        @foreach($proyeccionPorAnio as $anio => $asignaciones)
        <div class="year-marker mb-5">

            {{-- Year heading --}}
            <div class="d-flex align-items-baseline mb-3 mb-md-4 gap-2">
                <span class="display-year">{{ $anio }}</span>
                <span class="text-muted fw-medium small">Renovaciones Programadas</span>
            </div>

            {{-- ── TABLE (md and up) ── --}}
            <div class="card timeline-card shadow-sm overflow-hidden d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted small text-uppercase fw-bold">
                                <th class="ps-4 py-3">Mes Vencimiento</th>
                                <th>Personal / Área</th>
                                <th>EPP a Renovar</th>
                                <th>Fecha Entrega</th>
                                <th>Vida Útil</th>
                                <th class="text-center">Estado Actual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asignaciones as $item)
                            @php
                                $fecha  = \Carbon\Carbon::parse($item->fecha_vencimiento);
                                $hoy    = now();
                                $dias   = (int) $hoy->diffInDays($fecha, false);
                                $meses  = (int) $hoy->diffInMonths($fecha, false);
                            @endphp
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-4" style="width:150px;">
                                    <div class="month-badge mb-1 text-center">{{ $fecha->translatedFormat('F') }}</div>
                                    <div class="text-center small text-muted fw-semibold">{{ $fecha->format('d/m/Y') }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->personal->nombre_completo ?? 'N/A' }}</div>
                                    <span class="badge bg-light text-secondary border mt-1">
                                        {{ $item->personal->departamento->nombre ?? 'Sin Área' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">{{ $item->epp->nombre ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $item->epp->codigo_logistica ?? '' }}</small>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($item->fecha_entrega)->format('d/m/Y') }}</td>
                                <td>{{ $item->epp->vida_util_meses ?? 12 }} meses</td>
                                <td class="text-center">
                                    @if($fecha->isPast())
                                        <div class="px-3 py-2 bg-danger bg-opacity-10 text-danger rounded-3 d-inline-block">
                                            <span class="fw-black small"><i class="bi bi-shield-exclamation me-1"></i>EXPIRÓ</span>
                                        </div>
                                    @elseif($dias < 30)
                                        <div class="px-3 py-2 bg-warning bg-opacity-10 text-dark rounded-3 d-inline-block">
                                            <span class="fw-black h6 mb-0">{{ $dias }} Días</span>
                                            <div class="small fw-bold opacity-75">RENOVAR</div>
                                        </div>
                                    @else
                                        <div class="px-3 py-2 bg-success bg-opacity-10 text-success rounded-3 d-inline-block">
                                            <span class="fw-black h6 mb-0">{{ $meses }} Meses</span>
                                            <div class="small fw-bold opacity-75">VIGENTE</div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── CARDS (mobile, < md) ── --}}
            <div class="d-md-none d-flex flex-column gap-3">
                @foreach($asignaciones as $item)
                @php
                    $fecha  = \Carbon\Carbon::parse($item->fecha_vencimiento);
                    $hoy    = now();
                    $dias   = (int) $hoy->diffInDays($fecha, false);
                    $meses  = (int) $hoy->diffInMonths($fecha, false);
                @endphp
                <div class="card card-asignacion-vida shadow-sm">
                    <div class="card-body p-3">

                        {{-- Top: EPP + estado --}}
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div class="fw-bold text-primary flex-grow-1">{{ $item->epp->nombre ?? 'N/A' }}</div>
                            @if($fecha->isPast())
                                <span class="status-chip bg-danger bg-opacity-10 text-danger fw-black flex-shrink-0" style="font-size:.72rem;">
                                    <i class="bi bi-shield-exclamation me-1"></i>EXPIRÓ
                                </span>
                            @elseif($dias < 30)
                                <span class="status-chip bg-warning bg-opacity-10 text-dark fw-black flex-shrink-0" style="font-size:.72rem;">
                                    {{ $dias }}d · RENOVAR
                                </span>
                            @else
                                <span class="status-chip bg-success bg-opacity-10 text-success fw-black flex-shrink-0" style="font-size:.72rem;">
                                    {{ $meses }}m · VIGENTE
                                </span>
                            @endif
                        </div>

                        {{-- Personal --}}
                        <div class="small fw-semibold mb-1">
                            <i class="bi bi-person me-1 text-muted"></i>
                            {{ $item->personal->nombre_completo ?? 'N/A' }}
                        </div>

                        {{-- Chips --}}
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <span class="info-chip">
                                <i class="bi bi-building"></i>
                                {{ $item->personal->departamento->nombre ?? 'Sin Área' }}
                            </span>
                            <span class="info-chip">
                                <i class="bi bi-calendar-event"></i>
                                Vence: {{ $fecha->format('d/m/Y') }}
                            </span>
                            <span class="info-chip">
                                <i class="bi bi-box-arrow-in-down"></i>
                                Entrega: {{ \Carbon\Carbon::parse($item->fecha_entrega)->format('d/m/Y') }}
                            </span>
                            <span class="info-chip">
                                <i class="bi bi-clock-history"></i>
                                {{ $item->epp->vida_util_meses ?? 12 }} meses
                            </span>
                            @if($item->epp->codigo_logistica ?? false)
                            <span class="info-chip">
                                <i class="bi bi-upc-scan"></i>
                                {{ $item->epp->codigo_logistica }}
                            </span>
                            @endif
                        </div>

                        {{-- Month badge --}}
                        <div class="mt-2">
                            <span class="month-badge">{{ $fecha->translatedFormat('F') }}</span>
                        </div>

                    </div>
                </div>
                @endforeach
            </div>

        </div>
        @endforeach
    @endif

</div>
@endsection