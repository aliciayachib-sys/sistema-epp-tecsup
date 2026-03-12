@extends('layouts.app')

@section('content')
<style>
    /* ── CARDS ── */
    .card-epp {
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 12px;
        overflow: hidden;
    }
    @media (hover: hover) {
        .card-epp:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
    }
    .specs-box { border: 1px solid #e9ecef !important; }

    /* ── SIN IMAGEN PLACEHOLDER ── */
    .no-image-placeholder {
        width: 100%; height: 100%;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf0 100%);
        color: #aab0b8;
    }
    .no-image-placeholder i  { font-size: 2.2rem; margin-bottom: 6px; opacity: 0.5; }
    .no-image-placeholder span {
        font-size: 0.62rem; font-weight: 700;
        letter-spacing: 1.5px; text-transform: uppercase; opacity: 0.6;
    }

    /* ── FILTER STRIP ── */
    .filter-strip {
        display: flex; align-items: center; gap: 6px;
        overflow-x: auto; padding-bottom: 4px;
        -webkit-overflow-scrolling: touch;
    }
    .filter-strip::-webkit-scrollbar { display: none; }
    .filter-btn { transition: all 0.2s; white-space: nowrap; }

    .page-title { font-size: clamp(1.2rem, 4vw, 1.6rem); }
    .modal-content { border-radius: 15px !important; border: none !important; }

    /* ── SECTION LABEL in modal ── */
    .section-label {
        font-size: .68rem; font-weight: 700; letter-spacing: .08em;
        text-transform: uppercase; color: #6c757d;
        display: flex; align-items: center; gap: 6px;
        padding: 6px 10px; background: #f8f9fa;
        border-radius: 6px; margin-bottom: 14px;
        border-left: 3px solid #003366;
    }

    /* ── DEPTO CHIPS ── */
    .depto-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
        gap: 8px;
    }
    .depto-chip {
        display: flex; align-items: center; gap: 8px;
        padding: 8px 12px; border-radius: 8px; cursor: pointer;
        border: 1.5px solid #dee2e6; background: #fff;
        transition: all .15s; font-size: .82rem; user-select: none;
        line-height: 1.3;
    }
    .depto-chip:hover { border-color: #003366; background: #f0f4ff; }
    .depto-chip:has(input:checked) {
        border-color: #003366; background: #e8efff;
        color: #003366; font-weight: 600;
    }
    .depto-chip input { display: none; }
    .depto-chip .chip-icon { font-size: .9rem; flex-shrink: 0; opacity: .6; }
    .depto-chip:has(input:checked) .chip-icon { opacity: 1; }

    /* ── VENCIMIENTO BADGES ── */
    .badge-vencido { background-color:#dc3545; color:#fff; font-size:.6rem; padding:2px 6px; border-radius:20px; }
    .badge-proximo { background-color:#fd7e14; color:#fff; font-size:.6rem; padding:2px 6px; border-radius:20px; }

    @media (max-width: 575.98px) {
        .header-actions { width: 100%; }
        .header-actions .btn { flex: 1 1 auto; justify-content: center; font-size: .82rem; padding: 6px 10px; }
    }
    @media (max-width: 575.98px) {
        .filter-bar-selects .col-12 { width: 100%; }
    }
    @media (max-width: 575.98px) {
        .epp-item { width: 50%; }
        .card-epp .card-body { padding: 0.6rem !important; }
        .epp-img-container { height: 130px !important; }
        .specs-box span { font-size: 0.6rem !important; }
        .card-epp h6 { font-size: .82rem !important; }
        .card-epp .text-primary { font-size: .68rem !important; }
        .card-epp p.text-muted { font-size: .72rem !important; min-height: 1.8rem !important; }
        .card-epp .btn { font-size: .72rem; padding: 3px 6px; }
        .card-epp .btn-light { padding: 2px 6px; }
    }
    @media (min-width: 576px) and (max-width: 767.98px) {
        .epp-img-container { height: 150px !important; }
    }
    @media (max-width: 767.98px) {
        .filter-strip { padding-bottom: 6px; }
        .filter-strip .btn { font-size: .78rem; padding: 4px 12px; }
    }
</style>

<div class="container-fluid py-2 px-3 px-md-4">

    {{-- ── HEADER ── --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="page-title fw-bold mb-0">Catálogo de Equipos de Protección Personal</h2>
            <p class="text-muted small mb-0">Registro y administración de EPP mediante carga de Excel o ingreso manual</p>
        </div>
        <div class="header-actions d-flex flex-wrap gap-2 justify-content-start justify-content-sm-end">
            <button type="button" class="btn btn-outline-danger shadow-sm d-flex align-items-center"
                    data-bs-toggle="modal" data-bs-target="#modalVaciarEpps">
                <i class="bi bi-trash3 me-1"></i>Vaciar Todo
            </button>
            <button type="button" class="btn btn-success shadow-sm d-flex align-items-center"
                    data-bs-toggle="modal" data-bs-target="#modalImportarEpp">
                <i class="bi bi-file-earmark-excel me-1"></i>Importar
            </button>
            <a href="{{ route('epps.create') }}" class="btn btn-primary shadow-sm d-flex align-items-center"
               style="background-color:#003366; border:none;">
                <i class="bi bi-plus fs-5 me-1"></i>Nuevo EPP
            </a>
        </div>
    </div>

    {{-- ── FILTER BAR ── --}}
    <div class="card border-0 shadow-sm p-3 mb-4">
        <div class="row g-3 align-items-center filter-bar-selects">
            <div class="col-12">
                <div class="filter-strip">
                    <span class="fw-bold text-muted small text-nowrap"><i class="bi bi-tag me-1"></i>Categoría:</span>
                    <button class="btn btn-sm btn-primary rounded-pill filter-btn px-3" data-filter="all">Todas</button>
                    @foreach($categorias as $cat)
                        <button class="btn btn-sm btn-outline-primary rounded-pill filter-btn px-3"
                                data-filter="cat-{{ $cat->id }}">{{ $cat->nombre }}</button>
                    @endforeach
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchEpp" class="form-control bg-light border-0"
                           placeholder="Buscar por código, nombre o detalles...">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <select id="deptoFilter" class="form-select">
                    <option value="all">— Filtrar por departamento —</option>
                    @foreach($departamentos as $depto)
                        <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <select id="vencimientoFilter" class="form-select">
                    <option value="all">— Filtrar por estado de vencimiento —</option>
                    <option value="vencido">⛔ Vencidos</option>
                    <option value="proximo">⚠️ Próximos a vencer (30 días)</option>
                    <option value="vigente">✅ Vigentes</option>
                    <option value="sin_fecha">— Sin fecha de vencimiento —</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ── EPP GRID ── --}}
    <div class="row g-3" id="eppGrid">
        @forelse($epps as $epp)
        @php
            $vencimientoReal   = null;
            $estadoVencimiento = 'sin_fecha';
            if ($epp->created_at && $epp->vida_util_meses) {
                $vencimientoReal = \Carbon\Carbon::parse($epp->created_at)->addMonths($epp->vida_util_meses);
                $hoy = \Carbon\Carbon::today();
                if ($vencimientoReal->lt($hoy)) {
                    $estadoVencimiento = 'vencido';
                } elseif ($hoy->diffInDays($vencimientoReal) <= 30) {
                    $estadoVencimiento = 'proximo';
                } else {
                    $estadoVencimiento = 'vigente';
                }
            }
            $deptoIds = $epp->departamentos && $epp->departamentos->count()
                ? ',' . $epp->departamentos->pluck('id')->implode(',') . ','
                : ',';

            $imagenUrl = null;
            if ($epp->imagen) {
                if (str_starts_with($epp->imagen, 'http://') || str_starts_with($epp->imagen, 'https://')) {
                    $imagenUrl = $epp->imagen;
                } else {
                    $imagenUrl = asset('storage/' . $epp->imagen);
                }
            }
        @endphp

        <div class="col-6 col-sm-6 col-lg-4 col-xl-3 epp-item cat-{{ $epp->categoria_id }}"
             data-nombre="{{ strtolower($epp->nombre) }}"
             data-descripcion="{{ strtolower($epp->descripcion ?? '') }}"
             data-codigo="{{ strtolower($epp->codigo_logistica ?? '') }}"
             data-depto="{{ $deptoIds }}"
             data-vencimiento="{{ $estadoVencimiento }}">

            <div class="card border-0 shadow-sm h-100 card-epp">
                <div class="position-relative">
                    <div class="epp-img-container d-flex align-items-center justify-content-center bg-light"
                         style="height:170px; border-bottom:1px solid #f0f0f0; overflow:hidden;">
                        @if($imagenUrl)
                            <img src="{{ $imagenUrl }}"
                                 class="img-fluid h-100 p-2" style="object-fit:contain;"
                                 onerror="this.parentElement.innerHTML='<div class=\'no-image-placeholder\'><i class=\'bi bi-shield-slash\'></i><span>Sin Imagen</span></div>'">
                        @else
                            <div class="no-image-placeholder">
                                <i class="bi bi-shield-slash"></i>
                                <span>Sin Imagen</span>
                            </div>
                        @endif
                    </div>
                    @if($estadoVencimiento === 'vencido')
                        <span class="badge-vencido position-absolute top-0 end-0 m-2" style="z-index:5;">VENCIDO</span>
                    @elseif($estadoVencimiento === 'proximo')
                        <span class="badge-proximo position-absolute top-0 end-0 m-2" style="z-index:5;">PRÓX. A VENCER</span>
                    @endif
                </div>

                <div class="card-body d-flex flex-column p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="me-2 min-w-0">
                            @php
                                $partes = explode('-', $epp->nombre);
                                $principal = trim($partes[0]);
                                $subtipoDetalle = isset($partes[1]) ? trim($partes[1]) : null;
                            @endphp
                            <h6 class="fw-bold mb-0 text-dark" style="font-size:.95rem; line-height:1.2;">{{ Str::upper($principal) }}</h6>
                            @if($subtipoDetalle)
                                <span class="text-primary fw-semibold" style="font-size:0.75rem;">{{ Str::upper($subtipoDetalle) }}</span>
                            @endif
                        </div>
                        <div class="dropdown flex-shrink-0">
                            <button class="btn btn-light btn-sm border-0" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><a class="dropdown-item" href="{{ route('epps.edit', $epp->id) }}">
                                    <i class="bi bi-pencil me-2 text-primary"></i>Editar</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button type="button" class="dropdown-item text-danger"
                                            data-bs-toggle="modal" data-bs-target="#modalEliminarEpp"
                                            data-epp-nombre="{{ $epp->nombre }}"
                                            data-epp-url="{{ route('epps.destroy', $epp->id) }}">
                                        <i class="bi bi-trash me-2"></i>Eliminar
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <p class="text-muted mb-3" style="font-size:0.8rem; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:2.4rem;">
                        {{ $epp->descripcion ?? 'Sin especificaciones del modelo.' }}
                    </p>

                    <div class="specs-box bg-light rounded-3 p-2 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1">
                            <span class="text-muted" style="font-size:0.65rem;"><i class="bi bi-barcode me-1"></i>CÓD. LOGÍSTICA:</span>
                            <span class="fw-bold text-dark" style="font-size:0.7rem;">{{ $epp->codigo_logistica ?? 'N/A' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1">
                            <span class="text-muted" style="font-size:0.65rem;"><i class="bi bi-clock-history me-1"></i>VIDA ÚTIL:</span>
                            <span class="fw-bold text-dark" style="font-size:0.7rem;">
                                {{ $epp->vida_util_meses >= 12 ? ($epp->vida_util_meses / 12).' Años' : $epp->vida_util_meses.' Meses' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1">
                            <span class="text-muted" style="font-size:0.65rem;"><i class="bi bi-calendar-plus me-1"></i>F. REGISTRO:</span>
                            <span class="fw-bold text-dark" style="font-size:0.7rem;">
                                {{ $epp->created_at ? \Carbon\Carbon::parse($epp->created_at)->format('d/m/Y') : 'N/A' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted" style="font-size:0.65rem;"><i class="bi bi-calendar-x me-1"></i>F. VENCIMIENTO:</span>
                            @if($vencimientoReal)
                                <span class="fw-bold" style="font-size:0.7rem; color:{{ $estadoVencimiento==='vencido'?'#dc3545':($estadoVencimiento==='proximo'?'#fd7e14':'#198754') }};">
                                    {{ $vencimientoReal->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="fw-bold text-muted" style="font-size:0.7rem;">N/A</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-auto">
                        <a href="{{ route('epps.show', $epp->id) }}" class="btn btn-outline-dark btn-sm w-100 py-1 fw-bold">
                            <i class="bi bi-info-circle me-1"></i>Ver Detalles
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5" id="emptyStateTotal">
            <i class="bi bi-box-seam display-1 text-muted opacity-25"></i>
            <p class="mt-3 text-muted">No se encontraron equipos de protección personal.</p>
        </div>
        @endforelse
    </div>

    <div id="emptyStateFilter" class="text-center py-5" style="display:none;">
        <i class="bi bi-funnel display-1 text-muted opacity-25"></i>
        <p class="mt-3 fw-semibold text-muted mb-1">Sin resultados para este filtro</p>
        <p class="text-muted small">No hay EPPs que coincidan con la selección actual.</p>
        <button class="btn btn-sm btn-outline-primary mt-1" id="clearFiltersBtn">
            <i class="bi bi-x-circle me-1"></i>Limpiar filtros
        </button>
    </div>

</div>

{{-- ════ MODALES ════ --}}

{{-- Eliminar EPP --}}
<div class="modal fade" id="modalEliminarEpp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
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
                    @csrf @method('DELETE')
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Sí, Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Vaciar Todo --}}
<div class="modal fade" id="modalVaciarEpps" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>¿Estás seguro?</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p>Esta acción <b>eliminará todos los registros de EPP</b> y no se puede deshacer.</p>
                <p class="text-muted small">Se borrarán stocks, imágenes y configuraciones de todos los equipos.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('epps.clearAll') }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">Sí, eliminar todo</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Importar Excel --}}
<div class="modal fade" id="modalImportarEpp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-file-earmark-excel me-2"></i>Importar desde Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('epps.import_excel') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4 text-center">
                    <i class="bi bi-cloud-arrow-up display-1 text-success opacity-50 mb-3"></i>
                    <p class="mb-3">Selecciona el archivo Excel (.xlsx o .csv) que contiene la matriz de EPPs.</p>
                    <input type="file" name="file" class="form-control mb-3" accept=".xlsx,.xls,.csv" required>
                    <div class="text-start">
                        <label for="fecha_registro_excel" class="form-label fw-bold small">Fecha de Registro (opcional)</label>
                        <input type="date" name="fecha_registro" id="fecha_registro_excel" class="form-control mb-1">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEliminar = document.getElementById('modalEliminarEpp');
    if (modalEliminar) {
        modalEliminar.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            document.getElementById('eppNameToDelete').textContent = `"${btn.getAttribute('data-epp-nombre')}"`;
            document.getElementById('deleteEppForm').action = btn.getAttribute('data-epp-url');
        });
    }

    const filterBtns        = document.querySelectorAll('.filter-btn');
    const deptoFilter       = document.getElementById('deptoFilter');
    const vencimientoFilter = document.getElementById('vencimientoFilter');
    const searchInput       = document.getElementById('searchEpp');
    const items             = document.querySelectorAll('.epp-item');

    function applyFilters() {
        const activeCat   = (document.querySelector('.filter-btn.btn-primary') ?? {}).dataset?.filter ?? 'all';
        const activeDepto = deptoFilter.value;
        const activeVenc  = vencimientoFilter.value;
        const searchText  = searchInput.value.toLowerCase().trim();

        items.forEach(item => {
            const matchesCat    = activeCat === 'all' || item.classList.contains(activeCat);
            const matchesDepto  = activeDepto === 'all' || item.dataset.depto.includes(',' + activeDepto + ',');
            const matchesVenc   = activeVenc === 'all' || item.dataset.vencimiento === activeVenc;
            const matchesSearch = !searchText
                || item.dataset.nombre.includes(searchText)
                || item.dataset.codigo.includes(searchText)
                || item.dataset.descripcion.includes(searchText);

            item.style.display = (matchesCat && matchesDepto && matchesVenc && matchesSearch) ? '' : 'none';
        });

        const anyVisible = [...items].some(i => i.style.display !== 'none');
        const emptyFilter = document.getElementById('emptyStateFilter');
        if (emptyFilter) emptyFilter.style.display = anyVisible ? 'none' : 'block';
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            filterBtns.forEach(b => { b.classList.remove('btn-primary'); b.classList.add('btn-outline-primary'); });
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary');
            applyFilters();
        });
    });

    deptoFilter.addEventListener('change', applyFilters);
    vencimientoFilter.addEventListener('change', applyFilters);
    searchInput.addEventListener('input', applyFilters);

    const clearBtn = document.getElementById('clearFiltersBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            filterBtns.forEach(b => { b.classList.remove('btn-primary'); b.classList.add('btn-outline-primary'); });
            document.querySelector('.filter-btn[data-filter="all"]').classList.replace('btn-outline-primary', 'btn-primary');
            deptoFilter.value       = 'all';
            vencimientoFilter.value = 'all';
            searchInput.value       = '';
            applyFilters();
        });
    }
});
</script>
@endsection