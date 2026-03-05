@extends('layouts.app')

@section('content')
<style>
    @media (max-width: 575.98px) {
        /* Botones de acción apilados en móvil */
        .acciones-td {
            display: flex;
            flex-direction: column;
            gap: 4px;
            align-items: flex-start;
        }
        .acciones-td .btn,
        .acciones-td form { width: 100%; }
        .acciones-td form .btn { width: 100%; }

        /* Ocultar columna Departamento en móvil muy pequeño */
        .col-depto { display: none; }
    }
</style>

<div class="container-fluid px-3 px-md-4 py-4">

    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="fw-bold mb-0 fs-4">Talleres / Laboratorios</h2>
        <p class="text-muted mb-0 small">Gestión de ambientes por departamento</p>
    </div>

    <!-- Barra de Filtro + Botón Nuevo -->
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('talleres.index') }}"
                  class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center">
                <select name="departamento_id" class="form-select flex-grow-1" onchange="this.form.submit()">
                    <option value="">Todos los departamentos</option>
                    @foreach($departamentos as $dep)
                        <option value="{{ $dep->id }}" {{ ($depId == $dep->id) ? 'selected' : '' }}>
                            {{ $dep->nombre }}
                        </option>
                    @endforeach
                </select>
                <button type="button"
                        class="btn btn-primary flex-shrink-0"
                        data-bs-toggle="modal" data-bs-target="#modalCrear">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo Taller/Lab
                </button>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th class="col-depto">Departamento</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($talleres as $t)
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ $t->nombre }}</span>
                                {{-- En móvil mostramos el depto debajo del nombre --}}
                                <div class="d-block d-sm-none text-muted small mt-1">
                                    <i class="bi bi-building me-1"></i>{{ $t->departamento->nombre ?? '—' }}
                                </div>
                            </td>
                            <td class="col-depto">{{ $t->departamento->nombre ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $t->activo ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $t->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex flex-wrap justify-content-end gap-1">
                                    <button class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditar{{ $t->id }}">
                                        <i class="bi bi-pencil d-sm-none"></i>
                                        <span class="d-none d-sm-inline">Editar</span>
                                    </button>
                                    <form action="{{ route('talleres.toggle', $t) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-toggle-on d-sm-none"></i>
                                            <span class="d-none d-sm-inline">{{ $t->activo ? 'Desactivar' : 'Activar' }}</span>
                                        </button>
                                    </form>
                                    <form action="{{ route('talleres.destroy', $t) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar este taller/lab?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash d-sm-none"></i>
                                            <span class="d-none d-sm-inline">Eliminar</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Editar -->
                        <div class="modal fade" id="modalEditar{{ $t->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered mx-3 mx-sm-auto">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title fw-bold">Editar Taller/Lab</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="{{ route('talleres.update', $t) }}">
                                        @csrf @method('PUT')
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-uppercase text-muted">Nombre</label>
                                                <input type="text" name="nombre" class="form-control"
                                                       value="{{ $t->nombre }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-uppercase text-muted">Departamento</label>
                                                <select name="departamento_id" class="form-select" required>
                                                    @foreach($departamentos as $dep)
                                                        <option value="{{ $dep->id }}"
                                                            {{ $t->departamento_id == $dep->id ? 'selected' : '' }}>
                                                            {{ $dep->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 pt-0 flex-column flex-sm-row gap-2">
                                            <button type="button" class="btn btn-light w-100 w-sm-auto"
                                                    data-bs-dismiss="modal">Cancelar</button>
                                            <button class="btn btn-primary w-100 w-sm-auto"
                                                    style="background-color: #003366; border: none;">
                                                Guardar cambios
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-building fs-2 d-block mb-2 opacity-25"></i>
                                Sin talleres/labs registrados.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($talleres, 'links'))
        <div class="card-footer bg-white border-0 pt-0">
            {{ $talleres->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mx-3 mx-sm-auto">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Nuevo Taller/Lab</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('talleres.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Nombre</label>
                        <input type="text" name="nombre" class="form-control"
                               placeholder="Ej: Laboratorio de Electrónica" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Departamento</label>
                        <select name="departamento_id" class="form-select" required>
                            <option value="" disabled selected>Selecciona un departamento</option>
                            @foreach($departamentos as $dep)
                                <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 flex-column flex-sm-row gap-2">
                    <button type="button" class="btn btn-light w-100 w-sm-auto"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary w-100 w-sm-auto"
                            style="background-color: #003366; border: none;">
                        <i class="bi bi-plus-lg me-1"></i> Crear
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @media (min-width: 576px) {
        .w-sm-auto { width: auto !important; }
    }
</style>
@endsection