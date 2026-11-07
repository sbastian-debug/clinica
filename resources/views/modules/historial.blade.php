@php
    $rol = session('rol', 'Administrador');
    $paciente = $historialData['paciente'] ?? null;
    $citasHistorial = $historialData['citas_historial'] ?? collect();
    $stats = $historialData['stats'] ?? ['total' => 0, 'atendidas' => 0, 'canceladas' => 0, 'ausentes' => 0, 'programadas' => 0];
@endphp

<div class="row g-4">
    <!-- Selector de Paciente (Oculto para rol Paciente) -->
    @if($rol !== 'Paciente')
    <div class="col-12">
        <div class="custom-card p-3 p-sm-4">
            <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-3">
                <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3">
                    <i class="bi bi-folder2-open fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">{{ $rol === 'Administrador' ? 'CU12: ' : '' }}Consultar Historial de Citas por Paciente</h6>
                    <span class="text-muted small">Seleccione un paciente para auditar su trayectoria de citas, consultas médicas y asistencias</span>
                </div>
            </div>

            <form action="{{ route('dashboard') }}" method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="mod" value="historial">
                <div class="col-md-9 col-lg-8">
                    <label class="form-label small fw-semibold text-secondary">Buscar Paciente por Expediente o CI</label>
                    <select name="paciente_id" class="form-select rounded-3" onchange="this.form.submit()">
                        @foreach($pacientes as $p)
                            <option value="{{ $p->id }}" {{ $paciente && $paciente->id == $p->id ? 'selected' : '' }}>
                                {{ $p->codigo }} | {{ $p->nombres }} {{ $p->apellidos }} (CI: {{ $p->ci }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-4">
                    <button type="submit" class="btn btn-primary rounded-3 w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-search"></i>
                        <span>Consultar Expediente</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($paciente)
    <!-- Ficha del Paciente y Resumen de Métricas -->
    <div class="col-12">
        <div class="card border-0 bg-white shadow-sm rounded-4 p-3 p-sm-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 border-bottom pb-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
                        <i class="bi bi-person-vcard fs-2"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="fw-bold text-dark mb-0">{{ $paciente->nombres }} {{ $paciente->apellidos }}</h4>
                            <span class="badge bg-primary rounded-pill px-3 py-1">{{ $paciente->codigo }}</span>
                        </div>
                        <div class="text-muted small mt-1">
                            <span><strong class="text-dark">CI:</strong> {{ $paciente->ci }}</span> |
                            <span><strong class="text-dark">Fecha Nacimiento:</strong> {{ date('d/m/Y', strtotime($paciente->fecha_nacimiento)) }}</span> |
                            <span><strong class="text-dark">Sexo:</strong> {{ $paciente->sexo === 'F' ? 'Femenino' : 'Masculino' }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button onclick="window.print()" class="btn btn-outline-secondary rounded-pill px-3 btn-sm d-flex align-items-center gap-2">
                        <i class="bi bi-printer"></i>
                        <span>Imprimir Ficha</span>
                    </button>
                </div>
            </div>

            <!-- Datos de Contacto Rápidos -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3">
                        <span class="text-muted small d-block">Teléfono de Contacto</span>
                        <a href="tel:{{ $paciente->telefono }}" class="fw-bold text-dark text-decoration-none">
                            <i class="bi bi-telephone text-primary me-1"></i> {{ $paciente->telefono }}
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3">
                        <span class="text-muted small d-block">Correo Electrónico</span>
                        <span class="fw-bold text-dark">{{ $paciente->email ?? 'No registrado' }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3">
                        <span class="text-muted small d-block">Dirección Domiciliaria</span>
                        <span class="fw-bold text-dark">{{ $paciente->direccion ?? 'No registrada' }}</span>
                    </div>
                </div>
            </div>

            <!-- Ficha Médica de Base del Paciente -->
            @if(!empty($paciente->tipo_sangre) || !empty($paciente->alergias) || !empty($paciente->enfermedades_cronicas) || !empty($paciente->medicamentos) || !empty($paciente->antecedentes_relevantes))
            <div class="p-3 rounded-3 bg-light border border-info border-opacity-25 mb-4">
                <div class="d-flex align-items-center gap-2 mb-2 pb-1 border-bottom">
                    <i class="bi bi-heart-pulse-fill text-danger"></i>
                    <strong class="text-dark small text-uppercase">Información Médica del Paciente</strong>
                </div>
                <div class="row g-2 small">
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block">Tipo de Sangre:</span>
                        <span class="badge bg-danger">{{ $paciente->tipo_sangre ?? 'No especificado' }}</span>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block">Alergias:</span>
                        <strong class="{{ !empty($paciente->alergias) ? 'text-warning text-dark' : 'text-secondary' }}">{{ $paciente->alergias ?? 'Ninguna conocida' }}</strong>
                    </div>
                    <div class="col-12 col-md-6">
                        <span class="text-muted d-block">Enfermedades Crónicas:</span>
                        <span class="text-dark">{{ $paciente->enfermedades_cronicas ?? 'Sin antecedentes crónicos registrados' }}</span>
                    </div>
                    @if(!empty($paciente->medicamentos))
                    <div class="col-12 col-md-6 mt-1">
                        <span class="text-muted d-block">Medicamentos Actuales:</span>
                        <span class="text-dark">{{ $paciente->medicamentos }}</span>
                    </div>
                    @endif
                    @if(!empty($paciente->antecedentes_relevantes))
                    <div class="col-12 col-md-6 mt-1">
                        <span class="text-muted d-block">Antecedentes Relevantes:</span>
                        <span class="text-dark">{{ $paciente->antecedentes_relevantes }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Resumen Estadístico de Citas -->
            <div class="row g-2 g-sm-3">
                <div class="col-6 col-md-3">
                    <div class="border rounded-3 p-3 text-center bg-white shadow-xs">
                        <div class="text-muted small fw-medium">Total Citas Solicitadas</div>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['total'] }}</h3>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="border rounded-3 p-3 text-center bg-white shadow-xs">
                        <div class="text-success small fw-medium">Consultas Atendidas</div>
                        <h3 class="fw-bold text-success mb-0 mt-1">{{ $stats['atendidas'] }}</h3>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="border rounded-3 p-3 text-center bg-white shadow-xs">
                        <div class="text-danger small fw-medium">Citas Canceladas</div>
                        <h3 class="fw-bold text-danger mb-0 mt-1">{{ $stats['canceladas'] }}</h3>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="border rounded-3 p-3 text-center bg-white shadow-xs">
                        <div class="text-secondary small fw-medium">Inasistencias (No Asistió)</div>
                        <h3 class="fw-bold text-secondary mb-0 mt-1">{{ $stats['ausentes'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Línea de Tiempo y Tabla Cronológica de Citas -->
    <div class="col-12">
        <div class="custom-card">
            <div class="p-3 p-sm-4 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-1 text-dark">Línea de Tiempo de Consultas Médicas</h6>
                    <span class="text-muted small">Registro histórico ordenado desde la más reciente</span>
                </div>
                <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
                    {{ $citasHistorial->count() }} Registros
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Fecha</th>
                            <th>Horario</th>
                            <th>Especialidad</th>
                            <th>Médico Tratante</th>
                            <th>Motivo de Consulta</th>
                            <th>Estado Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($citasHistorial as $ch)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-primary border fw-bold px-2 py-1">
                                        {{ $ch->codigo }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">{{ date('d/m/Y', strtotime($ch->fecha_cita)) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ substr($ch->hora_inicio, 0, 5) }} - {{ substr($ch->hora_fin, 0, 5) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                                        {{ $ch->especialidad_nombre }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-medium text-dark">{{ $ch->medico_nombre }}</span>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $ch->motivo_consulta ?? 'Sin motivo' }}</span>
                                </td>
                                <td>
                                    @if($ch->estado === 'Atendida')
                                        <span class="badge badge-status badge-atendida">
                                            <i class="bi bi-check-circle me-1"></i> Atendida
                                        </span>
                                    @elseif($ch->estado === 'Programada')
                                        <span class="badge badge-status badge-programada">
                                            <i class="bi bi-hourglass-split me-1"></i> Programada
                                        </span>
                                    @elseif($ch->estado === 'En Espera')
                                        <span class="badge bg-warning text-dark px-2 py-1 rounded-pill fw-bold">
                                            <i class="bi bi-clock me-1"></i> En Espera
                                        </span>
                                    @elseif($ch->estado === 'No Asistió')
                                        <span class="badge bg-secondary text-white px-2 py-1 rounded-pill fw-bold">
                                            <i class="bi bi-person-x me-1"></i> No Asistió
                                        </span>
                                    @elseif($ch->estado === 'Cancelada')
                                        <span class="badge badge-status badge-cancelada" title="Motivo: {{ $ch->motivo_cancelacion }}">
                                            <i class="bi bi-x-circle me-1"></i> Cancelada
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    Este paciente aún no registra antecedentes de citas en el sistema.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="col-12 text-center py-5">
        <i class="bi bi-person-search fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-secondary">Seleccione un paciente para visualizar su historial clínico</h5>
    </div>
    @endif
</div>
