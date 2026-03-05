@extends('layouts.app')

@section('content')
<style>
    /* ── TABLE (desktop) ── */
    .table-desktop {
        min-width: 1100px;
    }

    /* ── CARDS (mobile) ── */
    .card-personal {
        border: none;
        border-radius: 16px;
        transition: box-shadow 0.2s;
    }
    .card-personal:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.08) !important; }

    .epp-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #f1f3f5;
        border-radius: 8px;
        padding: 3px 8px;
        font-size: 0.78rem;
        margin: 2px;
    }

    /* ── FILTER BAR ── */
    .filter-bar .form-select,
    .filter-bar .input-group {
        min-width: 0;
    }

    /* ── PAGE HEADER ── */
    .page-title { font-size: clamp(1.25rem, 4vw, 1.75rem); }

    /* ── MODAL ── */
    .modal-content { border-radius: 20px !important; border: none !important; }
</style>

<div class="container-fluid py-3 py-md-4">

    {{-- ── HEADER ── --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="page-title fw-bold mb-0">Entrega de EPP</h2>
            <p class="text-muted mb-0 small">Lista de personal y asignación de equipos</p>
        </div>
        <button class="btn btn-dark rounded-pill px-4 shadow-sm flex-shrink-0"
                data-bs-toggle="modal" data-bs-target="#modalMasivo">
            <i class="bi bi-boxes me-2"></i>Asignar a Todos
        </button>
    </div>

    {{-- ── FILTER BAR ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3 px-3 px-md-4">
            <form action="{{ route('entregas.index') }}" method="GET"
                  class="filter-bar d-flex align-items-center gap-2 flex-wrap" id="formFiltro">

                <i class="bi bi-funnel fs-5 text-primary d-none d-sm-block"></i>

                <select name="departamento_id" class="form-select form-select-sm shadow-sm"
                        style="max-width: 220px;"
                        onchange="document.getElementById('formFiltro').submit()">
                    <option value="">Todos los departamentos</option>
                    @foreach($departamentos as $depto)
                        <option value="{{ $depto->id }}"
                            {{ (isset($departamentoIdFiltro) && $departamentoIdFiltro == $depto->id) ? 'selected' : '' }}>
                            {{ $depto->nombre }}
                        </option>
                    @endforeach
                </select>

                <div class="input-group input-group-sm shadow-sm flex-grow-1" style="max-width: 280px;">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0"
                           id="buscadorDocente"
                           placeholder="Buscar docente, DNI..."
                           onkeyup="filtrarTabla()">
                </div>

                <a href="{{ route('entregas.index') }}" class="btn btn-sm btn-light border">
                    <i class="bi bi-x-circle me-1"></i>Limpiar
                </a>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         TABLE VIEW (md and up)
    ══════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm d-none d-md-block" style="border-radius: 20px; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="table table-hover align-middle mb-0 table-desktop" id="tablaPersonal">
                <thead class="table-light">
                    <tr>
                        <th style="width:18%;">Docente</th>
                        <th style="width:14%;">Carrera</th>
                        <th style="width:9%;">Tipo</th>
                        <th style="width:9%;">DNI</th>
                        <th style="width:24%;">EPPs Asignados</th>
                        <th style="width:18%;">Estado / Acciones</th>
                        <th style="width:8%; text-align:center;">Entregar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($personals as $persona)
                    @php $tallerNombre = $persona->talleres->first()->nombre ?? ''; @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 fw-semibold">{{ $persona->nombre_completo }}</p>
                                    <small class="text-muted">{{ $tallerNombre }}</small>
                                </div>
                                <button class="btn btn-link btn-sm text-muted ms-2 p-0"
                                        onclick="editarPersonal({{ $persona->id }}, '{{ addslashes($persona->nombre_completo) }}', '{{ $persona->dni }}', '{{ addslashes($persona->carrera) }}', '{{ $persona->tipo_contrato }}', '{{ addslashes($tallerNombre) }}')"
                                        title="Editar datos">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border" style="font-size:.82rem;">{{ $persona->carrera }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border" style="font-size:.82rem;">{{ $persona->tipo_contrato ?? '---' }}</span>
                        </td>
                        <td><small>{{ $persona->dni }}</small></td>

                        <td>
                            @forelse($persona->asignaciones as $asignacion)
                                <div class="py-1 border-bottom d-flex align-items-center" style="font-size:.88rem; min-height:38px;">
                                    <span class="fw-semibold text-dark">{{ $asignacion->epp->nombre }}</span>
                                    <span class="text-muted ms-1">x{{ $asignacion->cantidad }}</span>
                                </div>
                            @empty
                                <span class="text-muted small fst-italic">Sin asignaciones</span>
                            @endforelse
                        </td>

                        <td>
                            @forelse($persona->asignaciones as $asignacion)
                                <div class="py-1 border-bottom d-flex align-items-center gap-1" style="min-height:38px;">
                                    @if($asignacion->estado == 'Entregado')
                                        <button type="button" class="btn btn-success btn-sm py-0 px-2"
                                                style="font-size:.78rem; white-space:nowrap;"
                                                onclick="confirmarDevolucion('{{ route('asignaciones.devolver', $asignacion->id) }}')">
                                            <i class="bi bi-check-lg"></i> Devolver
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-sm py-0 px-2"
                                                style="font-size:.78rem;"
                                                title="Dañado"
                                                onclick="confirmarIncidencia({{ $asignacion->id }}, 'Dañado')">
                                            <i class="bi bi-tools"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2"
                                                style="font-size:.78rem;"
                                                title="Perdido"
                                                onclick="confirmarIncidencia({{ $asignacion->id }}, 'Perdido')">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    @elseif($asignacion->estado == 'Devuelto')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success" style="font-size:.75rem;">
                                            <i class="bi bi-check-circle me-1"></i>Devuelto
                                        </span>
                                    @elseif($asignacion->estado == 'Dañado')
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning" style="font-size:.75rem;">
                                            <i class="bi bi-tools me-1"></i>Dañado
                                        </span>
                                    @elseif($asignacion->estado == 'Perdido')
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger" style="font-size:.75rem;">
                                            <i class="bi bi-x-circle me-1"></i>Perdido
                                        </span>
                                    @endif
                                </div>
                            @empty
                                <span class="text-muted small fst-italic">—</span>
                            @endforelse
                        </td>

                        <td class="text-center">
                            <button class="btn btn-primary btn-sm rounded-pill px-3"
                                    onclick="abrirModalEntrega(
                                        {{ $persona->id }},
                                        '{{ addslashes($persona->nombre_completo) }}',
                                        '{{ addslashes($tallerNombre) }}',
                                        '{{ addslashes($persona->tipo_contrato ?? '') }}'
                                    )">
                                <i class="bi bi-hand-index-thumb me-1"></i>Entregar
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         CARD VIEW (mobile, < md)
    ══════════════════════════════════════ --}}
    <div class="d-md-none" id="listaCardsMobile">
        @forelse($personals as $persona)
        @php $tallerNombre = $persona->talleres->first()->nombre ?? ''; @endphp
        <div class="card card-personal shadow-sm mb-3 card-mobile-item"
             data-nombre="{{ strtolower($persona->nombre_completo) }}"
             data-dni="{{ strtolower($persona->dni) }}">
            <div class="card-body p-3">

                {{-- Header row --}}
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="flex-grow-1 me-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-bold">{{ $persona->nombre_completo }}</span>
                            <button class="btn btn-link btn-sm text-muted p-0"
                                    onclick="editarPersonal({{ $persona->id }}, '{{ addslashes($persona->nombre_completo) }}', '{{ $persona->dni }}', '{{ addslashes($persona->carrera) }}', '{{ $persona->tipo_contrato }}', '{{ addslashes($tallerNombre) }}')"
                                    title="Editar">
                                <i class="bi bi-pencil-square small"></i>
                            </button>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @if($tallerNombre)
                                <span class="epp-chip text-muted"><i class="bi bi-tools"></i>{{ $tallerNombre }}</span>
                            @endif
                            <span class="epp-chip text-muted"><i class="bi bi-credit-card"></i>{{ $persona->dni }}</span>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-pill px-3 flex-shrink-0"
                            onclick="abrirModalEntrega(
                                {{ $persona->id }},
                                '{{ addslashes($persona->nombre_completo) }}',
                                '{{ addslashes($tallerNombre) }}',
                                '{{ addslashes($persona->tipo_contrato ?? '') }}'
                            )">
                        <i class="bi bi-hand-index-thumb"></i>
                    </button>
                </div>

                {{-- Badges --}}
                <div class="d-flex flex-wrap gap-1 mb-3">
                    <span class="badge bg-light text-dark border">{{ $persona->carrera }}</span>
                    <span class="badge bg-light text-dark border">{{ $persona->tipo_contrato ?? '---' }}</span>
                </div>

                {{-- EPPs --}}
                @if($persona->asignaciones->count())
                <div class="border-top pt-2">
                    <small class="text-uppercase text-muted fw-bold" style="font-size:.65rem; letter-spacing:.05em;">EPPs Asignados</small>
                    <div class="mt-1">
                        @foreach($persona->asignaciones as $asignacion)
                        <div class="d-flex align-items-center justify-content-between py-1 border-bottom gap-2 flex-wrap">
                            <span class="small fw-semibold">
                                {{ $asignacion->epp->nombre }}
                                <span class="text-muted fw-normal">x{{ $asignacion->cantidad }}</span>
                            </span>
                            <div class="d-flex align-items-center gap-1">
                                @if($asignacion->estado == 'Entregado')
                                    <button type="button" class="btn btn-success btn-sm py-0 px-2"
                                            style="font-size:.75rem;"
                                            onclick="confirmarDevolucion('{{ route('asignaciones.devolver', $asignacion->id) }}')">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-sm py-0 px-2"
                                            style="font-size:.75rem;"
                                            title="Dañado"
                                            onclick="confirmarIncidencia({{ $asignacion->id }}, 'Dañado')">
                                        <i class="bi bi-tools"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2"
                                            style="font-size:.75rem;"
                                            title="Perdido"
                                            onclick="confirmarIncidencia({{ $asignacion->id }}, 'Perdido')">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                @elseif($asignacion->estado == 'Devuelto')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success" style="font-size:.7rem;">
                                        <i class="bi bi-check-circle me-1"></i>Devuelto
                                    </span>
                                @elseif($asignacion->estado == 'Dañado')
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning" style="font-size:.7rem;">
                                        <i class="bi bi-tools me-1"></i>Dañado
                                    </span>
                                @elseif($asignacion->estado == 'Perdido')
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger" style="font-size:.7rem;">
                                        <i class="bi bi-x-circle me-1"></i>Perdido
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                    <div class="border-top pt-2">
                        <span class="text-muted small fst-italic">Sin asignaciones</span>
                    </div>
                @endif

            </div>
        </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-person-x fs-1 d-block mb-2 opacity-25"></i>
                Sin personal registrado.
            </div>
        @endforelse
    </div>

</div>

{{-- ════════════════════════════════════════
     MODAL: Entrega Individual
════════════════════════════════════════ --}}
<div class="modal fade" id="modalEntrega" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="fw-bold">Entregar EPP a: <span id="nombreDocente" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('asignaciones.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="personal_id" id="personal_id">
                    <div class="mb-3">
                        <label for="fecha_entrega_individual" class="form-label fw-bold small">Fecha de Entrega</label>
                        <input type="date" name="fecha_entrega" id="fecha_entrega_individual"
                               class="form-control" value="{{ now()->format('Y-m-d') }}">
                        <small class="text-muted">Si la entrega fue en una fecha pasada, selecciónala aquí.</small>
                    </div>
                    <div class="alert alert-light border mb-3 py-2">
                        <small class="text-muted"><i class="bi bi-check2-square me-1"></i>Marca los equipos que deseas entregar.</small>
                    </div>
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="buscadorIndividual"
                                   class="form-control border-start-0 ps-0" placeholder="Buscar EPP...">
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-hover align-middle table-sm">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width:30px;"></th>
                                    <th>Equipo</th>
                                    <th style="width:60px;" class="text-center">Stock</th>
                                    <th style="width:80px;">Cant.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($epps as $epp)
                                @php $sinStock = $epp->stock <= 0; @endphp
                                <tr class="{{ $sinStock ? 'table-light text-muted' : '' }}">
                                    <td>
                                        <input class="form-check-input" type="checkbox"
                                               name="epps[{{ $epp->id }}][checked]" value="1"
                                               id="check_ind_{{ $epp->id }}"
                                               data-stock="{{ $epp->stock }}"
                                               {{ $sinStock ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        <label class="form-check-label w-100 small"
                                               for="check_ind_{{ $epp->id }}"
                                               style="{{ $sinStock ? '' : 'cursor:pointer;' }}">
                                            {{ $epp->nombre }}
                                            @if($sinStock)
                                                <span class="badge bg-danger ms-1" style="font-size:.7em;">AGOTADO</span>
                                            @endif
                                            <span id="badge_info_{{ $epp->id }}" class="badge ms-1" style="display:none; font-size:.7em;"></span>
                                        </label>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $sinStock ? 'bg-danger' : 'bg-secondary' }}">{{ $epp->stock }}</span>
                                    </td>
                                    <td>
                                        <input type="number" name="epps[{{ $epp->id }}][cantidad]"
                                               class="form-control form-control-sm text-center"
                                               value="1" min="1"
                                               {{ $sinStock ? 'disabled' : '' }}>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold">Confirmar Entrega</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════
     MODAL: Asignación Masiva
════════════════════════════════════════ --}}
<div class="modal fade" id="modalMasivo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 bg-light" style="border-radius:20px 20px 0 0;">
                <h5 class="fw-bold"><i class="bi bi-people-fill me-2"></i>Asignación Masiva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('entregas.asignar_masivo') }}" method="POST">
                @csrf
                <input type="hidden" name="departamento_id" value="{{ $departamentoIdFiltro ?? '' }}">
                <div class="modal-body p-3 p-sm-4">
                    <div class="alert alert-info border-0 d-flex align-items-center" role="alert">
                        <i class="bi bi-info-circle-fill me-2 fs-5 flex-shrink-0"></i>
                        <div class="small">Se asignará el equipo seleccionado a <strong>{{ $personals->count() }}</strong> docentes.</div>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_entrega_masiva" class="form-label fw-bold small">Fecha de Entrega</label>
                        <input type="date" name="fecha_entrega" id="fecha_entrega_masiva"
                               class="form-control" value="{{ now()->format('Y-m-d') }}">
                        <small class="text-muted">La fecha seleccionada se aplicará a todas las asignaciones.</small>
                    </div>
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="buscadorMasivo"
                                   class="form-control border-start-0 ps-0" placeholder="Buscar EPP para todos...">
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                        <table class="table table-hover align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Equipo (EPP)</th>
                                    <th style="width:70px;" class="text-center">Stock</th>
                                    <th style="width:90px;">Cant.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($epps as $epp)
                                @php $sinStock = $epp->stock <= 0; @endphp
                                <tr class="{{ $sinStock ? 'table-light text-muted' : '' }}">
                                    <td>
                                        <input class="form-check-input" type="checkbox"
                                               name="epps[{{ $epp->id }}][checked]" value="1"
                                               id="check_epp_{{ $epp->id }}"
                                               data-stock="{{ $epp->stock }}"
                                               {{ $sinStock ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        <label class="form-check-label w-100 small"
                                               for="check_epp_{{ $epp->id }}"
                                               style="{{ $sinStock ? '' : 'cursor:pointer;' }}">
                                            {{ $epp->nombre }}
                                            @if($sinStock)
                                                <span class="badge bg-danger ms-1" style="font-size:.7em;">AGOTADO</span>
                                            @endif
                                        </label>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $sinStock ? 'bg-danger' : 'bg-secondary' }}">{{ $epp->stock }}</span>
                                    </td>
                                    <td>
                                        <input type="number" name="epps[{{ $epp->id }}][cantidad]"
                                               class="form-control form-control-sm text-center"
                                               value="1" min="1"
                                               {{ $sinStock ? 'disabled' : '' }}>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 px-3 px-sm-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Confirmar Distribución</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════
     MODAL: Editar Personal
════════════════════════════════════════ --}}
<div class="modal fade" id="modalEditarPersonal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="fw-bold">Editar Datos del Docente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarPersonal" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre Completo</label>
                        <input type="text" name="nombre_completo" id="edit_nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">DNI</label>
                        <input type="text" name="dni" id="edit_dni" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Carrera / Especialidad</label>
                        <input type="text" name="carrera" id="edit_carrera" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tipo de Personal</label>
                        <select name="tipo_contrato" id="edit_tipo" class="form-select">
                            <option value="Docente TC">Docente Tiempo Completo</option>
                            <option value="Docente TP">Docente Tiempo Parcial</option>
                            <option value="Administrativo">Administrativo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Taller / Laboratorio</label>
                        <input type="text" name="taller_nombre" id="edit_taller"
                               class="form-control" list="listaTalleres"
                               placeholder="Escribe o selecciona..." autocomplete="off">
                        <datalist id="listaTalleres">
                            @foreach($talleres as $taller)
                                <option value="{{ $taller->nombre }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════
     MODAL: Confirmación de Acciones
════════════════════════════════════════ --}}
<div class="modal fade" id="modalConfirmacionAccion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4 text-center">
                <div id="iconoConfirmacion"
                     class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:60px; height:60px;">
                    <i class="bi bi-question-lg text-warning fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2" id="tituloConfirmacion">Confirmar Acción</h5>
                <p class="text-muted mb-4 small" id="mensajeConfirmacion">¿Estás seguro de realizar esta acción?</p>
                <form id="formConfirmacionAccion" method="POST" action="">
                    @csrf @method('PUT')
                    <input type="hidden" name="estado" id="inputEstadoAccion" disabled>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                                data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold"
                                id="btnConfirmarAccion">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const matrizReglas = @json($matriz ?? []);

function normalizar(str) {
    return str ? str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toUpperCase().trim() : "";
}

function abrirModalEntrega(id, nombre, tallerDocente, puestoDocente) {
    document.getElementById('personal_id').value = id;
    document.getElementById('nombreDocente').innerText = nombre;

    document.querySelectorAll('#modalEntrega input[type="checkbox"]').forEach(c => c.checked = false);
    document.querySelectorAll('[id^="badge_info_"]').forEach(b => {
        b.style.display = 'none'; b.innerText = ''; b.className = 'badge ms-1';
    });

    let tallerDoc = normalizar(tallerDocente).replace(/\s*[,\/]\s*/g, '|');
    let talleresDocente = tallerDoc.split('|').map(t => t.trim()).filter(t => t !== '');

    matrizReglas.forEach(regla => {
        let tallerRegla = normalizar(regla.taller ? regla.taller.toString() : "");
        let aplica = tallerRegla === "" || tallerRegla === "TODOS"
            || talleresDocente.some(t => t === tallerRegla || t.includes(tallerRegla) || tallerRegla.includes(t));

        if (aplica) {
            let checkbox = document.getElementById('check_ind_' + regla.epp_id);
            let badge    = document.getElementById('badge_info_' + regla.epp_id);
            if (checkbox) {
                checkbox.checked  = true;
                checkbox.disabled = parseInt(checkbox.getAttribute('data-stock') ?? '0') <= 0;
                if (badge) {
                    let esObl = regla.tipo_requerimiento === 'obligatorio';
                    badge.innerText   = esObl ? 'Obligatorio' : 'Específico';
                    badge.className   = esObl ? 'badge bg-danger ms-1' : 'badge bg-info text-dark ms-1';
                    badge.style.display = 'inline-block';
                }
            }
        }
    });

    // Reordenar: marcados+stock → marcados+agotado → disponibles → agotados
    let tbody = document.querySelector('#modalEntrega tbody');
    let rows  = Array.from(tbody.querySelectorAll('tr'));
    rows.sort((a, b) => {
        let cbA = a.querySelector('input[type="checkbox"]');
        let cbB = b.querySelector('input[type="checkbox"]');
        let [cA, cB] = [cbA?.checked ? 1 : 0, cbB?.checked ? 1 : 0];
        let [sA, sB] = [parseInt(cbA?.getAttribute('data-stock') ?? '0'), parseInt(cbB?.getAttribute('data-stock') ?? '0')];
        let pA = cA && sA > 0 ? 3 : cA && sA <= 0 ? 2 : sA > 0 ? 1 : 0;
        let pB = cB && sB > 0 ? 3 : cB && sB <= 0 ? 2 : sB > 0 ? 1 : 0;
        return pB - pA;
    });
    rows.forEach(r => tbody.appendChild(r));

    new bootstrap.Modal(document.getElementById('modalEntrega')).show();
}

// Buscadores en modales
document.getElementById('buscadorIndividual').addEventListener('keyup', function () {
    let f = this.value.toLowerCase();
    document.querySelectorAll('#modalEntrega tbody tr').forEach(row => {
        row.style.display = (row.querySelector('label')?.textContent.toLowerCase() ?? '').includes(f) ? '' : 'none';
    });
});

document.getElementById('buscadorMasivo').addEventListener('keyup', function () {
    let f = this.value.toLowerCase();
    document.querySelectorAll('#modalMasivo tbody tr').forEach(row => {
        row.style.display = (row.querySelector('label')?.textContent.toLowerCase() ?? '').includes(f) ? '' : 'none';
    });
});

// Buscador tabla + cards
function filtrarTabla() {
    let busqueda = document.getElementById('buscadorDocente').value.toLowerCase();

    // Desktop table
    document.querySelectorAll('#tablaPersonal tbody tr').forEach(row => {
        let nombre = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() ?? '';
        let dni    = row.querySelector('td:nth-child(4)')?.textContent.toLowerCase() ?? '';
        row.style.display = (nombre.includes(busqueda) || dni.includes(busqueda)) ? '' : 'none';
    });

    // Mobile cards
    document.querySelectorAll('.card-mobile-item').forEach(card => {
        let nombre = card.dataset.nombre ?? '';
        let dni    = card.dataset.dni ?? '';
        card.style.display = (nombre.includes(busqueda) || dni.includes(busqueda)) ? '' : 'none';
    });
}

function editarPersonal(id, nombre, dni, carrera, tipo, tallerNombre) {
    document.getElementById('formEditarPersonal').action = '/personals/' + id;
    document.getElementById('edit_nombre').value  = nombre;
    document.getElementById('edit_dni').value     = dni;
    document.getElementById('edit_carrera').value = carrera;
    document.getElementById('edit_tipo').value    = tipo;
    document.getElementById('edit_taller').value  = tallerNombre;
    new bootstrap.Modal(document.getElementById('modalEditarPersonal')).show();
}

function confirmarDevolucion(url) {
    document.getElementById('formConfirmacionAccion').action = url;
    document.getElementById('tituloConfirmacion').innerText  = 'Confirmar Devolución';
    document.getElementById('mensajeConfirmacion').innerText = '¿Confirmar devolución en buen estado? El stock aumentará.';
    document.getElementById('inputEstadoAccion').disabled    = true;
    document.getElementById('iconoConfirmacion').className   = 'bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3';
    document.getElementById('iconoConfirmacion').innerHTML   = '<i class="bi bi-check-lg text-success fs-3"></i>';
    document.getElementById('btnConfirmarAccion').className  = 'btn btn-success rounded-pill px-4 fw-bold';
    document.getElementById('btnConfirmarAccion').innerText  = 'Sí, Devolver';
    new bootstrap.Modal(document.getElementById('modalConfirmacionAccion')).show();
}

function confirmarIncidencia(id, estado) {
    document.getElementById('formConfirmacionAccion').action = '/asignaciones/' + id + '/incidencia';
    document.getElementById('inputEstadoAccion').disabled    = false;
    document.getElementById('inputEstadoAccion').value       = estado;
    document.getElementById('tituloConfirmacion').innerText  = 'Reportar ' + estado;
    document.getElementById('mensajeConfirmacion').innerText = '¿Marcar este equipo como ' + estado + '?';
    let c = estado === 'Perdido' ? 'danger' : 'warning';
    document.getElementById('iconoConfirmacion').className   = `bg-${c} bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3`;
    document.getElementById('iconoConfirmacion').innerHTML   = `<i class="bi bi-exclamation-triangle-fill text-${c} fs-3"></i>`;
    document.getElementById('btnConfirmarAccion').className  = `btn btn-${c} rounded-pill px-4 fw-bold`;
    document.getElementById('btnConfirmarAccion').innerText  = 'Confirmar';
    new bootstrap.Modal(document.getElementById('modalConfirmacionAccion')).show();
}
</script>
@endsection