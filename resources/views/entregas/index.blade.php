@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Entrega de EPP</h2>
            <p class="text-muted mb-0">Lista de personal y asignación de equipos</p>
        </div>
        <button class="btn btn-dark rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalMasivo">
            <i class="bi bi-boxes me-2"></i> Asignar a Todos
        </button>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex align-items-center gap-3 flex-wrap">
            <i class="bi bi-funnel fs-4 text-primary"></i>
            <form action="{{ route('entregas.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-grow-1 flex-wrap" id="formFiltro">
                <select name="departamento_id" class="form-select w-auto shadow-sm" onchange="document.getElementById('formFiltro').submit()">
                    <option value="">Filtrar por Departamento</option>
                    @foreach($departamentos as $depto)
                        <option value="{{ $depto->id }}" {{ (isset($departamentoIdFiltro) && $departamentoIdFiltro == $depto->id) ? 'selected' : '' }}>
                            {{ $depto->nombre }}
                        </option>
                    @endforeach
                </select>
                
                <div class="input-group shadow-sm" style="max-width: 280px;">
                    <span class="input-group-text bg-white border-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" 
                        class="form-control border-0 ps-0" 
                        id="buscadorDocente" 
                        placeholder="Buscar docente, DNI..."
                        onkeyup="filtrarTabla()">
                </div>
                
                <a href="{{ route('entregas.index') }}" class="btn btn-light border">Limpiar</a>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="table table-hover align-middle mb-0" id="tablaPersonal" style="min-width: 1300px;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 18%;">Docente</th>
                        <th style="width: 14%;">Carrera</th>
                        <th style="width: 10%;">Tipo</th>
                        <th style="width: 10%;">DNI</th>
                        <th style="width: 24%;">EPPs Asignados</th>
                        <th style="width: 16%;">Estado / Acciones</th>
                        <th style="width: 8%; text-align: center;">Entregar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($personals as $persona)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @php $tallerNombre = $persona->talleres->first()->nombre ?? ''; @endphp
                                <div>
                                    <p class="mb-0 fw-500">{{ $persona->nombre_completo }}</p>
                                    <small class="text-muted">{{ $tallerNombre }}</small>
                                </div>
                                <button class="btn btn-link btn-sm text-muted ms-2 p-0"
                                    onclick="editarPersonal({{ $persona->id }}, '{{ $persona->nombre_completo }}', '{{ $persona->dni }}', '{{ $persona->carrera }}', '{{ $persona->tipo_contrato }}', '{{ $tallerNombre }}')"
                                    title="Editar datos">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border" style="font-size: 0.85rem;">{{ $persona->carrera }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border" style="font-size: 0.85rem;">{{ $persona->tipo_contrato ?? '---' }}</span>
                        </td>
                        <td><small>{{ $persona->dni }}</small></td>

                        {{-- Columna EPPs: solo nombre + cantidad --}}
                        <td>
                            @forelse($persona->asignaciones as $asignacion)
                                <div class="py-1 border-bottom" style="font-size: 0.88rem; min-height: 38px; display: flex; align-items: center;">
                                    <span class="text-dark fw-500">{{ $asignacion->epp->nombre }}</span>
                                    <span class="text-muted ms-1">x{{ $asignacion->cantidad }}</span>
                                </div>
                            @empty
                                <span class="text-muted small fst-italic">Sin asignaciones</span>
                            @endforelse
                        </td>

                        {{-- Columna Estado/Acciones: alineada fila por fila con EPPs --}}
                        <td>
                            @forelse($persona->asignaciones as $asignacion)
                                <div class="py-1 border-bottom d-flex align-items-center gap-1" style="min-height: 38px;">
                                    @if($asignacion->estado == 'Entregado')
                                        {{-- Botón devolver --}}
                                        <button type="button"
                                            class="btn btn-success btn-sm py-0 px-2"
                                            style="font-size: 0.78rem; white-space: nowrap;"
                                            title="Devolver"
                                            onclick="confirmarDevolucion('{{ route('asignaciones.devolver', $asignacion->id) }}')">
                                            <i class="bi bi-check-lg"></i> Devolver
                                        </button>
                                        {{-- Botón Dañado (directo, sin dropdown) --}}
                                        <button type="button"
                                            class="btn btn-outline-warning btn-sm py-0 px-2"
                                            style="font-size: 0.78rem; white-space: nowrap;"
                                            title="Marcar como Dañado"
                                            onclick="confirmarIncidencia({{ $asignacion->id }}, 'Dañado')">
                                            <i class="bi bi-tools"></i>
                                        </button>
                                        {{-- Botón Perdido (directo, sin dropdown) --}}
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm py-0 px-2"
                                            style="font-size: 0.78rem; white-space: nowrap;"
                                            title="Marcar como Perdido"
                                            onclick="confirmarIncidencia({{ $asignacion->id }}, 'Perdido')">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    @elseif($asignacion->estado == 'Devuelto')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success" style="font-size: 0.75rem;">
                                            <i class="bi bi-check-circle me-1"></i>Devuelto
                                        </span>
                                    @elseif($asignacion->estado == 'Dañado')
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning" style="font-size: 0.75rem;">
                                            <i class="bi bi-tools me-1"></i>Dañado
                                        </span>
                                    @elseif($asignacion->estado == 'Perdido')
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger" style="font-size: 0.75rem;">
                                            <i class="bi bi-x-circle me-1"></i>Perdido
                                        </span>
                                    @endif
                                </div>
                            @empty
                                <span class="text-muted small fst-italic">—</span>
                            @endforelse
                        </td>

                        {{-- Columna Entregar --}}
                        <td style="text-align: center;">
                            {{-- DESPUÉS: escapar correctamente con addslashes para JS --}}
<button class="btn btn-primary btn-sm rounded-pill px-3"
    onclick="abrirModalEntrega(
        {{ $persona->id }},
        '{{ addslashes($persona->nombre_completo) }}',
        '{{ addslashes($tallerNombre) }}',
        '{{ addslashes($persona->tipo_contrato ?? '') }}'
    )">
    <i class="bi bi-hand-index-thumb me-1"></i> Entregar
</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Entrega Individual --}}
<div class="modal fade" id="modalEntrega" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px;">
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
                        <input type="date" name="fecha_entrega" id="fecha_entrega_individual" class="form-control" value="{{ now()->format('Y-m-d') }}">
                        <small class="text-muted">Si la entrega fue en una fecha pasada, selecciónala aquí.</small>
                    </div>
                    <div class="alert alert-light border mb-3 py-2">
                        <small class="text-muted"><i class="bi bi-check2-square me-1"></i> Marca los equipos que deseas entregar.</small>
                    </div>
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="buscadorIndividual" class="form-control border-start-0 ps-0" placeholder="Buscar EPP...">
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-hover align-middle table-sm">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 30px;"></th>
                                    <th>Equipo</th>
                                    <th style="width: 60px;" class="text-center">Stock</th>
                                    <th style="width: 80px;">Cant.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($epps as $epp)
                                @php $sinStock = $epp->stock <= 0; @endphp
                                <tr class="{{ $sinStock ? 'table-light text-muted' : '' }}">
                                    <td>
                                        <input class="form-check-input" type="checkbox" name="epps[{{ $epp->id }}][checked]" value="1" id="check_ind_{{ $epp->id }}" data-stock="{{ $epp->stock }}" {{ $sinStock ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        <label class="form-check-label w-100 small" for="check_ind_{{ $epp->id }}" style="{{ $sinStock ? '' : 'cursor: pointer;' }}">
                                            {{ $epp->nombre }}
                                            @if($sinStock)
                                                <span class="badge bg-danger ms-1" style="font-size: 0.7em;">AGOTADO</span>
                                            @endif
                                            <span id="badge_info_{{ $epp->id }}" class="badge ms-1" style="display: none; font-size: 0.7em;"></span>
                                        </label>
                                    </td>
                                    <td class="text-center"><span class="badge {{ $sinStock ? 'bg-danger' : 'bg-secondary' }}">{{ $epp->stock }}</span></td>
                                    <td>
                                        <input type="number" name="epps[{{ $epp->id }}][cantidad]" class="form-control form-control-sm text-center" value="1" min="1" {{ $sinStock ? 'disabled' : '' }}>
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

{{-- Modal Asignación Masiva --}}
<div class="modal fade" id="modalMasivo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-header border-0 bg-light" style="border-radius: 20px 20px 0 0;">
                <h5 class="fw-bold"><i class="bi bi-people-fill me-2"></i>Asignación Masiva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('entregas.asignar_masivo') }}" method="POST">
                @csrf
                <input type="hidden" name="departamento_id" value="{{ $departamentoIdFiltro ?? '' }}">
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 d-flex align-items-center" role="alert">
                        <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                        <div>Se asignará el equipo seleccionado a <strong>{{ $personals->count() }}</strong> docentes.</div>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_entrega_masiva" class="form-label fw-bold small">Fecha de Entrega</label>
                        <input type="date" name="fecha_entrega" id="fecha_entrega_masiva" class="form-control" value="{{ now()->format('Y-m-d') }}">
                        <small class="text-muted">La fecha seleccionada se aplicará a todas las asignaciones.</small>
                    </div>
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="buscadorMasivo" class="form-control border-start-0 ps-0" placeholder="Buscar EPP para todos...">
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>Equipo (EPP)</th>
                                    <th style="width: 80px;">Stock</th>
                                    <th style="width: 100px;">Cant.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($epps as $epp)
                                @php $sinStock = $epp->stock <= 0; @endphp
                                <tr class="{{ $sinStock ? 'table-light text-muted' : '' }}">
                                    <td>
                                        <input class="form-check-input" type="checkbox" name="epps[{{ $epp->id }}][checked]" value="1" id="check_epp_{{ $epp->id }}" data-stock="{{ $epp->stock }}" {{ $sinStock ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        <label class="form-check-label w-100" for="check_epp_{{ $epp->id }}" style="{{ $sinStock ? '' : 'cursor: pointer;' }}">
                                            {{ $epp->nombre }}
                                            @if($sinStock) <span class="badge bg-danger ms-1" style="font-size: 0.7em;">AGOTADO</span> @endif
                                        </label>
                                    </td>
                                    <td class="text-center"><span class="badge {{ $sinStock ? 'bg-danger' : 'bg-secondary' }}">{{ $epp->stock }}</span></td>
                                    <td>
                                        <input type="number" name="epps[{{ $epp->id }}][cantidad]" class="form-control form-control-sm text-center" value="1" min="1" {{ $sinStock ? 'disabled' : '' }}>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Confirmar Distribución</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Editar Personal --}}
<div class="modal fade" id="modalEditarPersonal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-header border-0">
                <h5 class="fw-bold">Editar Datos del Docente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarPersonal" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" name="nombre_completo" id="edit_nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">DNI</label>
                        <input type="text" name="dni" id="edit_dni" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Carrera / Especialidad</label>
                        <input type="text" name="carrera" id="edit_carrera" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Personal</label>
                        <select name="tipo_contrato" id="edit_tipo" class="form-select">
                            <option value="Docente TC">Docente Tiempo Completo</option>
                            <option value="Docente TP">Docente Tiempo Parcial</option>
                            <option value="Administrativo">Administrativo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Taller / Laboratorio</label>
                        <input type="text" name="taller_nombre" id="edit_taller" class="form-control" list="listaTalleres" placeholder="Escribe o selecciona..." autocomplete="off">
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

{{-- Modal Confirmación de Acciones --}}
<div class="modal fade" id="modalConfirmacionAccion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-body p-4 text-center">
                <div id="iconoConfirmacion"
                    class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                    style="width: 60px; height: 60px;">
                    <i class="bi bi-question-lg text-warning fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2" id="tituloConfirmacion">Confirmar Acción</h5>
                <p class="text-muted mb-4" id="mensajeConfirmacion">¿Estás seguro de realizar esta acción?</p>
                <form id="formConfirmacionAccion" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="estado" id="inputEstadoAccion" disabled>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold" id="btnConfirmarAccion">Confirmar</button>
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

    // 1. Limpiar todos los checks y badges
    document.querySelectorAll('#modalEntrega input[type="checkbox"]').forEach(check => {
        check.checked = false;
    });
    document.querySelectorAll('[id^="badge_info_"]').forEach(badge => {
        badge.style.display = 'none';
        badge.innerText = '';
        badge.className = 'badge ms-1';
    });

    // 2. Normalizar talleres del docente (soporta comas y barras)
    let tallerDoc = normalizar(tallerDocente).replace(/\s*[,\/]\s*/g, '|');
    let talleresDocente = tallerDoc.split('|').map(t => t.trim()).filter(t => t !== '');

    console.log('Talleres del docente:', talleresDocente);
    console.log('Total reglas en matriz:', matrizReglas.length);

    // 3. Marcar EPPs según la matriz (incluso agotados)
    matrizReglas.forEach(regla => {
        let tallerRegla = normalizar(regla.taller ? regla.taller.toString() : "");

        let aplica = false;
        if (tallerRegla === "" || tallerRegla === "TODOS") {
            aplica = true;
        } else {
            aplica = talleresDocente.some(t =>
                t === tallerRegla || t.includes(tallerRegla) || tallerRegla.includes(t)
            );
        }

        if (aplica) {
            let checkbox = document.getElementById('check_ind_' + regla.epp_id);
            let badge = document.getElementById('badge_info_' + regla.epp_id);

            if (checkbox) {
                checkbox.checked = true;

                // Si no tiene stock, marcar visualmente pero deshabilitar
                let stock = parseInt(checkbox.getAttribute('data-stock') ?? '0');
                if (stock <= 0) {
                    checkbox.disabled = true;
                } else {
                    checkbox.disabled = false;
                }

                if (badge) {
                    let esObligatorio = regla.tipo_requerimiento === 'obligatorio';
                    badge.innerText = esObligatorio ? 'Obligatorio' : 'Específico';
                    badge.className = esObligatorio
                        ? 'badge bg-danger ms-1'
                        : 'badge bg-info text-dark ms-1';
                    badge.style.display = 'inline-block';
                }
            }
        }
    });

    // 4. Reordenar: marcados arriba, luego disponibles, luego agotados
    let tbody = document.querySelector('#modalEntrega tbody');
    let rows = Array.from(tbody.querySelectorAll('tr'));
    rows.sort((a, b) => {
        let cbA = a.querySelector('input[type="checkbox"]');
        let cbB = b.querySelector('input[type="checkbox"]');
        let checkedA = cbA?.checked ? 1 : 0;
        let checkedB = cbB?.checked ? 1 : 0;
        let stockA = parseInt(cbA?.getAttribute('data-stock') ?? '0');
        let stockB = parseInt(cbB?.getAttribute('data-stock') ?? '0');

        // Primero: marcados con stock
        // Segundo: marcados sin stock (agotados)
        // Tercero: no marcados con stock
        // Cuarto: no marcados sin stock
        let prioridadA = checkedA && stockA > 0 ? 3 : checkedA && stockA <= 0 ? 2 : stockA > 0 ? 1 : 0;
        let prioridadB = checkedB && stockB > 0 ? 3 : checkedB && stockB <= 0 ? 2 : stockB > 0 ? 1 : 0;

        return prioridadB - prioridadA;
    });
    rows.forEach(row => tbody.appendChild(row));

    new bootstrap.Modal(document.getElementById('modalEntrega')).show();
}

// Buscador modal individual
document.getElementById('buscadorIndividual').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    document.querySelectorAll('#modalEntrega tbody tr').forEach(row => {
        let texto = row.querySelector('label')?.textContent.toLowerCase() ?? '';
        row.style.display = texto.includes(filter) ? '' : 'none';
    });
});

// Buscador modal masivo
document.getElementById('buscadorMasivo').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    document.querySelectorAll('#modalMasivo tbody tr').forEach(row => {
        let texto = row.querySelector('label')?.textContent.toLowerCase() ?? '';
        row.style.display = texto.includes(filter) ? '' : 'none';
    });
});

function filtrarTabla() {
    let busqueda = document.getElementById('buscadorDocente').value.toLowerCase();
    document.querySelectorAll('#tablaPersonal tbody tr').forEach(row => {
        let nombre = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() ?? '';
        let dni = row.querySelector('td:nth-child(4)')?.textContent.toLowerCase() ?? '';
        row.style.display = (nombre.includes(busqueda) || dni.includes(busqueda)) ? '' : 'none';
    });
}

function editarPersonal(id, nombre, dni, carrera, tipo, tallerNombre) {
    document.getElementById('formEditarPersonal').action = '/personals/' + id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_dni').value = dni;
    document.getElementById('edit_carrera').value = carrera;
    document.getElementById('edit_tipo').value = tipo;
    document.getElementById('edit_taller').value = tallerNombre;
    new bootstrap.Modal(document.getElementById('modalEditarPersonal')).show();
}

function confirmarDevolucion(url) {
    document.getElementById('formConfirmacionAccion').action = url;
    document.getElementById('tituloConfirmacion').innerText = 'Confirmar Devolución';
    document.getElementById('mensajeConfirmacion').innerText = '¿Confirmar devolución en buen estado? El stock aumentará.';
    document.getElementById('inputEstadoAccion').disabled = true;
    document.getElementById('iconoConfirmacion').className = 'bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4';
    document.getElementById('iconoConfirmacion').innerHTML = '<i class="bi bi-check-lg text-success fs-3"></i>';
    document.getElementById('btnConfirmarAccion').className = 'btn btn-success rounded-pill px-4 fw-bold';
    document.getElementById('btnConfirmarAccion').innerText = 'Sí, Devolver';
    new bootstrap.Modal(document.getElementById('modalConfirmacionAccion')).show();
}

function confirmarIncidencia(id, estado) {
    document.getElementById('formConfirmacionAccion').action = '/asignaciones/' + id + '/incidencia';
    document.getElementById('inputEstadoAccion').disabled = false;
    document.getElementById('inputEstadoAccion').value = estado;
    document.getElementById('tituloConfirmacion').innerText = 'Reportar ' + estado;
    document.getElementById('mensajeConfirmacion').innerText = '¿Marcar este equipo como ' + estado + '?';
    let colorClass = estado === 'Perdido' ? 'danger' : 'warning';
    document.getElementById('iconoConfirmacion').className = 'bg-' + colorClass + ' bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4';
    document.getElementById('iconoConfirmacion').innerHTML = '<i class="bi bi-exclamation-triangle-fill text-' + colorClass + ' fs-3"></i>';
    document.getElementById('btnConfirmarAccion').className = 'btn btn-' + colorClass + ' rounded-pill px-4 fw-bold';
    document.getElementById('btnConfirmarAccion').innerText = 'Confirmar';
    new bootstrap.Modal(document.getElementById('modalConfirmacionAccion')).show();
}
</script>
@endsection