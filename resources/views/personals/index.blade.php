@extends('layouts.app')

@section('content')
<style>
    .card-master { border-radius: 20px; border: none; background: #ffffff; }

    .table-modern thead { background-color: #f8f9fa; }
    .table-modern th {
        border: none;
        color: #64748b;
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 1px;
        padding: 14px 12px;
        white-space: nowrap;
    }
    .table-modern td { padding: 14px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

    .avatar-circle {
        width: 40px; height: 40px; min-width: 40px;
        background: #003a70; color: white;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%; font-weight: bold; font-size: 0.9rem;
    }

    .status-badge { padding: 5px 10px; border-radius: 10px; font-size: 0.72rem; font-weight: 700; white-space: nowrap; }

    /* Buscador */
    .search-box { border-radius: 50px; border: 2px solid #e9ecef; padding: 8px 16px; transition: 0.2s; }
    .search-box:focus { border-color: #003366; box-shadow: 0 0 0 0.2rem rgba(0,51,102,0.1); }

    /* Botones acción header — scroll horizontal en móvil */
    .btn-group-header {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-start;
    }

    @media (min-width: 768px) {
        .btn-group-header { justify-content: flex-end; }
    }

    /* Tabla siempre scrollable */
    .table-scroll-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }

    /* Highlight de búsqueda */
    .highlight { background-color: #fff3cd; border-radius: 3px; padding: 0 2px; }

    /* Fila oculta por búsqueda */
    .row-hidden { display: none; }
</style>

<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Alertas --}}
    @if($message = session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert" style="border-radius: 15px;">
        <i class="bi bi-check-circle me-2"></i><strong>¡Éxito!</strong> {{ $message }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if($message = session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert" style="border-radius: 15px;">
        <i class="bi bi-exclamation-triangle me-2"></i><strong>Error:</strong> {{ $message }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Encabezado --}}
    <div class="mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
            <div>
                <h2 class="fw-bold mb-0 fs-4">Base de Datos de Docentes</h2>
                <p class="text-muted mb-0 small">Lista maestra para asignación de EPP</p>
            </div>
            <div class="btn-group-header">
                <a href="{{ route('organizador.index') }}" class="btn btn-outline-primary rounded-pill px-3 fw-bold">
                    <i class="bi bi-grid-3x3-gap me-1"></i>Organizador
                </a>
                <button class="btn btn-success rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalImportarPersonal">
                    <i class="bi bi-file-earmark-excel me-1"></i>Importar Excel
                </button>
                <button class="btn btn-dark rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalNuevoDocente">
                    <i class="bi bi-person-plus-fill me-1"></i>Registrar
                </button>
                <button class="btn btn-warning rounded-pill px-3 fw-bold d-none" id="btnEliminarSeleccionados"
                        data-bs-toggle="modal" data-bs-target="#modalConfirmarDelete"
                        onclick="prepararEliminacionMultiple()">
                    <i class="bi bi-trash me-1"></i>Eliminar Sel.
                </button>
                <button class="btn btn-danger rounded-pill px-3 fw-bold" onclick="confirmarVaciarTodo()">
                    <i class="bi bi-exclamation-triangle me-1"></i>Vaciar Todo
                </button>
            </div>
        </div>

        {{-- Buscador --}}
        <div class="d-flex align-items-center gap-2">
            <div class="position-relative flex-grow-1" style="max-width: 460px;">
                <i class="bi bi-search position-absolute text-muted" style="top: 50%; left: 14px; transform: translateY(-50%);"></i>
                <input type="text" id="buscadorDocentes" class="form-control search-box ps-5"
                       placeholder="Buscar por nombre, DNI, carrera, área...">
            </div>
            <span class="text-muted small" id="contadorResultados"></span>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card card-master shadow-sm">
        <div class="card-body p-0">
            <div class="table-scroll-wrapper">
                <table class="table table-modern mb-0" style="min-width: 900px;">
                    <thead>
                        <tr>
                            <th style="width: 36px;">
                                <input type="checkbox" id="selectAll" class="form-check-input" onchange="seleccionarTodos(this)">
                            </th>
                            <th>Docente</th>
                            <th>Carrera</th>
                            <th>Tipo</th>
                            <th>DNI / Código</th>
                            <th>Área Asignada</th>
                            <th class="text-center">EPPs en Uso</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaDocentes">
                        @forelse($personals as $docente)
                        <tr class="fila-docente"
                            data-nombre="{{ strtolower($docente->nombre_completo) }}"
                            data-dni="{{ strtolower($docente->dni ?? '') }}"
                            data-carrera="{{ strtolower($docente->carrera ?? '') }}"
                            data-area="{{ strtolower($docente->departamento->nombre ?? '') }}"
                            data-tipo="{{ strtolower($docente->tipo_contrato ?? '') }}">
                            <td>
                                <input type="checkbox" class="form-check-input chkDocente"
                                       value="{{ $docente->id }}" onchange="actualizarBotonEliminar()">
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle">
                                        {{ strtoupper(substr($docente->nombre_completo, 0, 1)) }}
                                    </div>
                                    <span class="fw-bold text-dark">{{ $docente->nombre_completo }}</span>
                                </div>
                            </td>
                            <td class="text-m    uted">{{ $docente->carrera ?? '---' }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $docente->tipo_contrato ?? '---' }}</span>
                            </td>
                            <td class="text-muted fw-bold">{{ $docente->dni ?? '---' }}</td>
                            <td>
                                @if($docente->departamento)
                                    <span class="status-badge bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-building me-1"></i>{{ $docente->departamento->nombre }}
                                    </span>
                                @else
                                    <span class="status-badge bg-warning bg-opacity-10 text-warning">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Sin Asignar
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php $cantidad = $docente->asignaciones->count(); @endphp
                                @if($cantidad > 0)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 rounded-pill">
                                        <i class="bi bi-shield-check me-1"></i>{{ $cantidad }} Asignados
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 rounded-pill">
                                        <i class="bi bi-dash-circle me-1"></i>Ninguno
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <button class="btn btn-light btn-sm rounded-circle shadow-sm"
                                            onclick="abrirModalEditar(this)"
                                            data-id="{{ $docente->id }}"
                                            data-nombre="{{ $docente->nombre_completo }}"
                                            data-dni="{{ $docente->dni ?? '' }}"
                                            data-carrera="{{ $docente->carrera ?? '' }}"
                                            data-tipo="{{ $docente->tipo_contrato ?? '' }}"
                                            title="Editar">
                                        <i class="bi bi-pencil text-primary"></i>
                                    </button>
                                    <button class="btn btn-light btn-sm rounded-circle shadow-sm text-danger"
                                            title="Eliminar"
                                            onclick="confirmarEliminacion('{{ route('personals.destroy', $docente->id) }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-people display-4 text-light"></i>
                                <p class="text-muted mt-2 mb-0">No hay docentes en la base de datos.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Sin resultados de búsqueda --}}
            <div id="sinResultados" class="text-center py-5 d-none">
                <i class="bi bi-search display-4 text-light"></i>
                <p class="text-muted mt-2 mb-0">No se encontraron docentes con ese criterio.</p>
                <button class="btn btn-sm btn-outline-secondary rounded-pill mt-2" onclick="limpiarBusqueda()">
                    <i class="bi bi-x-circle me-1"></i>Limpiar búsqueda
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODALES ===================== --}}

<!-- Modal Nuevo Docente -->
<div class="modal fade" id="modalNuevoDocente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mx-3 mx-sm-auto">
        <div class="modal-content border-0 shadow" style="border-radius: 24px;">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" style="z-index:1050;" data-bs-dismiss="modal"></button>
            <div class="modal-body p-4 p-sm-5 text-center">
                <div class="bg-dark bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;">
                    <i class="bi bi-person-badge text-dark fs-2"></i>
                </div>
                <h4 class="fw-bold mb-1">Registrar Docente</h4>
                <p class="text-muted mb-4">Añade al personal a la lista maestra</p>
                <form action="{{ route('personals.store') }}" method="POST">
                    @csrf
                    <div class="text-start mb-3">
                        <label class="form-label small fw-bold text-muted">Nombre Completo</label>
                        <input type="text" name="nombre_completo" class="form-control bg-light border-0 py-2 px-3 rounded-3" placeholder="Ej. Pedro Picapiedra" required>
                    </div>
                    <div class="text-start mb-3">
                        <label class="form-label small fw-bold text-muted">DNI o Código de Planilla</label>
                        <input type="text" name="dni" class="form-control bg-light border-0 py-2 px-3 rounded-3" placeholder="Ej. 74859632">
                    </div>
                    <div class="text-start mb-3">
                        <label class="form-label small fw-bold text-muted">Carrera / Especialidad</label>
                        <input type="text" name="carrera" class="form-control bg-light border-0 py-2 px-3 rounded-3" placeholder="Ej. Mecánica, Software...">
                    </div>
                    <div class="text-start mb-4">
                        <label class="form-label small fw-bold text-muted">Tipo de Personal</label>
                        <select name="tipo_contrato" class="form-select bg-light border-0 py-2 px-3 rounded-3">
                            <option value="Docente TC">Docente Tiempo Completo</option>
                            <option value="Docente TP">Docente Tiempo Parcial</option>
                            <option value="Administrativo">Administrativo</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 rounded-pill py-2 fw-bold">Guardar en Lista Maestra</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Docente -->
<div class="modal fade" id="modalEditarDocente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mx-3 mx-sm-auto">
        <div class="modal-content border-0 shadow" style="border-radius: 24px;">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" style="z-index:1050;" data-bs-dismiss="modal"></button>
            <div class="modal-body p-4 p-sm-5 text-center">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;">
                    <i class="bi bi-pencil-square text-primary fs-2"></i>
                </div>
                <h4 class="fw-bold mb-1">Editar Docente</h4>
                <p class="text-muted mb-4">Actualizar información del personal</p>
                <form id="formEditarDocente" method="POST">
                    @csrf @method('PUT')
                    <div class="text-start mb-3">
                        <label class="form-label small fw-bold text-muted">Nombre Completo</label>
                        <input type="text" name="nombre_completo" id="edit_nombre" class="form-control bg-light border-0 py-2 px-3 rounded-3" required>
                    </div>
                    <div class="text-start mb-3">
                        <label class="form-label small fw-bold text-muted">DNI o Código de Planilla</label>
                        <input type="text" name="dni" id="edit_dni" class="form-control bg-light border-0 py-2 px-3 rounded-3">
                    </div>
                    <div class="text-start mb-3">
                        <label class="form-label small fw-bold text-muted">Carrera / Especialidad</label>
                        <input type="text" name="carrera" id="edit_carrera" class="form-control bg-light border-0 py-2 px-3 rounded-3">
                    </div>
                    <div class="text-start mb-4">
                        <label class="form-label small fw-bold text-muted">Tipo de Personal</label>
                        <select name="tipo_contrato" id="edit_tipo" class="form-select bg-light border-0 py-2 px-3 rounded-3">
                            <option value="Docente TC">Docente Tiempo Completo</option>
                            <option value="Docente TP">Docente Tiempo Parcial</option>
                            <option value="Administrativo">Administrativo</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold" style="background-color:#003366;border:none;">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminación individual -->
<div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mx-3 mx-sm-auto">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-body p-4 text-center">
                <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">¿Estás seguro?</h5>
                <p class="text-muted mb-4">Vas a eliminar a este docente de la lista maestra. Esta acción no se puede deshacer.</p>
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formEliminarDocente" method="POST" action="">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold w-100">Sí, Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminar Seleccionados -->
<div class="modal fade" id="modalConfirmarDelete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mx-3 mx-sm-auto">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-body p-4 text-center">
                <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">¿Eliminar Seleccionados?</h5>
                <p class="text-muted mb-4">Vas a eliminar <strong id="countSeleccionados">0</strong> docente(s). Esta acción no se puede deshacer.</p>
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formEliminarSeleccionados" method="POST" action="{{ route('personals.delete_multiple') }}">
                        @csrf
                        <input type="hidden" id="inputIds" name="ids" value="">
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold w-100">Sí, Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Vaciar Todo -->
<div class="modal fade" id="modalConfirmarVaciar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mx-3 mx-sm-auto">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-body p-4 text-center">
                <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;">
                    <i class="bi bi-exclamation-lg text-danger fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">⚠️ ¡Cuidado!</h5>
                <p class="text-muted mb-1"><strong>Estás a punto de eliminar TODOS los docentes</strong></p>
                <p class="text-muted mb-4 small">Total: <strong id="countTotalDocentes">0</strong> docente(s). Esta acción <strong>no se puede deshacer</strong>.</p>
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="{{ route('personals.delete_all') }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold w-100">Sí, Vaciar Todo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Importar Excel -->
<div class="modal fade" id="modalImportarPersonal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mx-3 mx-sm-auto">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-excel me-2"></i>Importar Docentes desde Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('personals.import_excel') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3 text-center">
                        <i class="bi bi-cloud-arrow-up display-1 text-success opacity-50"></i>
                    </div>
                    <p class="mb-4 text-center">Selecciona el archivo Excel que contiene la matriz de docentes.</p>
                    <input type="file" name="file" class="form-control border-2" accept=".xlsx,.xls,.csv" required>
                    <div class="mt-4 bg-light p-3 rounded-3 small">
                        <p class="fw-bold mb-2"><i class="bi bi-info-circle me-2"></i>Formato esperado del archivo:</p>
                        <ul class="ps-3 mb-2">
                            <li><strong>Hoja:</strong> "Matriz x docente" (segunda hoja)</li>
                            <li><strong>Columnas (en orden):</strong> Docente, TC/TP, Taller/Lab, DNI, Carrera</li>
                        </ul>
                        <p class="mb-0 text-muted">✓ Los datos se mostrarán automáticamente en la tabla después de importar.</p>
                    </div>
                </div>
                <div class="modal-footer border-0 flex-column flex-sm-row gap-2">
                    <button type="button" class="btn btn-light w-100 w-sm-auto" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4 w-100 w-sm-auto">
                        <i class="bi bi-download me-2"></i>Subir e Importar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @media (min-width: 576px) { .w-sm-auto { width: auto !important; } }
</style>

<script>
// ── Buscador ──────────────────────────────────────────────
const buscador = document.getElementById('buscadorDocentes');
const contador = document.getElementById('contadorResultados');
const sinResultados = document.getElementById('sinResultados');

buscador.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    const filas = document.querySelectorAll('.fila-docente');
    let visibles = 0;

    filas.forEach(fila => {
        const hayCoincidencia =
            fila.dataset.nombre.includes(q) ||
            fila.dataset.dni.includes(q) ||
            fila.dataset.carrera.includes(q) ||
            fila.dataset.area.includes(q) ||
            fila.dataset.tipo.includes(q);

        if (hayCoincidencia || q === '') {
            fila.classList.remove('row-hidden');
            visibles++;
        } else {
            fila.classList.add('row-hidden');
        }
    });

    // Contador
    const total = filas.length;
    contador.textContent = q ? `${visibles} de ${total} docentes` : '';

    // Mensaje sin resultados
    sinResultados.classList.toggle('d-none', visibles > 0 || q === '');
    document.querySelector('.table-scroll-wrapper').classList.toggle('d-none', visibles === 0 && q !== '');
});

function limpiarBusqueda() {
    buscador.value = '';
    buscador.dispatchEvent(new Event('input'));
}

// ── Modales ───────────────────────────────────────────────
function abrirModalEditar(btn) {
    document.getElementById('formEditarDocente').action = '/personals/' + btn.dataset.id;
    document.getElementById('edit_nombre').value  = btn.dataset.nombre;
    document.getElementById('edit_dni').value     = btn.dataset.dni;
    document.getElementById('edit_carrera').value = btn.dataset.carrera;
    document.getElementById('edit_tipo').value    = btn.dataset.tipo;

    var el = document.getElementById('modalEditarDocente');
    (bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el)).show();
}

function confirmarEliminacion(url) {
    document.getElementById('formEliminarDocente').action = url;
    new bootstrap.Modal(document.getElementById('modalConfirmarEliminar')).show();
}

function seleccionarTodos(checkbox) {
    document.querySelectorAll('.chkDocente').forEach(cb => cb.checked = checkbox.checked);
    actualizarBotonEliminar();
}

function actualizarBotonEliminar() {
    const checked = document.querySelectorAll('.chkDocente:checked').length;
    const btn = document.getElementById('btnEliminarSeleccionados');
    document.getElementById('countSeleccionados').textContent = checked;
    btn.classList.toggle('d-none', checked === 0);
}

function confirmarVaciarTodo() {
    document.getElementById('countTotalDocentes').textContent =
        document.querySelectorAll('.chkDocente').length;
    new bootstrap.Modal(document.getElementById('modalConfirmarVaciar')).show();
}

function prepararEliminacionMultiple() {
    const ids = Array.from(document.querySelectorAll('.chkDocente:checked')).map(cb => cb.value).join(',');
    document.getElementById('inputIds').value = ids;
}

document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('.alert-success')) {
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalImportarPersonal'));
        if (modal) modal.hide();
    }
});
</script>
@endsection