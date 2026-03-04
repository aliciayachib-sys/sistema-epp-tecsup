@extends('layouts.app')

@section('content')
<style>
    :root {
        --tecsup-blue: #003a70;
        --accent-color: #007bff;
    }
    .card-modern {
        border: none;
        border-radius: 24px;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        overflow: hidden;
        background: #fff;
    }
    .card-modern:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.12) !important;
    }
    .img-container {
        position: relative;
        height: 240px;
        overflow: hidden;
    }
    .img-gradient {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.4) 60%, transparent 100%);
        z-index: 1;
    }
    .badge-docentes {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(5px);
        color: var(--tecsup-blue);
        padding: 8px 15px;
        border-radius: 12px;
        font-weight: 800;
        z-index: 2;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .btn-manage {
        background: var(--tecsup-blue);
        border: none;
        padding: 12px;
        font-weight: 600;
        transition: 0.3s;
    }
    .btn-manage:hover {
        background: var(--accent-color);
        box-shadow: 0 8px 15px rgba(0,123,255,0.3);
    }
    .page-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 40px 0;
        border-radius: 30px;
        margin-bottom: 40px;
    }
</style>

<div class="container py-4">
    <div class="page-header px-5 shadow-sm d-flex justify-content-between align-items-center">
        <div>
            <h1 class="display-5 fw-bold text-dark mb-1">Panel de Control</h1>
            <p class="fs-5 text-muted mb-0">Gestión de Seguridad por Departamentos</p>
        </div>
        <div class="d-flex gap-3">
            <a href="{{ route('organizador.index') }}" class="btn btn-white shadow-sm rounded-pill px-4 py-2 fw-bold text-primary border">
                <i class="bi bi-person-gear me-2"></i>Organizar
            </a>
            <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#nuevoDeptoModal">
                <i class="bi bi-plus-lg me-2"></i>Nuevo Departamento
            </button>
            <button class="btn btn-outline-danger rounded-circle p-2 shadow-sm" title="Limpiar todo" data-bs-toggle="modal" data-bs-target="#modalBorrarTodo">
                <i class="bi bi-trash3 px-1"></i>
            </button>
        </div>
    </div>

    <div class="row">
        @foreach($departamentos as $depto)
        <div class="col-md-6 col-lg-4 mb-5">
            <div class="card card-modern shadow-sm h-100">
                <div class="img-container">
                    <div class="badge-docentes">
                        <i class="bi bi-people-fill me-1"></i> {{ $depto->personals_count ?? 0 }}
                    </div>
                    <div class="position-absolute top-0 start-0 m-2" style="z-index: 10;">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light bg-opacity-75 rounded-circle" type="button" data-bs-toggle="dropdown" title="Opciones">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu shadow border-0">
                                <li>
                                    <button class="dropdown-item" onclick="abrirModalEditar({{ $depto->id }}, '{{ e($depto->nombre) }}', '{{ $depto->imagen_url }}')">
                                        <i class="bi bi-pencil me-2"></i>Editar
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item text-danger" onclick="abrirModalBorrarIndividual({{ $depto->id }}, '{{ e($depto->nombre) }}')">
                                        <i class="bi bi-trash me-2"></i>Eliminar
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <img src="{{ $depto->imagen_url ? (Str::startsWith($depto->imagen_url, 'http') ? $depto->imagen_url : asset($depto->imagen_url)) : 'https://source.unsplash.com/featured/?' . Str::slug($depto->nombre) . ',technology,industry' }}"
                         class="w-100 h-100 object-fit-cover" 
                         alt="{{ $depto->nombre }}"
                         onerror="this.onerror=null; this.src='https://source.unsplash.com/featured/?{{ Str::slug($depto->nombre) }},technology,industry';">
                    <div class="img-gradient"></div>
                    <h4 class="position-absolute bottom-0 start-0 m-3 text-white fw-bold z-2">{{ $depto->nombre }}</h4>
                </div>
                
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="text-start">
                            <small class="text-uppercase text-muted fw-bold ls-1" style="font-size: 0.7rem;">Estado</small>
                            <div class="d-flex align-items-center">
                                <span class="bg-success rounded-circle me-2" style="width: 8px; height: 8px;"></span>
                                <span class="fw-bold text-dark">Operativo</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <small class="text-uppercase text-muted fw-bold ls-1" style="font-size: 0.7rem;">Seguridad</small>
                            <div class="text-warning fw-bold">100% OK</div>
                        </div>
                    </div>

                    <a href="{{ route('entregas.index', ['departamento_id' => $depto->id]) }}" class="btn btn-manage w-100 rounded-pill text-white">
                        Gestionar Personal <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="modal fade" id="nuevoDeptoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 28px;">
            <div class="modal-body p-5">
                <div class="text-center mb-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-building-add text-primary fs-1"></i>
                    </div>
                    <h3 class="fw-bold">Nueva Área</h3>
                    <p class="text-muted">Ingresa el nombre del departamento para comenzar la gestión de EPP.</p>
                </div>
                
                <form action="{{ route('departamentos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-bold">Nombre del Departamento</label>
                        <input type="text" name="nombre" class="form-control form-control-lg border-0 bg-light px-4 py-3" 
                               placeholder="Nombre del Departamento..." required style="border-radius: 15px;">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Imagen de Portada (Opcional)</label>
                        <ul class="nav nav-pills mb-2 gap-2" id="pills-tab" role="tablist">
                            <li class="nav-item"><button class="nav-link active btn-sm rounded-pill" id="pills-file-tab" data-bs-toggle="pill" data-bs-target="#pills-file" type="button">Subir Archivo</button></li>
                            <li class="nav-item"><button class="nav-link btn-sm rounded-pill" id="pills-url-tab" data-bs-toggle="pill" data-bs-target="#pills-url" type="button">Usar URL</button></li>
                        </ul>
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-file">
                                <input type="file" name="imagen" class="form-control" accept="image/*">
                            </div>
                            <div class="tab-pane fade" id="pills-url">
                                <input type="url" name="imagen_url_text" class="form-control" placeholder="https://ejemplo.com/imagen.jpg">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold py-3">
                        Crear Departamento
                    </button>
                    <button type="button" class="btn btn-link w-100 text-muted text-decoration-none mt-2" data-bs-dismiss="modal">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Departamento -->
<div class="modal fade" id="editarDeptoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 28px;">
            <div class="modal-body p-5">
                <div class="text-center mb-4">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-pencil-square text-warning fs-1"></i>
                    </div>
                    <h3 class="fw-bold">Editar Departamento</h3>
                </div>
                
                <form id="formEditarDepto" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="form-label fw-bold">Nombre del Departamento</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control form-control-lg border-0 bg-light px-4 py-3" required style="border-radius: 15px;">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Actualizar Imagen (Opcional)</label>
                        <ul class="nav nav-pills mb-2 gap-2" id="pills-tab-edit" role="tablist">
                            <li class="nav-item"><button class="nav-link active btn-sm rounded-pill" id="pills-file-tab-edit" data-bs-toggle="pill" data-bs-target="#pills-file-edit" type="button">Subir Archivo</button></li>
                            <li class="nav-item"><button class="nav-link btn-sm rounded-pill" id="pills-url-tab-edit" data-bs-toggle="pill" data-bs-target="#pills-url-edit" type="button">Usar URL</button></li>
                        </ul>
                        <div class="tab-content" id="pills-tabContent-edit">
                            <div class="tab-pane fade show active" id="pills-file-edit">
                                <input type="file" name="imagen" class="form-control" accept="image/*">
                            </div>
                            <div class="tab-pane fade" id="pills-url-edit">
                                <input type="url" name="imagen_url_text" id="edit_imagen_url" class="form-control" placeholder="https://ejemplo.com/imagen.jpg">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning btn-lg w-100 rounded-pill fw-bold py-3 text-white">
                        Guardar Cambios
                    </button>
                    <button type="button" class="btn btn-link w-100 text-muted text-decoration-none mt-2" data-bs-dismiss="modal">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Borrar Todo -->
<div class="modal fade" id="modalBorrarTodo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-body p-4 text-center">
                <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 70px; height: 70px;">
                    <i class="bi bi-exclamation-octagon-fill text-danger fs-1"></i>
                </div>
                <h4 class="fw-bold mb-2">¿Estás seguro de borrar todo?</h4>
                <p class="text-muted mb-4">Esta acción eliminará <strong>todos los departamentos</strong>. Los docentes volverán a la lista maestra "Sin Asignar".</p>
                
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 py-2" data-bs-dismiss="modal">Cancelar</button>
                    
                    <form action="{{ route('departamentos.destroy_all') }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill px-4 py-2 fw-bold">Sí, Borrar Todo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Borrar Individual -->
<div class="modal fade" id="modalBorrarIndividual" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-body p-4 text-center">
                <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 70px; height: 70px;">
                    <i class="bi bi-trash text-danger fs-1"></i>
                </div>
                <h4 class="fw-bold mb-2">¿Eliminar Departamento?</h4>
                <p class="text-muted mb-4" id="mensajeBorrarIndividual"></p>
                
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 py-2" data-bs-dismiss="modal">Cancelar</button>
                    
                    <form id="formBorrarIndividual" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill px-4 py-2 fw-bold">Sí, Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function abrirModalEditar(id, nombre, imagenUrl) {
    const form = document.getElementById('formEditarDepto');
    form.action = `{{ url('departamentos') }}/${id}`;
    
    document.getElementById('edit_nombre').value = nombre;
    
    if (imagenUrl && imagenUrl.startsWith('http')) {
        document.getElementById('edit_imagen_url').value = imagenUrl;
    } else {
        document.getElementById('edit_imagen_url').value = '';
    }

    new bootstrap.Modal(document.getElementById('editarDeptoModal')).show();
}

function abrirModalBorrarIndividual(id, nombre) {
    const form = document.getElementById('formBorrarIndividual');
    const mensaje = document.getElementById('mensajeBorrarIndividual');
    
    // Construimos la URL dinámicamente.
    form.action = `{{ url('departamentos') }}/${id}`;
    
    mensaje.innerHTML = `Estás a punto de eliminar <strong>${nombre}</strong>. El personal asignado volverá a la lista maestra "Sin Asignar".`;

    const modal = new bootstrap.Modal(document.getElementById('modalBorrarIndividual'));
    modal.show();
}
</script>
@endsection