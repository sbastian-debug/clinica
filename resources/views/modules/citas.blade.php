@php
    $rol = session('rol', 'Administrador');
@endphp

<div class="row g-4">
    <!-- Formulario para Registrar Nueva Cita (Para Paciente, Recepcionista y Administrador) -->
    @if($rol !== 'Medico')
    <div class="col-12">
        <div class="custom-card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3">
                        <i class="bi bi-calendar-plus fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            {{ $rol === 'Paciente' ? 'Solicitar Cita Médica (Auto-reserva)' : 'Programar Nueva Cita Médica (CU06 / RF06)' }}
                        </h6>
                        <span class="text-muted small">
                            Validación de disponibilidad en tiempo real (RF07) y regla de no agendamiento en fechas pasadas (RN06)
                        </span>
                    </div>
                </div>
            </div>

            @if(session('error_cita'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>{{ session('error_cita') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success_cita'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('success_cita') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('citas.store') }}" method="POST" class="row g-3">
                @csrf

                @if($rol === 'Paciente')
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label small fw-semibold text-secondary">Paciente Solicitante</label>
                        <input type="text" class="form-control rounded-3 bg-light" value="{{ session('nombre_completo', session('username')) }}" readonly>
                        <input type="hidden" name="paciente_id" value="{{ session('paciente_id') }}">
                    </div>
                @else
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label small fw-semibold text-secondary">Paciente *</label>
                        <select name="paciente_id" class="form-select rounded-3" required>
                            <option value="">-- Seleccionar Paciente --</option>
                            @foreach($pacientes as $p)
                                <option value="{{ $p->id }}">{{ $p->codigo }} | {{ $p->nombres }} {{ $p->apellidos }} (CI: {{ $p->ci }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-4 col-lg-3">
                    <label class="form-label small fw-semibold text-secondary">Especialidad Requerida *</label>
                    <select name="especialidad_id" id="selectEspecialidad" class="form-select rounded-3" required>
                        <option value="">-- Seleccionar Especialidad --</option>
                        @foreach($especialidades as $e)
                            <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 col-lg-3">
                    <label class="form-label small fw-semibold text-secondary">Médico Especialista *</label>
                    <select name="medico_id" id="selectMedico" class="form-select rounded-3" required>
                        <option value="">-- Seleccionar Médico --</option>
                        @foreach($medicos as $m)
                            <option value="{{ $m->id }}" data-especialidad="{{ $m->especialidad_id }}">
                                Dr(a). {{ $m->nombres }} {{ $m->apellidos }} ({{ $m->especialidad ?? 'General' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 col-lg-3">
                    <label class="form-label small fw-semibold text-secondary">Fecha de Cita *</label>
                    <input type="date" name="fecha_cita" class="form-control rounded-3"
                           value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required>
                </div>

                <div class="col-md-4 col-lg-3">
                    <label class="form-label small fw-semibold text-secondary">Horario de Inicio (Bloques 30m) *</label>
                    <select name="hora_inicio" class="form-select rounded-3" required>
                        <option value="08:00">08:00 AM</option>
                        <option value="08:30">08:30 AM</option>
                        <option value="09:00" selected>09:00 AM</option>
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

                <div class="col-md-8 col-lg-6">
                    <label class="form-label small fw-semibold text-secondary">Motivo de la Consulta</label>
                    <input type="text" name="motivo_consulta" class="form-control rounded-3"
                           placeholder="Ej. Chequeo preventivo de rutina, dolor agudo, control...">
                </div>

                <div class="col-md-4 col-lg-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-calendar-check-fill"></i>
                        <span>{{ $rol === 'Paciente' ? 'Confirmar Mi Cita' : 'Confirmar Cita' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Listado y Gestión de Citas Médicas (CU07, CU08, CU09, CU10) -->
    <div class="col-12">
        <div class="custom-card">
            <div class="p-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h6 class="fw-bold mb-1 text-dark">
                        @if($rol === 'Paciente')
                            Mis Citas Médicas y Consultas
                        @elseif($rol === 'Medico')
                            Agenda de Pacientes y Consultas del Médico
                        @else
                            Gestión de Citas Médicas (CU07 / CU08 / CU09 / CU10)
                        @endif
                    </h6>
                    <span class="text-muted small">
                        Controle el estado, asistencia en sala de espera, reprogramaciones y cancelaciones
                    </span>
                </div>
                <div class="search-box" style="width: 320px;">
                    <i class="bi bi-search"></i>
                    <input type="text" id="buscarCitas" class="form-control form-control-sm" placeholder="Buscar por código, médico o fecha...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaCitas">
                    <thead>
                        <tr>
                            <th>Código</th>
                            @if($rol !== 'Paciente')
                                <th>Paciente</th>
                            @endif
                            <th>Especialidad</th>
                            @if($rol !== 'Medico')
                                <th>Médico Asignado</th>
                            @endif
                            <th>Fecha</th>
                            <th>Horario</th>
                            <th>Motivo</th>
                            <th>Estado (CU10)</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($citas as $c)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-primary border fw-bold px-2 py-1">
                                        {{ $c->codigo }}
                                    </span>
                                </td>
                                @if($rol !== 'Paciente')
                                <td>
                                    <div class="fw-semibold text-dark">{{ $c->paciente_nombre }}</div>
                                    <span class="text-muted small">CI: {{ $c->paciente_ci }}</span>
                                </td>
                                @endif
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                                        {{ $c->especialidad_nombre }}
                                    </span>
                                </td>
                                @if($rol !== 'Medico')
                                <td>
                                    <div class="fw-medium text-dark">{{ $c->medico_nombre }}</div>
                                </td>
                                @endif
                                <td>
                                    <div class="fw-medium text-dark">{{ date('d/m/Y', strtotime($c->fecha_cita)) }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-clock me-1 text-secondary"></i>
                                        {{ substr($c->hora_inicio, 0, 5) }} - {{ substr($c->hora_fin, 0, 5) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small text-truncate d-inline-block" style="max-width: 140px;" title="{{ $c->motivo_consulta }}">
                                        {{ $c->motivo_consulta ?? 'Consulta general' }}
                                    </span>
                                </td>
                                <td>
                                    @if($c->estado === 'Programada')
                                        <span class="badge badge-status badge-programada">
                                            <i class="bi bi-hourglass-split me-1"></i> Programada
                                        </span>
                                    @elseif($c->estado === 'En Espera')
                                        <span class="badge bg-warning text-dark px-2 py-1 rounded-pill fw-bold">
                                            <i class="bi bi-clock-history me-1"></i> En Espera
                                        </span>
                                    @elseif($c->estado === 'Atendida')
                                        <span class="badge badge-status badge-atendida">
                                            <i class="bi bi-check2-circle me-1"></i> Atendida
                                        </span>
                                    @elseif($c->estado === 'No Asistió')
                                        <span class="badge bg-secondary text-white px-2 py-1 rounded-pill fw-bold">
                                            <i class="bi bi-person-x me-1"></i> No Asistió
                                        </span>
                                    @elseif($c->estado === 'Cancelada')
                                        <span class="badge badge-status badge-cancelada" title="Motivo: {{ $c->motivo_cancelacion }}">
                                            <i class="bi bi-x-circle me-1"></i> Cancelada
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        @if($c->estado === 'Programada' || $c->estado === 'En Espera')
                                            <!-- CU10: Registrar Llegada a Sala de Espera -->
                                            @if($c->estado === 'Programada' && $rol !== 'Paciente')
                                            <form action="{{ route('citas.controlarAsistencia') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="cita_id" value="{{ $c->id }}">
                                                <input type="hidden" name="estado" value="En Espera">
                                                <button type="submit" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2" title="Marcar en Sala de Espera (CU10)">
                                                    <i class="bi bi-box-arrow-in-right"></i> Llegó
                                                </button>
                                            </form>
                                            @endif

                                            <!-- CU10: Marcar Atendida (Médico, Admin y Recepción) -->
                                            @if($rol !== 'Paciente')
                                            <form action="{{ route('citas.controlarAsistencia') }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Confirmar que la consulta médica ha concluido exitosamente?');">
                                                @csrf
                                                <input type="hidden" name="cita_id" value="{{ $c->id }}">
                                                <input type="hidden" name="estado" value="Atendida">
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2" title="Marcar Atendida (CU10)">
                                                    <i class="bi bi-check-lg"></i> Atender
                                                </button>
                                            </form>
                                            @endif

                                            <!-- CU08: Reprogramar Cita -->
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2" title="Reprogramar fecha/hora (CU08)"
                                                    onclick="abrirModalReprogramar('{{ $c->id }}', '{{ $c->codigo }}', '{{ $c->fecha_cita }}', '{{ substr($c->hora_inicio, 0, 5) }}')">
                                                <i class="bi bi-arrow-repeat"></i> Reprogramar
                                            </button>

                                            <!-- CU09: Cancelar Cita -->
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Cancelar Cita (CU09)"
                                                    onclick="abrirModalCancelar('{{ $c->id }}', '{{ $c->codigo }}')">
                                                <i class="bi bi-x-lg"></i> Cancelar
                                            </button>
                                        @elseif($c->estado === 'Cancelada' && $c->motivo_cancelacion)
                                            <span class="text-muted small fst-italic" title="{{ $c->motivo_cancelacion }}">
                                                <i class="bi bi-info-circle me-1"></i>{{ Str::limit($c->motivo_cancelacion, 16) }}
                                            </span>
                                        @else
                                            <span class="text-success small fw-medium">
                                                <i class="bi bi-check-all"></i> Concluida
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    No se encontraron citas médicas registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
                        Indique la nueva fecha y franja de hora deseada. El sistema validará que el médico no tenga otra cita asignada en ese bloque (RF07).
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

<!-- Modal para Cancelar Cita Médica (CU09) -->
<div class="modal fade" id="modalCancelarCita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('citas.cancelar') }}" method="POST">
                @csrf
                <input type="hidden" name="cita_id" id="modal_cancelar_cita_id">

                <div class="modal-header">
                    <h6 class="modal-title fw-bold text-danger">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i> CU09: Cancelar Cita Médica <span id="modal_cancelar_codigo"></span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary small mb-3">
                        Indique el motivo de la cancelación. Al confirmar, el horario del médico quedará inmediatamente liberado para otros pacientes.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Motivo de Cancelación *</label>
                        <textarea name="motivo_cancelacion" id="modal_cancelar_motivo" class="form-control rounded-3" rows="3"
                                  placeholder="Ej. Incompatibilidad de horario, imprevisto de salud, reprogramación..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold">Confirmar Cancelación</button>
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

    function abrirModalCancelar(id, codigo) {
        document.getElementById('modal_cancelar_cita_id').value = id;
        document.getElementById('modal_cancelar_codigo').textContent = codigo;
        document.getElementById('modal_cancelar_motivo').value = '';
        var modal = new bootstrap.Modal(document.getElementById('modalCancelarCita'));
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        setupTableFilter('buscarCitas', 'tablaCitas');

        const selEsp = document.getElementById('selectEspecialidad');
        const selMed = document.getElementById('selectMedico');
        if (selEsp && selMed) {
            selEsp.addEventListener('change', function () {
                const espId = this.value;
                const options = selMed.querySelectorAll('option');
                options.forEach(opt => {
                    if (opt.value === '') return;
                    const optEsp = opt.getAttribute('data-especialidad');
                    if (!espId || optEsp == espId) {
                        opt.style.display = '';
                    } else {
                        opt.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
