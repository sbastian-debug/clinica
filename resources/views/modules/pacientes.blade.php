<div class="row g-4">
    <!-- Formulario para Registrar Nuevo Paciente -->
    <div class="col-12">
        <div class="custom-card p-3 p-sm-4">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3">
                        <i class="bi bi-person-plus-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Registrar Nuevo Paciente {{ session('rol') === 'Administrador' ? '(CU02 / RF02)' : '' }}</h6>
                        <span class="text-muted small">{{ session('rol') === 'Administrador' ? 'Validación de cédula de identidad única (RN01) y asignación automática de código correlativo' : 'Validación de cédula de identidad única y asignación de código' }}</span>
                    </div>
                </div>
            </div>

            @if(session('error_paciente'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>{{ session('error_paciente') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success_paciente'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('success_paciente') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('pacientes.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Cédula de Identidad (CI) *</label>
                    <input type="text" name="ci" class="form-control rounded-3" placeholder="Ej. 8945123" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Nombres *</label>
                    <input type="text" name="nombres" class="form-control rounded-3" placeholder="Ej. Juan Carlos" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Apellidos *</label>
                    <input type="text" name="apellidos" class="form-control rounded-3" placeholder="Ej. Pérez Gómez" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Fecha de Nacimiento *</label>
                    <input type="date" name="fecha_nacimiento" class="form-control rounded-3" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-secondary">Sexo *</label>
                    <select name="sexo" class="form-select rounded-3" required>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Teléfono de Contacto *</label>
                    <input type="text" name="telefono" class="form-control rounded-3" placeholder="Ej. 70011223" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Correo Electrónico (Opcional)</label>
                    <input type="email" name="email" class="form-control rounded-3" placeholder="paciente@correo.com">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Dirección Domiciliaria</label>
                    <input type="text" name="direccion" class="form-control rounded-3" placeholder="Ej. Calle Sucre #45">
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                        <i class="bi bi-person-check-fill"></i>
                        <span>Registrar Paciente</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Pacientes -->
    <div class="col-12">
        <div class="custom-card">
            <div class="p-3 p-sm-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h6 class="fw-bold mb-1 text-dark">Directorio de Pacientes Registrados</h6>
                    <span class="text-muted small">Total de pacientes en la base de datos de la clínica</span>
                </div>
                <div class="search-box" style="width: 320px; max-width: 100%;">
                    <i class="bi bi-search"></i>
                    <input type="text" id="buscarPacientes" class="form-control form-control-sm" placeholder="Buscar por CI, nombre o teléfono...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaPacientes">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>CI</th>
                            <th>Nombre Completo</th>
                            <th>Sexo</th>
                            <th>Fecha Nacimiento</th>
                            <th>Teléfono</th>
                            <th>Dirección</th>
                            <th class="text-end">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pacientes as $p)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-primary border fw-bold px-2 py-1">
                                        {{ $p->codigo }}
                                    </span>
                                </td>
                                <td><span class="fw-semibold text-dark">{{ $p->ci }}</span></td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $p->nombres }} {{ $p->apellidos }}</div>
                                    @if($p->email)
                                        <span class="text-muted small">{{ $p->email }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $p->sexo === 'F' ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary' }}">
                                        {{ $p->sexo === 'F' ? 'Femenino' : 'Masculino' }}
                                    </span>
                                </td>
                                <td>{{ date('d/m/Y', strtotime($p->fecha_nacimiento)) }}</td>
                                <td>
                                    <a href="tel:{{ $p->telefono }}" class="text-decoration-none text-dark">
                                        <i class="bi bi-telephone text-primary me-1"></i>{{ $p->telefono }}
                                    </a>
                                </td>
                                <td>{{ $p->direccion ?? 'No registrada' }}</td>
                                <td class="text-end">
                                    <span class="badge badge-status badge-atendida">
                                        <i class="bi bi-check-circle me-1"></i> Activo
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    No se encontraron pacientes registrados.
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
        setupTableFilter('buscarPacientes', 'tablaPacientes');
    });
</script>
