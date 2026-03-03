@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Panel de Gestión EPP</h2>
            <p class="text-muted">Control de inventario y asignaciones para Jiancarlo</p>
        </div>
        <div class="d-flex gap-2">
           

            <button type="button" class="btn btn-outline-danger d-flex align-items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#modalVaciarEpps">
                <i class="bi bi-trash3 me-1"></i> Vaciar Todo
            </button>
            <button type="button" class="btn btn-success d-flex align-items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportarEpp">
                <i class="bi bi-file-earmark-excel me-1"></i> Importar Matriz
            </button>
            <button type="button" class="btn btn-primary d-flex align-items-center shadow-sm" style="background-color: #003366; border: none;" data-bs-toggle="modal" data-bs-target="#modalNuevoEpp">
                <i class="bi bi-plus fs-4 me-1"></i> Nuevo EPP
            </button>
        </div>
    </div>

    {{-- BARRA DE FILTROS AVANZADA --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-2 overflow-auto pb-2">
                            <span class="fw-bold text-muted small text-nowrap"><i class="bi bi-tag"></i> Categoría:</span>
                            <button class="btn btn-sm btn-primary rounded-pill filter-btn px-3" data-filter="all">Todas</button>
                            @foreach($categorias as $cat)
                                <button class="btn btn-sm btn-outline-primary rounded-pill filter-btn text-nowrap px-3" data-filter="cat-{{ $cat->id }}">
                                    {{ $cat->nombre }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    
                    

                    <div class="col-md-4">
                        <label class="small fw-bold text-muted mb-1 d-block"><i class="bi bi-list-stars"></i> Seleccionar Tipo:</label>
                        <select id="subtipoFilter" class="form-select form-select-sm border-primary shadow-sm">
                            <option value="all">-- Todos los tipos --</option>
                            @foreach($epps->pluck('nombre')->unique()->sort() as $nombreUnico)
                                <option value="{{ strtolower($nombreUnico) }}">{{ $nombreUnico }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="small fw-bold text-muted mb-1 d-block"><i class="bi bi-search"></i> Búsqueda por texto:</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="searchEpp" class="form-control border-start-0 ps-0" placeholder="Escribe código, nombre o detalles del material...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- GRILLA DE CARDS --}}
    <div class="row" id="eppGrid">
        @forelse($epps as $epp)
        <div class="col-md-4 mb-4 epp-item cat-{{ $epp->categoria_id }}" 
             data-subtipo="{{ strtolower($epp->nombre) }}" 
             data-nombre="{{ strtolower($epp->nombre) }}" 
             data-descripcion="{{ strtolower($epp->descripcion) }}"
             data-codigo="{{ strtolower($epp->codigo_logistica ?? '') }}"
        >
            
            <div class="card border-0 shadow-sm h-100 card-epp">
                <div class="epp-image-container position-relative" style="height: 180px; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                    @if($epp->imagen)
                        <img src="{{ asset('storage/' . $epp->imagen) }}" class="img-fluid h-100 p-2" style="object-fit: contain;">
                    @else
                        <i class="bi bi-box-seam display-4 text-light"></i>
                    @endif
                    
                    <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1 align-items-end" style="z-index: 2;">
                        @if($epp->stock > 10)
                            <span class="badge bg-success shadow-sm">Disponible</span>
                        @elseif($epp->stock > 0)
                            <span class="badge bg-warning text-dark shadow-sm">Stock Bajo</span>
                        @else
                            <span class="badge bg-danger shadow-sm">Agotado</span>
                        @endif

                    </div>
                </div>
                
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            @php
                                $partes = explode('-', $epp->nombre);
                                $principal = trim($partes[0]);
                                $subtipoDetalle = isset($partes[1]) ? trim($partes[1]) : null;
                            @endphp
                            <h6 class="fw-bold mb-0 text-dark">{{ Str::upper($principal) }}</h6>
                            @if($subtipoDetalle)
                                <span class="badge bg-light text-primary border border-primary-subtle" style="font-size: 0.65rem;">
                                    {{ Str::upper($subtipoDetalle) }}
                                </span>
                            @endif
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm border" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li>
                                    <a class="dropdown-item" href="{{ route('epps.show', $epp->id) }}">
                                        <i class="bi bi-eye me-2 text-info"></i> Ver Detalles
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('epps.edit', $epp->id) }}">
                                         <i class="bi bi-pencil me-2 text-primary"></i> Editar EPP
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#modalEliminarEpp" data-epp-nombre="{{ $epp->nombre }}" data-epp-url="{{ route('epps.destroy', $epp->id) }}">
                                        <i class="bi bi-trash me-2"></i> Eliminar
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <p class="text-muted mb-3 mt-1" style="font-size: 0.8rem; line-height: 1.2; height: 2.4em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                        {{ $epp->descripcion ?? 'Sin descripción adicional disponible.' }}
                    </p>

                    <div class="specs-box bg-light rounded-3 p-2 mb-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1">
                        <span class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-shield-check me-1"></i>VIGENCIA:</span>
                        <span class="fw-bold text-dark" style="font-size: 0.75rem;">
                            @if($epp->vida_util_meses)
                                {{ $epp->vida_util_meses >= 12 ? ($epp->vida_util_meses / 12) . ' Años' : $epp->vida_util_meses . ' Meses' }}
                            @else
                                Sin definir
                            @endif
                        </span>
                    </div>
                        <div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1">
                            <span class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-upc-scan me-1"></i>CÓDIGO:</span>
                            <span class="fw-bold text-dark" style="font-size: 0.75rem;">{{ $epp->codigo_logistica ?? 'CSK-'.$epp->id }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-box-seam me-1"></i>STOCK:</span>
                            <span class="fw-bold {{ $epp->stock <= 10 ? 'text-danger' : 'text-success' }}" style="font-size: 0.75rem;">{{ $epp->stock }} und.</span>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="d-grid">
                            <a href="{{ route('departamentos.index') }}" class="btn btn-success btn-sm shadow-sm {{ $epp->stock <= 0 ? 'disabled' : '' }}">
                                <i class="bi bi-building me-1"></i> Asignar a Departamento
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-archive display-1 text-muted opacity-25"></i>
            <p class="mt-3 text-muted">No hay EPPs registrados.</p>
        </div>
        @endforelse
    </div>
</div>

{{-- MODAL ELIMINAR EPP --}}
<div class="modal fade" id="modalEliminarEpp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p>¿Estás seguro de que deseas eliminar el EPP <strong id="eppNameToDelete"></strong>?</p>
                <p class="text-muted small">Esta acción es irreversible y eliminará el equipo del inventario.</p>
            </div>
            <div class="modal-footer border-0">
                <form id="deleteEppForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Sí, Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL NUEVO EPP --}}
<div class="modal fade" id="modalNuevoEpp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">Registrar Nuevo EPP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('epps.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Nombre del Equipo</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej. Guante - Nitrilo" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Fecha de Vencimiento (Opcional)</label>
                            <input type="date" name="fecha_vencimiento" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Descripción / Notas</label>
                            <textarea name="descripcion" class="form-control" rows="2" placeholder="Detalles del material, talla o uso específico..."></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Categoría</label>
                            <select name="categoria_id" class="form-select" required>
                                <option value="">Seleccione...</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Código Logística</label>
                            <input type="text" name="codigo_logistica" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Stock Inicial</label>
                            <input type="number" name="cantidad" class="form-control" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Vida Útil (Meses)</label>
                            <input type="number" name="vida_util_meses" class="form-control" value="12" min="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Imagen del producto</label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                        </div>
                        {{-- NEW: Date input for registration date --}}
                        <div class="col-12">
                            <label for="fecha_registro_manual" class="form-label fw-bold small">Fecha de Registro (opcional)</label>
                            <input type="date" name="fecha_registro" id="fecha_registro_manual" class="form-control">
                            <small class="form-text text-muted">Si no se especifica, se usará la fecha actual.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4" style="background-color: #003366;">Guardar EPP</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL VACIAR TODO --}}
<div class="modal fade" id="modalVaciarEpps" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title border-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>¿Estás seguro?</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p>Esta acción <b>eliminará todos los registros de EPP</b> y no se puede deshacer.</p>
                <p class="text-muted small">Se borrarán stocks, imágenes y configuraciones de todos los equipos.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                {{-- Asegúrate de que esta ruta exista en tu archivo web.php --}}
                <form action="{{ route('epps.clearAll') }}" method="POST">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger px-4">Sí, eliminar todo</button>
</form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lógica para el modal de eliminación
        const modalEliminar = document.getElementById('modalEliminarEpp');
        if (modalEliminar) {
            modalEliminar.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const eppNombre = button.getAttribute('data-epp-nombre');
                const eppUrl = button.getAttribute('data-epp-url');
                document.getElementById('eppNameToDelete').textContent = `"${eppNombre}"`;
                document.getElementById('deleteEppForm').action = eppUrl;
            });
        }

        const filterBtns = document.querySelectorAll('.filter-btn');
        const subtipoFilter = document.getElementById('subtipoFilter');
       
        const searchInput = document.getElementById('searchEpp');
        const items = document.querySelectorAll('.epp-item');

        function applyFilters() {
            const activeCatBtn = document.querySelector('.filter-btn.btn-primary');
            const activeCat = activeCatBtn ? activeCatBtn.dataset.filter : 'all';
            const activeSubtipo = subtipoFilter ? subtipoFilter.value : 'all';
            const searchText = searchInput.value.toLowerCase();

            items.forEach(item => {
                const matchesCat = activeCat === 'all' || item.classList.contains(activeCat);
                const matchesSubtipo = activeSubtipo === 'all' || item.dataset.subtipo === activeSubtipo;
                
                
                const matchesSearch = item.dataset.nombre.includes(searchText) || 
                                     item.dataset.codigo.includes(searchText) ||
                                     item.dataset.descripcion.includes(searchText);

                item.style.display = (matchesCat && matchesSubtipo && matchesSearch) ? 'block' : 'none';
            });
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-outline-primary');
                });
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
                applyFilters();
            });
        });

        subtipoFilter.addEventListener('change', applyFilters);
        
        searchInput.addEventListener('input', applyFilters);
    });
</script>

<style>
    .card-epp { transition: transform 0.2s, box-shadow 0.2s; border-radius: 12px; overflow: hidden; }
    .card-epp:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .specs-box { border: 1px solid #e9ecef !important; }
    .filter-btn { transition: all 0.2s; }
    .overflow-auto::-webkit-scrollbar { display: none; }

    .pulse-alert {
        animation: pulse-yellow 2s infinite;
    }
    @keyframes pulse-yellow {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
</style>


{{-- MODAL IMPORTAR MATRIZ --}}
<div class="modal fade" id="modalImportarEpp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-excel me-2"></i>Importar desde Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('epps.import_excel') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <i class="bi bi-cloud-arrow-up display-1 text-success opacity-50"></i>
                    </div>
                    <p class="mb-3">Selecciona el archivo Excel (.xlsx o .csv) que contiene la matriz de EPPs.</p>
                    <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required>
                    <div class="mt-3 small text-muted text-start">
                        {{-- NEW: Date input for registration date --}}
                        <label for="fecha_registro_excel" class="form-label fw-bold small text-start d-block">Fecha de Registro (opcional)</label>
                        <input type="date" name="fecha_registro" id="fecha_registro_excel" class="form-control">
                        <small class="form-text text-muted text-start d-block">Si no se especifica, se usará la fecha actual.</small>
                    </div>
                    <div class="mt-3 small text-muted text-start">
                        <p class="mb-1 fw-bold">Requisitos del archivo:</p>
                        <ul class="ps-3">
                            <li>Columnas: Nombre, Categoría, Stock Inicial, Código.</li>
                            <li>La categoría debe existir previamente en el sistema.</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4">Subir e Importar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection