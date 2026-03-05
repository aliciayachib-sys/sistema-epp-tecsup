@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="fw-bold mb-0 fs-4">Categorías de EPP</h2>
        <p class="text-muted mb-0 small">Gestión de categorías para equipos de protección personal</p>
    </div>

    <!-- Card principal -->
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-3 p-sm-4">

            <!-- Formulario Agregar -->
            <form action="{{ route('categorias.store') }}" method="POST" class="mb-4">
                @csrf
                <label class="form-label small fw-bold text-uppercase text-muted">Nueva Categoría</label>
                <div class="d-flex flex-column flex-sm-row gap-2">
                    <input type="text" name="nombre" class="form-control flex-grow-1"
                           placeholder="Ej: Guantes Dieléctricos, Cascos, Protección Visual..."
                           required>
                    <button class="btn btn-success flex-shrink-0" type="submit">
                        <i class="bi bi-plus-circle me-1"></i> Agregar
                    </button>
                </div>
            </form>

            <hr class="my-3">

            <!-- Tabla -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-muted small" style="width: 60px;">#</th>
                            <th class="text-muted small">Nombre de la Categoría</th>
                            <th class="text-end text-muted small">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categorias as $categoria)
                        <tr>
                            <td class="text-muted small">{{ $categoria->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width: 32px; height: 32px;">
                                        <i class="bi bi-tag-fill text-primary" style="font-size: 0.75rem;"></i>
                                    </div>
                                    <span class="fw-semibold">{{ $categoria->nombre }}</span>
                                </div>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                        onclick="confirmarEliminacion('{{ route('categorias.destroy', $categoria->id) }}')">
                                    <i class="bi bi-trash d-sm-none"></i>
                                    <span class="d-none d-sm-inline"><i class="bi bi-trash me-1"></i>Eliminar</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="bi bi-tags fs-2 d-block mb-2 opacity-25"></i>
                                No hay categorías registradas aún.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mx-3 mx-sm-auto">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-body p-4 text-center">
                <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                     style="width: 60px; height: 60px;">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">¿Eliminar Categoría?</h5>
                <p class="text-muted mb-4">Esta acción borrará la categoría permanentemente. ¿Estás seguro?</p>
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancelar</button>
                    <form id="formEliminarCategoria" method="POST" action="">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold w-100">
                            Sí, Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarEliminacion(url) {
    document.getElementById('formEliminarCategoria').action = url;
    new bootstrap.Modal(document.getElementById('modalConfirmarEliminar')).show();
}
</script>
@endsection