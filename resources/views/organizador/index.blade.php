@extends('layouts.app')

@section('content')
<style>
    .pool-container {
        background: #f8f9fa;
        border-radius: 24px;
        border: 2px dashed #dee2e6;
        min-height: 200px;
    }
    .docente-item {
        transition: 0.2s;
        cursor: pointer;
        border-radius: 15px;
        border: 1px solid #eee;
    }
    .docente-item:hover { background: #eef2ff; border-color: #003a70; }
    .docente-checkbox:checked + .docente-card { background: #003a70; color: white; }

    .depto-target {
        border-radius: 20px;
        transition: 0.3s;
        border: 2px solid transparent;
    }
    .depto-target.active-drop {
        border-color: #003a70;
        background: #f0f7ff;
        transform: scale(1.02);
    }

    /* Scroll en desktop, expandible en móvil */
    .pool-scroll {
        max-height: 600px;
        overflow-y: auto;
    }

    @media (max-width: 991.98px) {
        .pool-scroll {
            max-height: 300px;
        }
    }

    @media (max-width: 575.98px) {
        .pool-scroll {
            max-height: 240px;
        }
        /* Botones full-width en móvil */
        .btn-volver,
        .btn-transfer {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="container-fluid px-3 px-md-4 py-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-0 fs-4">Organizador Maestro</h2>
            <p class="text-muted mb-0 small">Selecciona docentes y elige su destino</p>
        </div>
        <a href="{{ route('departamentos.index') }}"
           class="btn btn-light rounded-pill px-4 btn-volver flex-shrink-0">
            Volver al Panel
        </a>
    </div>

    <div class="row g-4">

        <!-- Panel Docentes Libres -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 24px;">
                <div class="d-flex justify-content-between align-items-center px-2 mb-3">
                    <h5 class="fw-bold mb-0">Docentes Libres</h5>
                    <span class="badge bg-secondary rounded-pill" id="contador-seleccion">0 seleccionados</span>
                </div>
                <div class="pool-container pool-scroll p-2">
                    @forelse($sinAsignar as $docente)
                        <label class="d-block mb-2 w-100">
                            <input type="checkbox" class="docente-checkbox d-none" value="{{ $docente->id }}">
                            <div class="docente-item docente-card p-3 bg-white shadow-sm">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3 text-primary flex-shrink-0">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <span class="fw-bold text-truncate">{{ $docente->nombre_completo }}</span>
                                </div>
                            </div>
                        </label>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-2 d-block mb-2 opacity-25"></i>
                            No hay docentes sin asignar.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Panel Departamentos -->
        <div class="col-12 col-lg-8">
            <div class="row g-3" id="contenedor-deptos">
                @foreach($departamentos as $depto)
                <div class="col-12 col-sm-6 col-xl-6">
                    <div class="card depto-target shadow-sm h-100 p-3 p-sm-4 bg-white" data-id="{{ $depto->id }}">
                        <div class="text-center mb-3">
                            <h5 class="fw-bold text-dark mb-2 fs-6 fs-sm-5">{{ $depto->nombre }}</h5>
                            <span class="badge bg-soft-primary text-primary border border-primary border-opacity-25 rounded-pill px-3">
                                {{ $depto->personals_count }} Docentes
                            </span>
                        </div>

                        <button onclick="asignarMasivo({{ $depto->id }})"
                                class="btn btn-primary rounded-pill w-100 py-2 btn-transfer d-none shadow-sm mt-auto">
                            Mover Seleccionados <i class="bi bi-chevron-right ms-2"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    // Actualizar contador y botones al seleccionar docentes
    document.addEventListener('change', function () {
        const seleccionados = document.querySelectorAll('.docente-checkbox:checked').length;
        const botones = document.querySelectorAll('.btn-transfer');
        const contador = document.getElementById('contador-seleccion');

        contador.textContent = seleccionados + ' seleccionado' + (seleccionados !== 1 ? 's' : '');
        contador.className = seleccionados > 0
            ? 'badge bg-primary rounded-pill'
            : 'badge bg-secondary rounded-pill';

        botones.forEach(btn => {
            if (seleccionados > 0) {
                btn.classList.remove('d-none');
                btn.closest('.depto-target').classList.add('active-drop');
            } else {
                btn.classList.add('d-none');
                btn.closest('.depto-target').classList.remove('active-drop');
            }
        });
    });

    function asignarMasivo(deptoId) {
        const ids = Array.from(document.querySelectorAll('.docente-checkbox:checked')).map(cb => cb.value);
        if (ids.length === 0) return;

        fetch("{{ route('organizador.asignar') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ docente_ids: ids, departamento_id: deptoId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) location.reload();
        });
    }
</script>
@endpush
@endsection