<div class="row g-4">
    <!-- Formulario para Registrar Nuevo Médico -->
    <div class="col-12">
        <div class="custom-card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-info bg-opacity-10 text-info rounded-3">
                        <i class="bi bi-person-badge-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Registrar Nuevo Médico {{ session('rol') === 'Administrador' ? '(CU03 / RF03)' : '' }}</h6>
                        <span class="text-muted small">{{ session('rol') === 'Administrador' ? 'Asignación de matrícula profesional única (RN02), código correlativo y especialidad' : 'Asignación de matrícula profesional única, código y especialidad' }}</span>
                    </div>
                </div>
            </div>

            @if(session('error_medico'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>{{ session('error_medico') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success_medico'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('success_medico') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('medicos.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Matrícula Profesional *</label>
                    <input type="text" name="nro_registro_profesional" class="form-control rounded-3" placeholder="Ej. MP-10234" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Nombres *</label>
                    <input type="text" name="nombres" class="form-control rounded-3" placeholder="Ej. Juan Roberto" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Apellidos *</label>
                    <input type="text" name="apellidos" class="form-control rounded-3" placeholder="Ej. Pérez Silva" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Especialidad Principal *</label>
                    <select name="especialidad_id" class="form-select rounded-3" required>
                        <option value="">-- Seleccionar Especialidad --</option>
                        @foreach($especialidades as $e)
                            <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Teléfono *</label>
                    <input type="text" name="telefono" class="form-control rounded-3" placeholder="Ej. 70011223" required>
                </div>

                <div class="col-md-5">
                    <label class="form-label small fw-semibold text-secondary">Correo Electrónico *</label>
                    <input type="email" name="email" class="form-control rounded-3" placeholder="medico@vidasalud.bo" required>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-person-check-fill"></i>
                        <span>Registrar Médico</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Médicos -->
    <div class="col-12">
        <div class="custom-card">
            <div class="p-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h6 class="fw-bold mb-1 text-dark">Plantel Médico Especializado</h6>
                    <span class="text-muted small">Cuerpo facultativo y profesionales de la clínica</span>
                </div>
                <div class="search-box" style="width: 320px;">
                    <i class="bi bi-search"></i>
                    <input type="text" id="buscarMedicos" class="form-control form-control-sm" placeholder="Buscar por código, médico o matrícula...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaMedicos">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Matrícula</th>
                            <th>Médico Especialista</th>
                            <th>Especialidad</th>
                            <th>Teléfono</th>
                            <th>Correo Institucional</th>
                            <th class="text-end">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($medicos as $m)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-primary border fw-bold px-2 py-1">
                                        {{ $m->codigo }}
                                    </span>
                                </td>
                                <td><span class="fw-semibold text-dark">{{ $m->nro_registro_profesional }}</span></td>
                                <td>
                                    <div class="fw-bold text-dark">Dr(a). {{ $m->nombres }} {{ $m->apellidos }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                                        {{ $m->especialidad ?? 'Medicina General' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="tel:{{ $m->telefono }}" class="text-decoration-none text-dark">
                                        <i class="bi bi-telephone text-primary me-1"></i>{{ $m->telefono }}
                                    </a>
                                </td>
                                <td>{{ $m->email }}</td>
                                <td class="text-end">
                                    <span class="badge badge-status badge-atendida">
                                        <i class="bi bi-check-circle me-1"></i> Activo
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-badge fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    No se encontraron médicos registrados.
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
        setupTableFilter('buscarMedicos', 'tablaMedicos');
    });
</script>
