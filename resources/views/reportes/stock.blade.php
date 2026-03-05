@extends('layouts.app')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
    .page-title { font-size: clamp(1.2rem, 4vw, 1.75rem); }

    /* ── CARDS (mobile) ── */
    .card-epp {
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
            <h2 class="page-title fw-bold mb-0">Reporte de Stock Actual</h2>
            <p class="text-muted small mb-0">Inventario general de EPPs al {{ date('d/m/Y') }}</p>
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

    {{-- ── TABLE (md and up) ── --}}
    <div class="card border-0 shadow-sm d-none d-md-block" style="border-radius:15px; overflow:hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="min-width:200px;">Nombre del EPP</th>
                        <th style="min-width:140px;">Categoría</th>
                        <th class="text-center" style="min-width:110px;">Stock Físico</th>
                        <th class="text-center" style="min-width:110px;">Estado</th>
                        <th class="text-center" style="min-width:110px;">Deteriorados</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($epps as $epp)
                    <tr>
                        <td class="ps-4 fw-bold text-dark">{{ $epp->nombre }}</td>
                        <td>
                            <span class="badge bg-light text-secondary border">
                                {{ $epp->categoria->nombre ?? 'General' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $epp->stock < 10 ? 'bg-danger' : 'bg-success' }} fs-6">
                                {{ $epp->stock }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($epp->stock == 0)
                                <span class="text-danger fw-bold small">AGOTADO</span>
                            @elseif($epp->stock < 10)
                                <span class="text-warning fw-bold small">CRÍTICO</span>
                            @else
                                <span class="text-success fw-bold small">DISPONIBLE</span>
                            @endif
                        </td>
                        <td class="text-center text-muted">{{ $epp->deteriorado ?? 0 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── CARDS (mobile, < md) ── --}}
    <div class="d-md-none">
        @forelse($epps as $epp)
        <div class="card card-epp shadow-sm mb-3">
            <div class="card-body p-3">

                {{-- Top: name + estado --}}
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <span class="fw-bold text-dark flex-grow-1">{{ $epp->nombre }}</span>
                    @if($epp->stock == 0)
                        <span class="badge bg-danger flex-shrink-0">AGOTADO</span>
                    @elseif($epp->stock < 10)
                        <span class="badge bg-warning text-dark flex-shrink-0">CRÍTICO</span>
                    @else
                        <span class="badge bg-success flex-shrink-0">DISPONIBLE</span>
                    @endif
                </div>

                {{-- Chips: categoría, stock, deteriorados --}}
                <div class="d-flex flex-wrap gap-1 mt-2">
                    <span class="info-chip">
                        <i class="bi bi-tag"></i>{{ $epp->categoria->nombre ?? 'General' }}
                    </span>
                    <span class="info-chip {{ $epp->stock < 10 ? 'text-danger' : '' }}">
                        <i class="bi bi-archive"></i>Stock: <strong>{{ $epp->stock }}</strong>
                    </span>
                    @if(($epp->deteriorado ?? 0) > 0)
                    <span class="info-chip text-warning">
                        <i class="bi bi-exclamation-triangle"></i>Deteriorados: {{ $epp->deteriorado }}
                    </span>
                    @endif
                </div>

            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
            No hay EPPs registrados.
        </div>
        @endforelse
    </div>

</div>

<script>
    document.getElementById('btnDescargarPdf')?.addEventListener('click', function () {
        const element = document.getElementById('reporte-completo');
        element.classList.add('modo-pdf');
        const opt = {
            margin:      0.5,
            filename:    'Reporte_Stock_EPP_{{ date("d-m-Y") }}.pdf',
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