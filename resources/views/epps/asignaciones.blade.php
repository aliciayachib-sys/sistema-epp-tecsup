@extends('layouts.app')

@section('content')
<style>
    .page-title { font-size: clamp(1.25rem, 4vw, 1.75rem); }

    /* ── FILTER BAR ── */
    .filter-bar .input-group,
    .filter-bar .form-select { min-width: 0; }

    /* ── TABLE (desktop) ── */
    .table-hover tbody tr:hover { background-color: #f8fafc; }
    .badge { font-weight: 600; }

    /* FIX: Bootstrap fuerza color:white en .badge — lo anulamos */
    .badge.bg-light       { color: #333 !important; }
    .badge-depto          { background-color: #e0f2fe !important; color: #0369a1 !important; font-weight: 600; }
    .badge-carrera        { background-color: #f1f5f9 !important; color: #334155 !important; font-weight: 600; border: 1px solid #e2e8f0; }

    /* ── CARDS (mobile) ── */
    .card-asignacion {
        border: none;
        border-radius: 14px;
        transition: box-shadow 0.2s;
    }
    .card-asignacion:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.08) !important; }

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

    /* ── FILTER COLLAPSE TOGGLE (mobile) ── */
    .btn-filter-toggle { font-size: 0.85rem; }
</style>

<div class="container py-3 py-md-4">

    {{-- ── HEADER ── --}}
    <div class="mb-4">
        <h2 class="page-title fw-bold text-dark mb-0">Historial de Entregas de EPP</h2>
        <p class="text-muted small mb-0">Consulta por Departamento › Carrera › Taller/Lab</p>
    </div>

    {{-- ── FILTER BAR ── --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
        <div class="card-body p-3">

            {{-- Mobile: toggle filters --}}
            <div class="d-md-none mb-2 d-flex gap-2">
                <div class="input-group flex-grow-1">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                    <input id="searchInput" type="text"
                           class="form-control border-0 bg-light"
                           placeholder="Buscar docente, DNI, EPP...">
                </div>
                <button class="btn btn-light border btn-filter-toggle"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#filtrosExtras"
                        aria-expanded="false">
                    <i class="bi bi-sliders me-1"></i>Filtros
                </button>
            </div>

            {{-- Desktop: all filters visible --}}
            <div class="d-none d-md-block">
                <div class="row g-2 align-items-center filter-bar">
                    <div class="col-lg-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input id="searchInputDesktop" type="text"
                                   class="form-control border-0 bg-light"
                                   placeholder="Buscar por docente, DNI, EPP...">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <div class="input-group input-group-sm border rounded">
                            <span class="input-group-text bg-white border-0 text-muted small">Desde:</span>
                            <input type="date" id="dateFromDesktop" class="form-control border-0 ps-0">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <div class="input-group input-group-sm border rounded">
                            <span class="input-group-text bg-white border-0 text-muted small">Hasta:</span>
                            <input type="date" id="dateToDesktop" class="form-control border-0 ps-0">
                        </div>
                    </div>
                    <div class="col-lg-4 d-flex gap-1">
                        @php
                            $deps  = collect($asignaciones)->map(fn($a) => optional(optional($a->personal)->departamento)->nombre)->filter()->unique()->sort()->values();
                            $cars  = collect($asignaciones)->map(fn($a) => optional($a->personal)->carrera)->filter()->unique()->sort()->values();
                            $talls = collect();
                            foreach ($asignaciones as $a) {
                                if ($a->personal && method_exists($a->personal, 'talleres') && $a->personal->talleres)
                                    $talls = $talls->merge($a->personal->talleres->pluck('nombre'));
                            }
                            $talls = $talls->filter()->unique()->sort()->values();
                        @endphp
                        <select id="depFilterDesktop" class="form-select form-select-sm">
                            <option value="">Departamentos</option>
                            @foreach($deps as $d) <option value="{{ $d }}">{{ $d }}</option> @endforeach
                        </select>
                        <select id="carFilterDesktop" class="form-select form-select-sm">
                            <option value="">Carreras</option>
                            @foreach($cars as $c) <option value="{{ $c }}">{{ $c }}</option> @endforeach
                        </select>
                        <select id="tallerFilterDesktop" class="form-select form-select-sm">
                            <option value="">Talleres</option>
                            @foreach($talls as $t) <option value="{{ $t }}">{{ $t }}</option> @endforeach
                        </select>
                        <button type="button" id="btnResetDesktop"
                                class="btn btn-sm btn-outline-secondary flex-shrink-0" title="Limpiar Filtros">
                            <i class="bi bi-eraser"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mobile: collapsible extra filters --}}
            <div class="collapse d-md-none" id="filtrosExtras">
                <div class="pt-2 d-flex flex-column gap-2">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="input-group input-group-sm border rounded">
                                <span class="input-group-text bg-white border-0 text-muted" style="font-size:.75rem;">Desde:</span>
                                <input type="date" id="dateFrom" class="form-control border-0 ps-0">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group input-group-sm border rounded">
                                <span class="input-group-text bg-white border-0 text-muted" style="font-size:.75rem;">Hasta:</span>
                                <input type="date" id="dateTo" class="form-control border-0 ps-0">
                            </div>
                        </div>
                    </div>
                    <select id="depFilter" class="form-select form-select-sm">
                        <option value="">Todos los departamentos</option>
                        @foreach($deps as $d) <option value="{{ $d }}">{{ $d }}</option> @endforeach
                    </select>
                    <select id="carFilter" class="form-select form-select-sm">
                        <option value="">Todas las carreras</option>
                        @foreach($cars as $c) <option value="{{ $c }}">{{ $c }}</option> @endforeach
                    </select>
                    <select id="tallerFilter" class="form-select form-select-sm">
                        <option value="">Todos los talleres</option>
                        @foreach($talls as $t) <option value="{{ $t }}">{{ $t }}</option> @endforeach
                    </select>
                    <button type="button" id="btnReset"
                            class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-eraser me-1"></i>Limpiar filtros
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════
         TABLE (md and up)
    ══════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm d-none d-md-block" style="border-radius: 12px;">
        <div class="card-body p-3 p-lg-4">
            <div class="table-responsive">
                <table id="asignacionesTable" class="table align-middle table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width:110px;">Fecha</th>
                            <th style="min-width:150px;">Departamento</th>
                            <th style="min-width:160px;">Carrera</th>
                            <th style="min-width:180px;">Taller/Lab</th>
                            <th style="min-width:200px;">Personal</th>
                            <th style="min-width:220px;">Equipo (EPP)</th>
                            <th class="text-center">Cant.</th>
                            <th style="min-width:110px;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asignaciones as $asignacion)
                        @php
                            $personal  = $asignacion->personal;
                            $dep       = optional(optional($personal)->departamento)->nombre;
                            $car       = optional($personal)->carrera;
                            $talleres  = $personal && method_exists($personal, 'talleres') && $personal->talleres
                                            ? $personal->talleres->pluck('nombre')->join(', ') : '';
                            $buscarText = strtolower(trim(
                                ($personal->nombre_completo ?? '') . ' ' .
                                ($personal->dni ?? '') . ' ' .
                                ($asignacion->epp->nombre ?? '') . ' ' .
                                ($dep ?? '') . ' ' . ($car ?? '') . ' ' . $talleres
                            ));
                            $estado = (string)$asignacion->estado;
                            $cls = in_array($estado, ['Posee','Entregado']) ? 'bg-success'
                                 : (in_array($estado, ['Dañado','Perdido']) ? 'bg-danger' : 'bg-secondary');
                        @endphp
                        <tr data-search="{{ $buscarText }}"
                            data-dep="{{ strtolower($dep ?? '') }}"
                            data-car="{{ strtolower($car ?? '') }}"
                            data-taller="{{ strtolower($talleres ?? '') }}"
                            data-fecha="{{ \Carbon\Carbon::parse($asignacion->fecha_entrega)->format('Y-m-d') }}">
                            <td>{{ \Carbon\Carbon::parse($asignacion->fecha_entrega)->format('d/m/Y') }}</td>
                            <td>
                                {{-- FIX: usamos clase propia en lugar de badge+bg-info-soft --}}
                                <span class="badge badge-depto">{{ $dep ?? '—' }}</span>
                            </td>
                            <td>
                                {{-- FIX: clase propia para carrera --}}
                                <span class="badge badge-carrera">{{ $car ?? '—' }}</span>
                            </td>
                            <td><small class="text-muted">{{ $talleres ?: '—' }}</small></td>
                            <td>
                                <div class="fw-semibold">{{ $personal->nombre_completo ?? 'No asignado' }}</div>
                                <small class="text-muted">{{ $personal->dni ?? '' }}</small>
                            </td>
                            <td>{{ $asignacion->epp->nombre }}</td>
                            <td class="text-center"><span class="badge bg-secondary">{{ $asignacion->cantidad }}</span></td>
                            <td><span class="badge rounded-pill {{ $cls }}">{{ $estado }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-5 text-muted">No hay registros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         CARDS (mobile, < md)
    ══════════════════════════════════════ --}}
    <div class="d-md-none" id="listaMobileCards">
        @forelse($asignaciones as $asignacion)
        @php
            $personal  = $asignacion->personal;
            $dep       = optional(optional($personal)->departamento)->nombre;
            $car       = optional($personal)->carrera;
            $talleres  = $personal && method_exists($personal, 'talleres') && $personal->talleres
                            ? $personal->talleres->pluck('nombre')->join(', ') : '';
            $buscarText = strtolower(trim(
                ($personal->nombre_completo ?? '') . ' ' .
                ($personal->dni ?? '') . ' ' .
                ($asignacion->epp->nombre ?? '') . ' ' .
                ($dep ?? '') . ' ' . ($car ?? '') . ' ' . $talleres
            ));
            $estado = (string)$asignacion->estado;
            $cls = in_array($estado, ['Posee','Entregado']) ? 'bg-success'
                 : (in_array($estado, ['Dañado','Perdido']) ? 'bg-danger' : 'bg-secondary');
            $fechaYmd = \Carbon\Carbon::parse($asignacion->fecha_entrega)->format('Y-m-d');
        @endphp
        <div class="card card-asignacion shadow-sm mb-3 card-mobile-item"
             data-search="{{ $buscarText }}"
             data-dep="{{ strtolower($dep ?? '') }}"
             data-car="{{ strtolower($car ?? '') }}"
             data-taller="{{ strtolower($talleres ?? '') }}"
             data-fecha="{{ $fechaYmd }}">
            <div class="card-body p-3">

                {{-- Top row: date + estado --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ \Carbon\Carbon::parse($asignacion->fecha_entrega)->format('d/m/Y') }}
                    </span>
                    <span class="badge rounded-pill {{ $cls }}">{{ $estado }}</span>
                </div>

                {{-- Person --}}
                <div class="fw-bold mb-1">{{ $personal->nombre_completo ?? 'No asignado' }}</div>
                @if($personal->dni ?? false)
                    <div class="text-muted small mb-2">DNI: {{ $personal->dni }}</div>
                @endif

                {{-- EPP --}}
                <div class="d-flex align-items-center gap-2 mb-3 p-2 bg-light rounded-3">
                    <i class="bi bi-shield-check text-primary"></i>
                    <div class="flex-grow-1 small fw-semibold">{{ $asignacion->epp->nombre }}</div>
                    <span class="badge bg-secondary">x{{ $asignacion->cantidad }}</span>
                </div>

                {{-- Chips: dept, carrera, taller --}}
                <div class="d-flex flex-wrap gap-1">
                    @if($dep)
                        <span class="info-chip"><i class="bi bi-building"></i>{{ $dep }}</span>
                    @endif
                    @if($car)
                        <span class="info-chip"><i class="bi bi-mortarboard"></i>{{ $car }}</span>
                    @endif
                    @if($talleres)
                        <span class="info-chip"><i class="bi bi-tools"></i>{{ $talleres }}</span>
                    @endif
                </div>

            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
            No hay registros.
        </div>
        @endforelse
    </div>

</div>

<script>
(function () {

    // ── Helpers ──────────────────────────────────────────────────────
    function getVal(id) {
        const el = document.getElementById(id);
        return el ? el.value.trim().toLowerCase() : '';
    }

    function filterRows(rows, q, dep, car, tal, from, to) {
        rows.forEach(row => {
            if (row.cells && row.cells.length === 1) return; // colspan empty row

            const matchText  = !q   || (row.getAttribute('data-search') ?? '').includes(q);
            const matchDep   = !dep || (row.getAttribute('data-dep')    ?? '') === dep;
            const matchCar   = !car || (row.getAttribute('data-car')    ?? '') === car;
            const matchTal   = !tal || (row.getAttribute('data-taller') ?? '').split(',').map(s => s.trim()).includes(tal);
            const rfecha     = row.getAttribute('data-fecha') ?? '';
            const matchFecha = (!from || rfecha >= from) && (!to || rfecha <= to);

            row.style.display = (matchText && matchDep && matchCar && matchTal && matchFecha) ? '' : 'none';
        });
    }

    // ── Desktop ───────────────────────────────────────────────────────
    const desktopInputs = ['searchInputDesktop','depFilterDesktop','carFilterDesktop',
                           'tallerFilterDesktop','dateFromDesktop','dateToDesktop'];
    const tableRows = Array.from(document.querySelectorAll('#asignacionesTable tbody tr'));

    function applyDesktop() {
        filterRows(
            tableRows,
            getVal('searchInputDesktop'),
            getVal('depFilterDesktop'),
            getVal('carFilterDesktop'),
            getVal('tallerFilterDesktop'),
            document.getElementById('dateFromDesktop')?.value ?? '',
            document.getElementById('dateToDesktop')?.value ?? ''
        );
    }

    desktopInputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.addEventListener('input', applyDesktop); el.addEventListener('change', applyDesktop); }
    });

    document.getElementById('btnResetDesktop')?.addEventListener('click', () => {
        desktopInputs.forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
        applyDesktop();
    });

    // ── Mobile ────────────────────────────────────────────────────────
    const mobileInputs = ['searchInput','depFilter','carFilter','tallerFilter','dateFrom','dateTo'];
    const mobileCards  = Array.from(document.querySelectorAll('.card-mobile-item'));

    function applyMobile() {
        filterRows(
            mobileCards,
            getVal('searchInput'),
            getVal('depFilter'),
            getVal('carFilter'),
            getVal('tallerFilter'),
            document.getElementById('dateFrom')?.value ?? '',
            document.getElementById('dateTo')?.value ?? ''
        );
    }

    mobileInputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.addEventListener('input', applyMobile); el.addEventListener('change', applyMobile); }
    });

    document.getElementById('btnReset')?.addEventListener('click', () => {
        mobileInputs.forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
        applyMobile();
        const col = document.getElementById('filtrosExtras');
        if (col) bootstrap.Collapse.getInstance(col)?.hide();
    });

})();
</script>
@endsection