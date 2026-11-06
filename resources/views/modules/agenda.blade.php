@php
    $rol = session('rol', 'Administrador');
    $medicoId = $agendaData['medico_id'] ?? null;
    $fecha = $agendaData['fecha'] ?? date('Y-m-d');
    $medico = $agendaData['medico'] ?? null;
    $citasDia = $agendaData['citas_dia'] ?? collect();
    $slots = $agendaData['slots'] ?? [];
@endphp

<div class="row g-4">
    <!-- Barra Superior de Filtros de Agenda -->
    <div class="col-12">
        <div class="custom-card p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 border-bottom pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-info bg-opacity-10 text-info rounded-3">
                        <i class="bi bi-calendar2-range-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">CU11: Consultar Agenda Médica Diaria</h6>
                        <span class="text-muted small">Visualice los turnos ocupados y franjas libres del médico especialista en tiempo real</span>
                    </div>
                </div>

                @if($medico)
                <span class="badge bg-light text-primary border px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-person-badge me-1"></i>
                    Dr(a). {{ $medico->nombres }} {{ $medico->apellidos }}
                </span>
                @endif
            </div>

            <!-- Formulario de Consulta de Agenda -->
            <form action="{{ route('dashboard') }}" method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="mod" value="agenda">

                @if($rol !== 'Medico')
                <div class="col-md-5 col-lg-4">
                    <label class="form-label small fw-semibold text-secondary">Seleccionar Médico Especialista</label>
                    <select name="medico_id" class="form-select rounded-3" onchange="this.form.submit()">
                        @foreach($medicos as $m)
                            <option value="{{ $m->id }}" {{ $medicoId == $m->id ? 'selected' : '' }}>
                                Dr(a). {{ $m->nombres }} {{ $m->apellidos }} ({{ $m->especialidad ?? 'General' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-md-4 col-lg-3">
                    <label class="form-label small fw-semibold text-secondary">Fecha de Consulta</label>
                    <input type="date" name="fecha" class="form-control rounded-3" value="{{ $fecha }}" onchange="this.form.submit()">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <a href="{{ route('dashboard', ['mod' => 'agenda', 'medico_id' => $medicoId, 'fecha' => date('Y-m-d')]) }}"
                       class="btn btn-outline-primary rounded-3 w-100 py-2 fw-semibold {{ $fecha == date('Y-m-d') ? 'active' : '' }}">
                        <i class="bi bi-calendar-event me-1"></i> Hoy
                    </a>
                    <a href="{{ route('dashboard', ['mod' => 'agenda', 'medico_id' => $medicoId, 'fecha' => date('Y-m-d', strtotime('+1 day'))]) }}"
                       class="btn btn-outline-secondary rounded-3 w-100 py-2 fw-semibold {{ $fecha == date('Y-m-d', strtotime('+1 day')) ? 'active' : '' }}">
                        <i class="bi bi-calendar-plus me-1"></i> Mañana
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen de Ocupación del Día -->
    @php
        $totalSlots = count($slots);
        $ocupados = 0;
        foreach($slots as $s) {
            if(isset($citasDia[$s]) && $citasDia[$s]->estado !== 'Cancelada') {
                $ocupados++;
            }
        }
        $disponibles = $totalSlots - $ocupados;
        $porcentaje = $totalSlots > 0 ? round(($ocupados / $totalSlots) * 100) : 0;
    @endphp

    <div class="col-12">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="kpi-card">
                    <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Turnos Ocupados</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $ocupados }} / {{ $totalSlots }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kpi-card">
                    <div class="kpi-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-unlock-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Turnos Disponibles</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $disponibles }} Libres</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kpi-card">
                    <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Nivel de Ocupación</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $porcentaje }}%</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cuadrícula / Cronograma de Turnos Diarios -->
    <div class="col-12">
        <div class="custom-card p-4">
            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary"></i>
                Cronograma de Turnos para el {{ date('d/m/Y', strtotime($fecha)) }}
            </h6>

            <div class="row g-3">
                @foreach($slots as $hora)
                    @php
                        $cita = $citasDia[$hora] ?? null;
                        $esOcupado = $cita && $cita->estado !== 'Cancelada';
                    @endphp

                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="p-3 rounded-4 border {{ $esOcupado ? 'bg-white border-primary border-opacity-25 shadow-sm' : 'bg-light border-dashed' }}" style="min-height: 140px;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge {{ $esOcupado ? 'bg-primary' : 'bg-secondary' }} px-3 py-2 rounded-pill fw-semibold">
                                    <i class="bi bi-clock me-1"></i> {{ $hora }} - {{ date('H:i', strtotime($hora . ' +30 minutes')) }}
                                </span>

                                @if($esOcupado)
                                    @if($cita->estado === 'Programada')
                                        <span class="badge badge-status badge-programada">Programada</span>
                                    @elseif($cita->estado === 'En Espera')
                                        <span class="badge bg-warning text-dark px-2 py-1 rounded-pill fw-bold">
                                            <i class="bi bi-hourglass-split me-1"></i> En Espera
                                        </span>
                                    @elseif($cita->estado === 'Atendida')
                                        <span class="badge badge-status badge-atendida">Atendida</span>
                                    @elseif($cita->estado === 'No Asistió')
                                        <span class="badge bg-secondary text-white px-2 py-1 rounded-pill fw-bold">No Asistió</span>
                                    @endif
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill">
                                        <i class="bi bi-check-circle me-1"></i> Libre
                                    </span>
                                @endif
                            </div>

                            @if($esOcupado)
                                <div class="mb-2">
                                    <div class="fw-bold text-dark text-truncate">{{ $cita->paciente_nombre }}</div>
                                    <div class="text-muted small">CI: {{ $cita->paciente_ci }} | Tel: {{ $cita->paciente_telefono }}</div>
                                    <div class="text-secondary small fst-italic mt-1 text-truncate">
                                        <i class="bi bi-chat-left-text me-1"></i>{{ $cita->motivo_consulta ?? 'Sin motivo específico' }}
                                    </div>
                                </div>

                                <!-- Acciones de Control de Asistencia y Gestión -->
                                <div class="d-flex flex-wrap gap-1 mt-auto pt-2 border-top">
                                    @if($cita->estado !== 'Atendida')
                                        <!-- Marcar En Espera (CU10) -->
                                        @if($cita->estado === 'Programada')
                                        <form action="{{ route('citas.controlarAsistencia') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="cita_id" value="{{ $cita->id }}">
                                            <input type="hidden" name="estado" value="En Espera">
                                            <button type="submit" class="btn btn-xs btn-outline-warning text-dark rounded-pill py-1 px-2 small fw-semibold" title="Marcar llegada a recepción">
                                                <i class="bi bi-box-arrow-in-right"></i> En Espera
                                            </button>
                                        </form>
                                        @endif

                                        <!-- Marcar Atendida (CU10) -->
                                        <form action="{{ route('citas.controlarAsistencia') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="cita_id" value="{{ $cita->id }}">
                                            <input type="hidden" name="estado" value="Atendida">
                                            <button type="submit" class="btn btn-xs btn-outline-success rounded-pill py-1 px-2 small fw-semibold" title="Completar consulta médica">
                                                <i class="bi bi-check2-circle"></i> Atender
                                            </button>
                                        </form>

                                        <!-- Marcar No Asistió (CU10) -->
                                        <form action="{{ route('citas.controlarAsistencia') }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Registrar que el paciente no asistió a su turno?');">
                                            @csrf
                                            <input type="hidden" name="cita_id" value="{{ $cita->id }}">
                                            <input type="hidden" name="estado" value="No Asistio">
                                            <button type="submit" class="btn btn-xs btn-outline-secondary rounded-pill py-1 px-2 small" title="Registrar inasistencia">
                                                <i class="bi bi-person-x"></i> Ausente
                                            </button>
                                        </form>

                                        <!-- Reprogramar (CU08) -->
                                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-1 px-2 small"
                                                onclick="abrirModalReprogramar('{{ $cita->id }}', '{{ $cita->codigo }}', '{{ $cita->fecha_cita }}', '{{ substr($cita->hora_inicio, 0, 5) }}')">
                                            <i class="bi bi-calendar2-range"></i> Reprogramar
                                        </button>
                                    @else
                                        <span class="text-success small fw-medium">
                                            <i class="bi bi-check-all me-1"></i> Consulta finalizada con éxito
                                        </span>
                                    @endif
                                </div>
                            @else
                                <div class="text-muted small py-2">
                                    <i class="bi bi-info-circle me-1"></i> Horario disponible para reserva médica
                                </div>
                                @if($rol !== 'Medico' && $rol !== 'Paciente')
                                <div class="pt-2 border-top">
                                    <a href="{{ route('dashboard', ['mod' => 'citas']) }}" class="btn btn-sm btn-outline-primary w-100 rounded-pill py-1 small">
                                        <i class="bi bi-plus-circle me-1"></i> Agendar Cita Aquí
                                    </a>
                                </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal para Reprogramar Cita (CU08) -->
<div class="modal fade" id="modalReprogramarCita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('citas.reprogramar') }}" method="POST">
                @csrf
                <input type="hidden" name="cita_id" id="modal_reprog_cita_id">

                <div class="modal-header">
                    <h6 class="modal-title fw-bold text-primary">
                        <i class="bi bi-calendar2-range-fill me-2"></i> CU08: Reprogramar Cita <span id="modal_reprog_codigo"></span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary small mb-3">
                        Seleccione la nueva fecha y hora para la cita. El sistema verificará automáticamente la disponibilidad del médico (RF07).
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nueva Fecha de Atención *</label>
                        <input type="date" name="nueva_fecha" id="modal_reprog_fecha" class="form-control rounded-3"
                               min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nuevo Horario de Inicio *</label>
                        <select name="nueva_hora" id="modal_reprog_hora" class="form-select rounded-3" required>
                            <option value="08:00">08:00 AM</option>
                            <option value="08:30">08:30 AM</option>
                            <option value="09:00">09:00 AM</option>
                            <option value="09:30">09:30 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="10:30">10:30 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="11:30">11:30 AM</option>
                            <option value="14:00">02:00 PM</option>
                            <option value="14:30">02:30 PM</option>
                            <option value="15:00">03:00 PM</option>
                            <option value="15:30">03:30 PM</option>
                            <option value="16:00">04:00 PM</option>
                            <option value="16:30">04:30 PM</option>
                            <option value="17:00">05:00 PM</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Confirmar Reprogramación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function abrirModalReprogramar(id, codigo, fecha, hora) {
        document.getElementById('modal_reprog_cita_id').value = id;
        document.getElementById('modal_reprog_codigo').textContent = codigo;
        document.getElementById('modal_reprog_fecha').value = fecha;
        if(hora) {
            document.getElementById('modal_reprog_hora').value = hora;
        }
        var modal = new bootstrap.Modal(document.getElementById('modalReprogramarCita'));
        modal.show();
    }
</script>
