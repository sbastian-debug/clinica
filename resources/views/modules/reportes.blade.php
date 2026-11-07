@php
    $fechaDesde = $reportesData['fecha_desde'] ?? date('Y-m-01');
    $fechaHasta = $reportesData['fecha_hasta'] ?? date('Y-m-d');
    $total = $reportesData['total'] ?? 0;
    $atendidas = $reportesData['atendidas'] ?? 0;
    $canceladas = $reportesData['canceladas'] ?? 0;
    $ausentes = $reportesData['ausentes'] ?? 0;
    $pendientes = $reportesData['pendientes'] ?? 0;
    $porEspecialidad = $reportesData['por_especialidad'] ?? collect();
    $porMedico = $reportesData['por_medico'] ?? collect();
    $citasReporte = $reportesData['citas'] ?? collect();

    $tasaEfectividad = $total > 0 ? round(($atendidas / $total) * 100, 1) : 0;
@endphp

<div class="row g-4">
    <!-- Barra Superior de Filtrado de Fechas -->
    <div class="col-12 d-print-none">
        <div class="custom-card p-3 p-sm-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 border-bottom pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-success bg-opacity-10 text-success rounded-3">
                        <i class="bi bi-file-earmark-bar-graph-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">{{ session('rol') === 'Administrador' ? 'CU13: ' : '' }}Generar Reportes Administrativos y Estadísticos</h6>
                        <span class="text-muted small">Filtre por rango de fechas para evaluar productividad médica, atención y efectividad</span>
                    </div>
                </div>

                <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-printer-fill"></i>
                    <span class="fw-semibold">Imprimir / Guardar PDF</span>
                </button>
            </div>

            <form action="{{ route('dashboard') }}" method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="mod" value="reportes">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Fecha Inicial (Desde) *</label>
                    <input type="date" name="fecha_desde" class="form-control rounded-3" value="{{ $fechaDesde }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Fecha Final (Hasta) *</label>
                    <input type="date" name="fecha_hasta" class="form-control rounded-3" value="{{ $fechaHasta }}" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-primary rounded-3 w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-funnel-fill"></i>
                        <span>Generar Reporte</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Encabezado Institucional (Visible en Impresión) -->
    <div class="col-12 d-none d-print-block">
        <div class="text-center pb-3 border-bottom mb-4">
            <h3 class="fw-bold text-dark mb-1">CLÍNICA VIDA SALUD</h3>
            <p class="text-muted small mb-1">Informe de Gestión Clínica y Productividad Asistencial</p>
            <div class="small fw-semibold text-secondary">
                Período auditado: Del {{ date('d/m/Y', strtotime($fechaDesde)) }} al {{ date('d/m/Y', strtotime($fechaHasta)) }} | Generado el {{ date('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    <!-- Tarjetas de Métricas Ejecutivas del Reporte -->
    <div class="col-12">
        <div class="row g-2 g-sm-3">
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-calendar2-range"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Total Citas en Período</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ $total }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="kpi-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Consultas Atendidas</div>
                        <h3 class="fw-bold mb-0 text-success">{{ $atendidas }}</h3>
                        <span class="badge bg-success bg-opacity-10 text-success small">{{ $tasaEfectividad }}% efectividad</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="kpi-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Citas Canceladas</div>
                        <h3 class="fw-bold mb-0 text-danger">{{ $canceladas }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="kpi-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="bi bi-person-x"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Inasistencias (No Asistió)</div>
                        <h3 class="fw-bold mb-0 text-secondary">{{ $ausentes }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos / Distribución por Especialidad y por Médico -->
    <div class="col-12 col-lg-6">
        <div class="custom-card p-4 h-100">
            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-diagram-3-fill text-primary"></i>
                Demanda por Especialidad Médica
            </h6>
            @if($porEspecialidad->count() > 0)
                <div class="d-flex flex-column gap-3">
                    @foreach($porEspecialidad as $especialidad => $cantidad)
                        @php $pct = $total > 0 ? round(($cantidad / $total) * 100) : 0; @endphp
                        <div>
                            <div class="d-flex justify-content-between small fw-semibold text-secondary mb-1">
                                <span>{{ $especialidad }}</span>
                                <span class="text-dark">{{ $cantidad }} citas ({{ $pct }}%)</span>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 6px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $pct }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-muted small py-4 text-center">No hay citas en este período.</div>
            @endif
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="custom-card p-4 h-100">
            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-person-badge-fill text-info"></i>
                Productividad por Médico Especialista
            </h6>
            @if($porMedico->count() > 0)
                <div class="d-flex flex-column gap-3">
                    @foreach($porMedico as $medico => $cantidad)
                        @php $pctMed = $total > 0 ? round(($cantidad / $total) * 100) : 0; @endphp
                        <div>
                            <div class="d-flex justify-content-between small fw-semibold text-secondary mb-1">
                                <span>{{ $medico }}</span>
                                <span class="text-dark">{{ $cantidad }} consultas ({{ $pctMed }}%)</span>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 6px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $pctMed }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-muted small py-4 text-center">No hay citas en este período.</div>
            @endif
        </div>
    </div>

    <!-- Tabla Detallada para Auditoría de Citas en el Período -->
    <div class="col-12">
        <div class="custom-card">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-1 text-dark">Detalle de Consultas en el Período Auditado</h6>
                    <span class="text-muted small">Auditoría administrativa de {{ $citasReporte->count() }} citas</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Especialidad</th>
                            <th>Fecha</th>
                            <th>Horario</th>
                            <th class="text-end">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($citasReporte as $cr)
                            <tr>
                                <td><span class="badge bg-light text-primary border fw-bold">{{ $cr->codigo }}</span></td>
                                <td><span class="fw-semibold text-dark">{{ $cr->paciente_nombre }}</span></td>
                                <td>{{ $cr->medico_nombre }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $cr->especialidad_nombre }}</span></td>
                                <td>{{ date('d/m/Y', strtotime($cr->fecha_cita)) }}</td>
                                <td>{{ substr($cr->hora_inicio, 0, 5) }} - {{ substr($cr->hora_fin, 0, 5) }}</td>
                                <td class="text-end">
                                    @if($cr->estado === 'Atendida')
                                        <span class="badge badge-status badge-atendida">Atendida</span>
                                    @elseif($cr->estado === 'Cancelada')
                                        <span class="badge badge-status badge-cancelada">Cancelada</span>
                                    @elseif($cr->estado === 'No Asistió')
                                        <span class="badge bg-secondary text-white px-2 py-1 rounded-pill small fw-bold">No Asistió</span>
                                    @elseif($cr->estado === 'En Espera')
                                        <span class="badge bg-warning text-dark px-2 py-1 rounded-pill small fw-bold">En Espera</span>
                                    @else
                                        <span class="badge badge-status badge-programada">Programada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    No se registraron citas dentro del rango de fechas especificado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
