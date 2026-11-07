<div class="row g-4">
    <!-- Formulario para Asignar Nuevo Horario -->
    <div class="col-12 col-lg-5">
        <div class="custom-card p-3 p-sm-4">
            <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-3">
                <div class="p-2 bg-warning bg-opacity-10 text-warning rounded-3">
                    <i class="bi bi-clock-history fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Asignar Horario {{ session('rol') === 'Administrador' ? '(CU05 / RF05)' : '' }}</h6>
                    <span class="text-muted small">Programación de turnos y validación de fin posterior a inicio</span>
                </div>
            </div>

            @if(session('error_horario'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>{{ session('error_horario') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success_horario'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('success_horario') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('horarios.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-12">
                    <label class="form-label small fw-semibold text-secondary">Médico Especialista *</label>
                    <select name="medico_id" class="form-select rounded-3" required>
                        <option value="">-- Seleccionar Médico --</option>
                        @foreach($medicos as $m)
                            <option value="{{ $m->id }}">Dr(a). {{ $m->nombres }} {{ $m->apellidos }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-semibold text-secondary">Día de Atención *</label>
                    <select name="dia_semana" class="form-select rounded-3" required>
                        <option value="1">Lunes</option>
                        <option value="2">Martes</option>
                        <option value="3">Miércoles</option>
                        <option value="4">Jueves</option>
                        <option value="5">Viernes</option>
                        <option value="6">Sábado</option>
                        <option value="7">Domingo</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label small fw-semibold text-secondary">Hora Inicio *</label>
                    <input type="time" name="hora_inicio" class="form-control rounded-3" value="08:00" required>
                </div>

                <div class="col-6">
                    <label class="form-label small fw-semibold text-secondary">Hora Fin *</label>
                    <input type="time" name="hora_fin" class="form-control rounded-3" value="12:00" required>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-semibold text-secondary">Duración de Bloque (Minutos)</label>
                    <select name="duracion_minutos" class="form-select rounded-3">
                        <option value="15">15 minutos</option>
                        <option value="20">20 minutos</option>
                        <option value="30" selected>30 minutos (Estándar)</option>
                        <option value="45">45 minutos</option>
                        <option value="60">60 minutos</option>
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-warning text-dark w-100 rounded-3 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-clock-fill"></i>
                        <span>Guardar Horario</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Directorio de Horarios Asignados -->
    <div class="col-12 col-lg-7">
        <div class="custom-card">
            <div class="p-3 p-sm-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h6 class="fw-bold mb-1 text-dark">Turnos y Disponibilidad Médica</h6>
                    <span class="text-muted small">Cronograma de horarios para consulta externa</span>
                </div>
                <div class="search-box" style="width: 240px; max-width: 100%;">
                    <i class="bi bi-search"></i>
                    <input type="text" id="buscarHorarios" class="form-control form-control-sm" placeholder="Buscar por médico o día...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaHorarios">
                    <thead>
                        <tr>
                            <th>Médico</th>
                            <th>Día</th>
                            <th>Horario de Atención</th>
                            <th>Bloque</th>
                            <th class="text-end">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $dias = [
                                1 => 'Lunes',
                                2 => 'Martes',
                                3 => 'Miércoles',
                                4 => 'Jueves',
                                5 => 'Viernes',
                                6 => 'Sábado',
                                7 => 'Domingo'
                            ];
                        @endphp
                        @forelse($horarios as $h)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">Dr(a). {{ $h->nombres }} {{ $h->apellidos }}</div>
                                    <span class="text-muted small">{{ $h->medico_codigo }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-medium px-2 py-1">
                                        {{ $dias[$h->dia_semana] ?? $h->dia_semana }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-primary fw-semibold">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ substr($h->hora_inicio, 0, 5) }} - {{ substr($h->hora_fin, 0, 5) }}
                                    </span>
                                </td>
                                <td>{{ $h->duracion_minutos }} min</td>
                                <td class="text-end">
                                    <span class="badge badge-status badge-atendida">
                                        <i class="bi bi-check-circle me-1"></i> Activo
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    No se encontraron horarios de atención configurados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        setupTableFilter('buscarHorarios', 'tablaHorarios');
    });
</script>
