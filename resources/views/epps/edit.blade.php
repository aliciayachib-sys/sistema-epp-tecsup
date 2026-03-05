@extends('layouts.app')

@section('content')
<style>
    .input-group-text { border: 1px solid #dee2e6; }
    .form-control,
    .form-select  { border: 1px solid #dee2e6; }
    .form-control:focus,
    .form-select:focus { border-color: #003366; box-shadow: none; }
    .page-title { font-size: clamp(1.2rem, 4vw, 1.6rem); }
</style>

<div class="container-fluid py-2">

    {{-- ── HEADER ── --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="page-title fw-bold mb-0">Editar EPP</h2>
            <p class="text-muted small mb-0">Modifica los datos del equipo de protección personal.</p>
        </div>
        <a href="{{ route('epps.index') }}" class="btn btn-outline-secondary shadow-sm flex-shrink-0">
            <i class="bi bi-arrow-left me-1"></i>Volver al catálogo
        </a>
    </div>

    {{-- ── ERRORS ── --}}
    @if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-4">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-12 col-lg-9 col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 p-md-4 p-lg-5">
                    <form action="{{ route('epps.update', $epp->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Nombre + Categoría --}}
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-bold small">Nombre del EPP <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-box-seam"></i></span>
                                    <input type="text" name="nombre"
                                           class="form-control bg-light border-start-0"
                                           value="{{ old('nombre', $epp->nombre) }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-bold small">Categoría <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-tag"></i></span>
                                    <select name="categoria_id" class="form-select bg-light border-start-0" required>
                                        <option value="">— Selecciona una categoría —</option>
                                        @foreach($categorias as $cat)
                                            <option value="{{ $cat->id }}"
                                                {{ old('categoria_id', $epp->categoria_id) == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" name="tipo" value="{{ old('tipo', $epp->tipo) }}">
                            </div>
                        </div>

                        {{-- Descripción --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Descripción</label>
                            <textarea name="descripcion" class="form-control bg-light" rows="3">{{ old('descripcion', $epp->descripcion) }}</textarea>
                        </div>

                        {{-- Marca + Código --}}
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-bold small">Marca / Modelo</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-building"></i></span>
                                    <input type="text" name="marca_modelo"
                                           class="form-control bg-light border-start-0"
                                           value="{{ old('marca_modelo', $epp->marca_modelo) }}">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-bold small">Código de Logística</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-barcode"></i></span>
                                    <input type="text" name="codigo_logistica"
                                           class="form-control bg-light border-start-0"
                                           value="{{ old('codigo_logistica', $epp->codigo_logistica) }}">
                                </div>
                            </div>
                        </div>

                        {{-- Departamento + Vida útil --}}
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-bold small">Departamento</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-building"></i></span>
                                    <select name="departamento_id" class="form-select bg-light border-start-0">
                                        <option value="">— Selecciona un departamento —</option>
                                        @forelse($departamentos as $depto)
                                            <option value="{{ $depto->id }}"
                                                {{ old('departamento_id', $epp->departamento_id) == $depto->id ? 'selected' : '' }}>
                                                {{ $depto->nombre }}
                                            </option>
                                        @empty
                                            <option value="" disabled>No hay departamentos disponibles</option>
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-bold small">Vida útil (meses) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-calendar-event"></i></span>
                                    <input type="number" name="vida_util_meses"
                                           class="form-control bg-light border-start-0"
                                           value="{{ old('vida_util_meses', $epp->vida_util_meses) }}" required>
                                </div>
                            </div>
                        </div>

                        {{-- Frecuencia + Precio + Cantidad + Fecha ingreso --}}
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-sm-6 col-lg-3">
                                <label class="form-label fw-bold small">Frecuencia de Entrega</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-arrow-repeat"></i></span>
                                    <input type="text" name="frecuencia_entrega"
                                           class="form-control bg-light border-start-0"
                                           value="{{ old('frecuencia_entrega', $epp->frecuencia_entrega) }}">
                                </div>
                            </div>
                            <div class="col-6 col-sm-3 col-lg-3">
                                <label class="form-label fw-bold small">Precio (USD)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" name="precio"
                                           class="form-control bg-light border-start-0"
                                           placeholder="0.00" step="0.01"
                                           value="{{ old('precio', $epp->precio) }}">
                                </div>
                            </div>
                            <div class="col-6 col-sm-3 col-lg-3">
                                <label class="form-label fw-bold small">Cantidad</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-box"></i></span>
                                    <input type="number" name="cantidad"
                                           class="form-control bg-light border-start-0"
                                           value="{{ old('cantidad', $epp->cantidad) }}">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-3">
                                <label class="form-label fw-bold small">Fecha Ingreso al Almacén</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-calendar2-check"></i></span>
                                    <input type="date" name="fecha_ingreso_almacen"
                                           class="form-control bg-light border-start-0"
                                           value="{{ old('fecha_ingreso_almacen', $epp->fecha_ingreso_almacen ?? ($epp->created_at ? $epp->created_at->format('Y-m-d') : '')) }}">
                                </div>
                                <small class="text-muted" style="font-size:.72rem;">El vencimiento se calcula automáticamente.</small>
                            </div>
                        </div>

                        {{-- Ficha técnica + Imagen --}}
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-bold small">Ficha Técnica (PDF opcional)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-file-earmark-pdf"></i></span>
                                    <input type="file" name="ficha_tecnica"
                                           class="form-control bg-light border-start-0"
                                           accept=".pdf">
                                </div>
                                @if($epp->ficha_tecnica)
                                    <small class="text-success">
                                        <i class="bi bi-check-circle me-1"></i>Archivo actual:
                                        <a href="{{ asset('storage/' . $epp->ficha_tecnica) }}" target="_blank">Ver</a>
                                    </small>
                                @endif
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-bold small">Imagen del EPP</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-image"></i></span>
                                    <input type="file" name="imagen" id="image-input"
                                           class="form-control bg-light border-start-0"
                                           accept="image/*">
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size:.72rem;">Formatos: JPG, PNG, GIF (Máx: 2MB)</small>
                            </div>
                        </div>

                        {{-- New image preview --}}
                        <div id="preview-container" class="mb-3" style="display:none;">
                            <label class="form-label fw-bold small">Vista Previa (nueva imagen)</label>
                            <div class="text-center">
                                <img id="image-preview" src="" alt="Vista previa"
                                     class="img-fluid rounded border"
                                     style="max-width:280px; max-height:220px; object-fit:cover;">
                            </div>
                        </div>

                        {{-- Current image --}}
                        @if($epp->imagen)
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Imagen Actual</label>
                            <div class="text-center">
                                <img src="{{ asset('storage/' . $epp->imagen) }}"
                                     alt="Imagen actual"
                                     class="img-fluid rounded border"
                                     style="max-width:280px; max-height:220px; object-fit:contain;">
                                <small class="text-muted d-block mt-2">Sube una nueva imagen para reemplazarla</small>
                            </div>
                        </div>
                        @endif

                        {{-- Actions --}}
                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4 pt-2 border-top">
                            <a href="{{ route('epps.index') }}" class="btn btn-light order-sm-1">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary px-4 order-sm-2"
                                    style="background-color:#003366; border:none;">
                                <i class="bi bi-save me-1"></i>Guardar Cambios
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput       = document.getElementById('image-input');
    const previewContainer = document.getElementById('preview-container');
    const imagePreview     = document.getElementById('image-preview');

    if (imageInput) {
        imageInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    imagePreview.src = event.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
                imagePreview.src = '';
            }
        });
    }
});
</script>
@endsection