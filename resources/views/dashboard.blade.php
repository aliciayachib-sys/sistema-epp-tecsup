@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- ENCABEZADO PRINCIPAL -->
    <div class="mb-4 text-center text-md-start">
        <h1 class="fw-bold mb-1" style="color: #000;">Control y Trazabilidad de EPP</h1>
        <p class="text-muted">Tecsup Norte - Centro de Seguridad</p>
    </div>

    <!-- TARJETAS KPI PRINCIPALES -->
    <div class="row mb-4">
        <!-- Stock Disponible -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 5px solid #28a745;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 small">Stock Disponible</p>
                            <h3 class="fw-bold" style="color: #28a745;">{{ number_format($stockDisponible) }}</h3>
                            <small class="text-muted">En almacén</small>
                        </div>
                        <div style="font-size: 2rem; color: #28a745; opacity: 0.3;">
                            <i class="bi bi-box2-heart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- EPP Entregados -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 5px solid #003366;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 small">EPP Entregados</p>
                            <h3 class="fw-bold" style="color: #003366;">{{ number_format($eppEntregados) }}</h3>
                            <small class="text-muted">Asignados a personal</small>
                        </div>
                        <div style="font-size: 2rem; color: #003366; opacity: 0.3;">
                            <i class="bi bi-hand-thumbs-up"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- EPP Próximos a Vencer -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 5px solid #ffc107;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 small">Próximos a Vencer</p>
                            <h3 class="fw-bold" style="color: #ffc107;">{{ number_format($proximosVencer) }}</h3>
                            <small class="text-muted">Renovaciones pendientes</small>
                        </div>
                        <div style="font-size: 2rem; color: #ffc107; opacity: 0.3;">
                            <i class="bi bi-exclamation-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- EPP Deteriorados/Baja -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 5px solid #dc3545;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 small">Atención Requerida</p>
                            <h3 class="fw-bold" style="color: #dc3545;">{{ number_format($vencidos + $deteriorados) }}</h3>
                            <small class="text-muted">Vencidos (Asig.) + Baja</small>
                        </div>
                        <div style="font-size: 2rem; color: #dc3545; opacity: 0.3;">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GRÁFICAS PRINCIPALES -->
    <div class="row mb-4">
        <!-- Gráfica 1: EPP por Estado -->
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">📊 EPP por Estado</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartEstado" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfica 2: EPP por Departamento -->
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">📊 EPP por Departamento</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartDepartamento" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfica 3: Renovaciones por Mes -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">📊 Proyección de Renovaciones (Próximos 12 Meses)</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartRenovaciones" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ALERTAS IMPORTANTES - AHORA DEBAJO DE GRÁFICAS -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-bell-fill" style="color: #dc3545;"></i> Alertas Críticas
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        {{-- El bloque de alertas de vencidos se eliminó porque $alertasVencidos siempre está vacío según DashboardController --}}

                        @forelse($alertasStockCritico as $alerta)
                        <!-- Alerta: Stock Crítico -->
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-warning border-0 mb-2" role="alert">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>⚡ Stock Crítico</strong>
                                        <br>
                                        <small>{{ $alerta->nombre }} - {{ $alerta->stock }} unidades disponibles</small>
                                    </div>
                                    <button class="btn btn-sm btn-outline-warning">Ver detalle</button>
                                </div>
                            </div>
                        </div>
                        @empty
                        @endforelse

                        @if(count($alertasVencidos) > 0 || count($alertasStockCritico) > 0)
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-warning border-0 mb-0" role="alert">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>📅 {{ number_format($proximosVencer) }} Renovaciones en 30 días</strong>
                                        <br>
                                        <small>Requieren renovación próximamente</small>
                                    </div>
                                    <button class="btn btn-sm btn-outline-warning">Ver detalle</button>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="col-12">
                            <div class="alert alert-info border-0" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i> <strong>¡Todo en orden!</strong> No hay alertas críticas en este momento.
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLAS RESUMEN -->
    <div class="row">
        <!-- Últimas Entregas -->
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">🚚 Últimas Entregas</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Usuario</th>
                                    <th>EPP</th>
                                    <th>Fecha</th>
                                    <th>Área</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ultimasEntregas as $entrega)
                                <tr>
                                    <td><strong>{{ $entrega->personal->nombre_completo ?? 'N/A' }}</strong></td>
                                    <td>{{ $entrega->epp->nombre ?? 'N/A' }}</td>
                                    <td>{{ optional($entrega->fecha_entrega ?? $entrega->created_at)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $entrega->personal->departamento->nombre ?? $entrega->personal->carrera ?? 'Sin área' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Sin registros</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- EPP Datos de Baja -->
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">🗑️ EPP Datos de Baja</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>EPP</th>
                                    <th>Cantidad</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($eppBaja as $epp)
                                <tr>
                                    <td><strong>{{ $epp->nombre }}</strong></td>
                                    <td><span class="badge bg-danger">{{ $epp->deteriorado ?? $epp->cantidad }}</span></td>
                                    <td>
                                        @if($epp->deteriorado > 0)
                                            <span class="badge bg-warning text-dark">Deteriorado</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($epp->estado) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $epp->updated_at->format('d/m/Y') ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Sin registros</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CHART.JS LIBRARY -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

<script>
    // Datos reales desde el servidor
    const estadisticas = @json($estadisticasEstado);
    const departamentosData = @json($departamentosData);
    const renovacionesData = @json($renovacionesPorMes);

    // Gráfica 1: EPP por Estado (Dona)
    const ctxEstado = document.getElementById('chartEstado').getContext('2d');
    new Chart(ctxEstado, {
        type: 'doughnut',
        data: {
            labels: ['En Almacén', 'Entregados (Vigentes)', 'Por Vencer (Asignados)', 'Vencidos (Asignados)', 'Baja / Deteriorados'],
            datasets: [{
                data: [
                    estadisticas.enAlmacen,
                    estadisticas.entregados,
                    estadisticas.porVencer,
                    estadisticas.vencidos,
                    estadisticas.deteriorados
                ],
                backgroundColor: [
                    '#28a745',
                    '#003366',
                    '#ffc107',
                    '#dc3545',
                    '#6c757d'
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Gráfica 2: EPP por Departamento (Barras)
    const ctxDepartamento = document.getElementById('chartDepartamento').getContext('2d');
    const departamentoLabels = departamentosData.map(d => d.nombre);
    const departamentoCantidades = departamentosData.map(d => d.cantidad);
    
    new Chart(ctxDepartamento, {
        type: 'bar',
        data: {
            labels: departamentoLabels,
            datasets: [{
                label: 'Total EPP Asignados',
                data: departamentoCantidades,
                backgroundColor: [
                    '#003366',
                    '#0056b3',
                    '#0066cc',
                    '#1085d0',
                    '#5ba3e0'
                ],
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Gráfica 3: Renovaciones por Mes (Línea)
    const ctxRenovaciones = document.getElementById('chartRenovaciones').getContext('2d');
    const meses = renovacionesData.map(r => r.mes);
    const cantidades = renovacionesData.map(r => r.cantidad);
    
    new Chart(ctxRenovaciones, {
        type: 'line',
        data: {
            labels: meses,
            datasets: [{
                label: 'Renovaciones Necesarias',
                data: cantidades,
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#ffc107',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<style>
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .badge-role {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection
