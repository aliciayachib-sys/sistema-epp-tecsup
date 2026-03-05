@extends('layouts.app')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
    .page-title { font-size: clamp(1.2rem, 4vw, 1.75rem); }

    /* ── CARDS (mobile) ── */
    .card-incidencia {
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
        font-size: 0.75rem;
        color: #495057;
    }

    /* ── PDF / PRINT ── */
    .modo-pdf { font-size: 10px !important; background: white; }
    .modo-pdf h2 { font-size: 16px !important; margin-bottom: 5px !important; }
    .modo-pdf p  { font-size: 11px !important; margin-bottom: 10px !important; }
    .modo-pdf .table th,
    .modo-pdf .table td { padding: 4px 6px !important; font-size: 10px !important; }
    .modo-pdf .badge { font-size: 9px !important; padding: 3px 6px !important; }

    @media print {
        body * { visibility: hidden; }
        #reporte-completo, #reporte-completo * { visibility: visible; }
        #reporte-completo {
            position: absolute; left: 0; top: 0;
            width: 100%; background: white;
        }
        .no-print { display: none !important; }
        body { font-size: 10pt; }
        h2   { font-size: 14pt !important; }
        .table th, .table td { padding: 4px !important; font-size: 9pt !important; }
        .badge { font-size: 8pt !important; border: 1px solid #000; }
    }
</style>

<div class="container py-3 py-md-4" id="reporte-completo">

    {{-- ── HEADER ── --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4 no-print"
         data-html2canvas-ignore="true">
        <div>
            <h2 class="page-title fw-bold mb-0">Reporte de Incidencias y Bajas</h2>
            <p class="text-muted small mb-0">Listado de EPPs reportados como dañados, perdidos o dados de baja.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 flex-shrink-0">
            <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
            <button id="btnDescargarPdf" class="btn btn-danger rounded-pill shadow-sm">
                <i class="bi bi-file-earmark-pdf me-2"></i>PDF
            </button>
            <button onclick="window.print()" class="btn btn-dark rounded-pill shadow-sm">
                <i class="bi bi-printer me-2"></i>Imprimir
            </button>
        </div>
    </div>

    @if($incidencias->isEmpty())
        <div class="card border-0 shadow-sm text-center py-5" style="border-radius:15px;">
            <i class="bi bi-check-circle-fill text-success fs-1"></i>
            <p class="text-muted mt-2 mb-0">No se han reportado incidencias hasta la fecha.</p>
        </div>
    @else

        {{-- ── TABLE (md and up) ── --}}
        <div class="card border-0 shadow-sm d-none d-md-block" style="border-radius:15px; overflow:hidden;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="min-width:180px;">EPP</th>
                            <th style="min-width:180px;">Personal Asignado</th>
                            <th style="min-width:150px;">Departamento</th>
                            <th class="text-center" style="min-width:80px;">Cantidad</th>
                            <th class="text-center" style="min-width:130px;">Estado Incidencia</th>
                            <th style="min-width:140px;">Fecha Reporte</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($incidencias as $incidencia)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $incidencia->epp->nombre ?? 'N/A' }}</td>
                            <td>{{ $incidencia->personal->nombre_completo ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-light text-secondary border">
                                    {{ $incidencia->personal->departamento->nombre ?? 'Sin Depto.' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $incidencia->cantidad }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $incidencia->estado == 'Dañado' ? 'warning text-dark' : 'danger' }}">
                                    {{ $incidencia->estado }}
                                </span>
                            </td>
                            <td>{{ $incidencia->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── CARDS (mobile, < md) ── --}}
        <div class="d-md-none">
            @foreach($incidencias as $incidencia)
            <div class="card card-incidencia shadow-sm mb-3">
                <div class="card-body p-3">

                    {{-- Top: EPP name + estado badge --}}
                    <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                        <div class="fw-bold text-dark flex-grow-1">
                            <i class="bi bi-shield-exclamation text-secondary me-1"></i>
                            {{ $incidencia->epp->nombre ?? 'N/A' }}
                        </div>
                        <span class="badge bg-{{ $incidencia->estado == 'Dañado' ? 'warning text-dark' : 'danger' }} flex-shrink-0">
                            {{ $incidencia->estado }}
                        </span>
                    </div>

                    {{-- Personal --}}
                    <div class="small mb-2">
                        <i class="bi bi-person me-1 text-muted"></i>
                        <span>{{ $incidencia->personal->nombre_completo ?? 'N/A' }}</span>
                    </div>

                    {{-- Chips: depto + cantidad + fecha --}}
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <span class="info-chip">
                            <i class="bi bi-building"></i>
                            {{ $incidencia->personal->departamento->nombre ?? 'Sin Depto.' }}
                        </span>
                        <span class="info-chip">
                            <i class="bi bi-hash"></i>
                            Cant: {{ $incidencia->cantidad }}
                        </span>
                        <span class="info-chip">
                            <i class="bi bi-clock"></i>
                            {{ $incidencia->updated_at->format('d/m/Y H:i') }}
                        </span>
                    </div>

                </div>
            </div>
            @endforeach
        </div>

    @endif
</div>

<script>
    document.getElementById('btnDescargarPdf')?.addEventListener('click', function () {
        const element = document.getElementById('reporte-completo');
        element.classList.add('modo-pdf');
        const opt = {
            margin:      0.5,
            filename:    'Reporte_Incidencias_{{ date("d-m-Y") }}.pdf',
            image:       { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF:       { unit: 'in', format: 'letter', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save().then(() => {
            element.classList.remove('modo-pdf');
        });
    });
</script>
@endsection