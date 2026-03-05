@extends('layouts.app')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
    .page-title { font-size: clamp(1.2rem, 4vw, 1.75rem); }

    /* ── FILTER CARD ── */
    .filter-card { border-radius: 15px; }

    /* ── ACTION BUTTONS ── */
    .btn-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .btn-actions .btn { white-space: nowrap; }

    /* ── REPORT TABLE (desktop) ── */
    .table-hover tbody tr:hover { background-color: #f8fafc; }

    /* ── REPORT CARDS (mobile) ── */
    .card-persona {
        border: none;
        border-radius: 14px;
    }
    .epp-item {
        font-size: 0.85rem;
        padding: 4px 0;
        border-bottom: 1px solid #f1f3f5;
    }
    .epp-item:last-child { border-bottom: none; }

    /* ── PDF / PRINT ── */
    .modo-pdf { font-size: 10px !important; background: white; }
    .modo-pdf h2 { font-size: 16px !important; margin-bottom: 5px !important; }
    .modo-pdf p  { font-size: 11px !important; margin-bottom: 10px !important; }
    .modo-pdf .table th,
    .modo-pdf .table td { padding: 4px 6px !important; font-size: 10px !important; }
    .modo-pdf .badge { font-size: 9px !important; padding: 3px 6px !important; }
    .modo-pdf .no-export { display: none !important; }

    @media print {
        body * { visibility: hidden; }
        #reporte-completo, #reporte-completo * { visibility: visible; }
        #reporte-completo {
            position: absolute; left: 0; top: 0;
            width: 100%; background: white;
        }
        .no-print, .no-export { display: none !important; }
        body { font-size: 10pt; }
        h2   { font-size: 14pt !important; }
        .table th, .table td { padding: 4px !important; font-size: 9pt !important; }
    }
</style>

<div class="container py-3 py-md-4" id="reporte-completo">

    {{-- ── HEADER ── --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4 no-print" data-html2canvas-ignore="true">
        <div>
            <h2 class="page-title fw-bold mb-0">Reporte de Asignaciones por Área</h2>
            <p class="text-muted small mb-0">Consulte los equipos entregados al personal de cada departamento.</p>
        </div>
        <a href="{{ route('reportes.index') }}"
           class="btn btn-outline-secondary rounded-pill flex-shrink-0">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    {{-- ── FILTER CARD ── --}}
    <div class="card border-0 shadow-sm mb-4 filter-card no-export no-print"
         data-html2canvas-ignore="true">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('reportes.departamento') }}" method="GET">

                <div class="row g-3 align-items-end">

                    {{-- Departamento --}}
                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="departamento_id" class="form-label fw-bold small">Departamento</label>
                        <select name="departamento_id" id="departamento_id"
                                class="form-select bg-light border-0"
                                onchange="this.form.submit()">
                            <option value="">— Seleccionar —</option>
                            @foreach($departamentos as $depto)
                                <option value="{{ $depto->id }}"
                                    {{ (isset($departamentoSeleccionado) && $departamentoSeleccionado->id == $depto->id) ? 'selected' : '' }}>
                                    {{ $depto->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Fecha inicio --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <label for="fecha_inicio" class="form-label fw-bold small">Desde</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio"
                               class="form-control bg-light border-0"
                               value="{{ request('fecha_inicio') }}"
                               onchange="this.form.submit()">
                    </div>

                    {{-- Fecha fin --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <label for="fecha_fin" class="form-label fw-bold small">Hasta</label>
                        <input type="date" name="fecha_fin" id="fecha_fin"
                               class="form-control bg-light border-0"
                               value="{{ request('fecha_fin') }}"
                               onchange="this.form.submit()">
                    </div>

                    {{-- Action buttons --}}
                    @if(isset($departamentoSeleccionado))
                    <div class="col-12 col-lg-4">
                        <div class="btn-actions justify-content-start justify-content-lg-end">
                            <button id="btnDescargarPdf" type="button"
                                    class="btn btn-danger rounded-pill px-4 shadow-sm">
                                <i class="bi bi-file-earmark-pdf me-2"></i>Descargar PDF
                            </button>
                            <button type="button" onclick="window.print()"
                                    class="btn btn-dark rounded-pill px-4 shadow-sm">
                                <i class="bi bi-printer me-2"></i>Imprimir
                            </button>
                        </div>
                    </div>
                    @endif

                </div>
            </form>
        </div>
    </div>

    {{-- ── REPORT CONTENT ── --}}
    @if(isset($departamentoSeleccionado))

    {{-- Department header (always visible, shown in PDF/print too) --}}
    <div class="card border-0 shadow-sm" style="border-radius:15px; overflow:hidden;">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
            <h5 class="mb-0 fw-bold text-primary">
                <i class="bi bi-building me-2"></i>{{ $departamentoSeleccionado->nombre }}
            </h5>
        </div>

        @if($personal->isEmpty())
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox text-muted fs-1"></i>
                <p class="text-muted mt-2 mb-0">No hay personal registrado en este departamento.</p>
            </div>
        @else

            {{-- ── TABLE (md and up) ── --}}
            <div class="d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="min-width:200px;">Docente / Personal</th>
                                <th style="min-width:110px;">DNI</th>
                                <th>Equipos Asignados (EPP)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($personal as $persona)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $persona->nombre_completo }}</div>
                                    <small class="text-muted">{{ $persona->carrera }}</small>
                                </td>
                                <td>{{ $persona->dni }}</td>
                                <td>
                                    @if($persona->asignaciones->where('estado', 'Entregado')->isEmpty())
                                        <span class="badge bg-light text-muted border">Sin asignaciones activas</span>
                                    @else
                                        <ul class="list-unstyled mb-0">
                                            @foreach($persona->asignaciones->where('estado', 'Entregado') as $asignacion)
                                                <li class="mb-1">
                                                    <i class="bi bi-check2-circle text-success me-1"></i>
                                                    {{ $asignacion->epp->nombre }}
                                                    <span class="fw-bold">x{{ $asignacion->cantidad }}</span>
                                                    <small class="text-muted ms-1">
                                                        ({{ $asignacion->fecha_entrega ? \Carbon\Carbon::parse($asignacion->fecha_entrega)->format('d/m/Y') : 'S/F' }})
                                                    </small>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── CARDS (mobile, < md) ── --}}
            <div class="d-md-none p-3">
                @foreach($personal as $persona)
                @php $asignacionesActivas = $persona->asignaciones->where('estado', 'Entregado'); @endphp
                <div class="card card-persona shadow-sm mb-3">
                    <div class="card-body p-3">

                        {{-- Person info --}}
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-bold text-dark">{{ $persona->nombre_completo }}</div>
                                <small class="text-muted">{{ $persona->carrera }}</small>
                            </div>
                            <span class="badge bg-light text-dark border ms-2 flex-shrink-0">
                                DNI: {{ $persona->dni }}
                            </span>
                        </div>

                        {{-- EPP list --}}
                        @if($asignacionesActivas->isEmpty())
                            <div class="text-muted small fst-italic mt-1">Sin asignaciones activas</div>
                        @else
                            <div class="border-top pt-2 mt-1">
                                <small class="text-uppercase text-muted fw-bold"
                                       style="font-size:.65rem; letter-spacing:.05em;">
                                    EPPs Entregados
                                </small>
                                <div class="mt-1">
                                    @foreach($asignacionesActivas as $asignacion)
                                    <div class="epp-item d-flex align-items-center gap-2">
                                        <i class="bi bi-check2-circle text-success flex-shrink-0"></i>
                                        <span class="flex-grow-1">{{ $asignacion->epp->nombre }}</span>
                                        <span class="fw-bold text-dark">x{{ $asignacion->cantidad }}</span>
                                        <small class="text-muted flex-shrink-0">
                                            {{ $asignacion->fecha_entrega
                                                ? \Carbon\Carbon::parse($asignacion->fecha_entrega)->format('d/m/Y')
                                                : 'S/F' }}
                                        </small>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
                @endforeach
            </div>

        @endif
    </div>

    @endif
</div>

<script>
    const btnPdf = document.getElementById('btnDescargarPdf');
    if (btnPdf) {
        btnPdf.addEventListener('click', function () {
            const element = document.getElementById('reporte-completo');
            element.classList.add('modo-pdf');
            const opt = {
                margin:      0.5,
                filename:    'Reporte_Asignaciones_{{ date("d-m-Y") }}.pdf',
                image:       { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF:       { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save().then(() => {
                element.classList.remove('modo-pdf');
            });
        });
    }
</script>
@endsection