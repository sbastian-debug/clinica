<div class="row g-4">
    <!-- Formulario para Registrar Nueva Especialidad -->
    <div class="col-12 col-lg-5">
        <div class="custom-card p-3 p-sm-4">
            <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-3">
                <div class="p-2 bg-success bg-opacity-10 text-success rounded-3">
                    <i class="bi bi-diagram-3-fill fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Nueva Especialidad {{ session('rol') === 'Administrador' ? '(CU04 / RF04)' : '' }}</h6>
                    <span class="text-muted small">Alta de área médica y servicios de consulta</span>
                </div>
            </div>

            @if(session('error_especialidad'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>{{ session('error_especialidad') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success_especialidad'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('success_especialidad') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('especialidades.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-12">
                    <label class="form-label small fw-semibold text-secondary">Nombre de la Especialidad *</label>
                    <input type="text" name="nombre" class="form-control rounded-3" placeholder="Ej. Cardiología, Dermatología..." required>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-semibold text-secondary">Descripción del Servicio</label>
                    <textarea name="descripcion" class="form-control rounded-3" rows="3" placeholder="Breve descripción del alcance del área clínica..."></textarea>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-success w-100 rounded-3 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>Guardar Especialidad</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Directorio de Especialidades -->
    <div class="col-12 col-lg-7">
        <div class="custom-card">
            <div class="p-3 p-sm-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h6 class="fw-bold mb-1 text-dark">Especialidades Médicas Habilitadas</h6>
                    <span class="text-muted small">Catálogo de áreas asistenciales de la clínica</span>
                </div>
                <div class="search-box" style="width: 240px; max-width: 100%;">
                    <i class="bi bi-search"></i>
                    <input type="text" id="buscarEspecialidades" class="form-control form-control-sm" placeholder="Buscar especialidad...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaEspecialidades">
                    <thead>
                        <tr>
                            <th style="width: 70px;">#</th>
                            <th>Especialidad</th>
                            <th>Descripción</th>
                            <th class="text-end">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($especialidades as $e)
                            <tr>
                                <td>
                                    <span class="text-muted fw-bold">#{{ $e->id }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                        <i class="bi bi-bookmark-plus text-primary"></i>
                                        {{ $e->nombre }}
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $e->descripcion ?? 'Sin descripción adicional' }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="badge badge-status badge-atendida">
                                        <i class="bi bi-check-circle me-1"></i> Activa
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-diagram-3 fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    No se encontraron especialidades médicas registradas.
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
        setupTableFilter('buscarEspecialidades', 'tablaEspecialidades');
    });
</script>
