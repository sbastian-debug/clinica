<div class="row g-4">
    <!-- Tarjeta de Resumen del Perfil del Paciente -->
    <div class="col-12">
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
            <div class="card-body p-4 text-white">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center shadow-sm fw-bold"
                             style="width: 72px; height: 72px; font-size: 1.8rem; border: 3px solid rgba(255, 255, 255, 0.4);">
                            {{ strtoupper(substr($perfilPaciente->nombres ?? session('nombre_completo', 'P'), 0, 1)) }}{{ strtoupper(substr($perfilPaciente->apellidos ?? '', 0, 1)) }}
                        </div>
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <h4 class="fw-bold mb-0 text-white">
                                    {{ ($perfilPaciente->nombres ?? '') . ' ' . ($perfilPaciente->apellidos ?? '') ?: session('nombre_completo', 'Paciente') }}
                                </h4>
                                <span class="badge bg-white text-primary fw-semibold px-2 py-1 rounded-pill small">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i> Paciente Activo
                                </span>
                            </div>
                            <div class="text-white-50 small d-flex flex-wrap align-items-center gap-3">
                                <span><i class="bi bi-upc-scan me-1"></i> Código: <strong class="text-white">{{ $perfilPaciente->codigo ?? session('paciente_codigo', 'PAC-XXXX') }}</strong></span>
                                <span><i class="bi bi-card-heading me-1"></i> CI: <strong class="text-white">{{ $perfilPaciente->ci ?? session('paciente_ci', '---') }}</strong></span>
                                <span><i class="bi bi-person-badge me-1"></i> Usuario: <strong class="text-white">{{ $perfilPaciente->username ?? session('username') }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column align-items-md-end text-white-50 small">
                        <span><i class="bi bi-calendar-check me-1"></i> Miembro desde: <strong class="text-white">{{ !empty($perfilPaciente->created_at) ? date('d/m/Y', strtotime($perfilPaciente->created_at)) : 'Reciente' }}</strong></span>
                        <span><i class="bi bi-envelope me-1"></i> {{ $perfilPaciente->email ?? session('email') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario para Editar la Información Propia -->
    <div class="col-12">
        <div class="custom-card p-3 p-sm-4">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3">
                        <i class="bi bi-person-gear fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Editar Mi Información Personal</h5>
                        <span class="text-muted small">Actualice sus datos personales, contacto y credenciales de acceso de forma segura.</span>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger rounded-3 py-2 px-3 small mb-3">
                    <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Se encontraron errores en el formulario:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('perfil.actualizar') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <!-- Sección 1: Datos Personales y Expediente -->
                    <div class="col-lg-6 border-end-lg pe-lg-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill p-2">
                                <i class="bi bi-person-vcard fs-6"></i>
                            </span>
                            <h6 class="fw-bold mb-0 text-dark">1. Datos Personales y Expediente</h6>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-semibold text-secondary">Nombres *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" name="nombres" class="form-control rounded-end-3"
                                           value="{{ old('nombres', $perfilPaciente->nombres ?? '') }}" required>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-semibold text-secondary">Apellidos *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" name="apellidos" class="form-control rounded-end-3"
                                           value="{{ old('apellidos', $perfilPaciente->apellidos ?? '') }}" required>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-semibold text-secondary">Cédula de Identidad (CI) *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="bi bi-card-heading"></i>
                                    </span>
                                    <input type="text" name="ci" class="form-control rounded-end-3"
                                           value="{{ old('ci', $perfilPaciente->ci ?? session('paciente_ci', '')) }}" required>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-semibold text-secondary">Fecha de Nacimiento *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="bi bi-calendar-event"></i>
                                    </span>
                                    <input type="date" name="fecha_nacimiento" class="form-control rounded-end-3"
                                           value="{{ old('fecha_nacimiento', $perfilPaciente->fecha_nacimiento ?? '') }}" required>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-semibold text-secondary">Sexo *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="bi bi-gender-ambiguous"></i>
                                    </span>
                                    <select name="sexo" class="form-select rounded-end-3" required>
                                        <option value="M" {{ old('sexo', $perfilPaciente->sexo ?? '') === 'M' ? 'selected' : '' }}>Masculino</option>
                                        <option value="F" {{ old('sexo', $perfilPaciente->sexo ?? '') === 'F' ? 'selected' : '' }}>Femenino</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-semibold text-secondary">Teléfono Celular *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="bi bi-telephone"></i>
                                    </span>
                                    <input type="text" name="telefono" class="form-control rounded-end-3"
                                           value="{{ old('telefono', $perfilPaciente->telefono ?? '') }}" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold text-secondary">Dirección Domiciliaria</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="bi bi-geo-alt"></i>
                                    </span>
                                    <input type="text" name="direccion" class="form-control rounded-end-3"
                                           placeholder="Zona, calle, número de vivienda..."
                                           value="{{ old('direccion', $perfilPaciente->direccion ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 2: Cuenta de Usuario y Acceso -->
                    <div class="col-lg-6 ps-lg-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-info bg-opacity-10 text-primary rounded-pill p-2">
                                <i class="bi bi-shield-lock fs-6"></i>
                            </span>
                            <h6 class="fw-bold mb-0 text-dark">2. Credenciales de Acceso y Cuenta</h6>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-secondary">Código de Expediente Clínico</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="bi bi-upc"></i>
                                    </span>
                                    <input type="text" class="form-control bg-light text-muted rounded-end-3"
                                           value="{{ $perfilPaciente->codigo ?? session('paciente_codigo', 'PAC-XXXX') }}" readonly>
                                </div>
                                <span class="form-text text-muted small" style="font-size: 0.75rem;">
                                    Identificador institucional único asignado al registrarse.
                                </span>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold text-secondary">Nombre de Usuario *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="bi bi-at"></i>
                                    </span>
                                    <input type="text" name="username" class="form-control rounded-end-3"
                                           value="{{ old('username', $perfilPaciente->username ?? session('username')) }}" required>
                                </div>
                                <span class="form-text text-muted small" style="font-size: 0.75rem;">
                                    Nombre único para iniciar sesión en el portal.
                                </span>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold text-secondary">Correo Electrónico *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" name="email" class="form-control rounded-end-3"
                                           value="{{ old('email', $perfilPaciente->email ?? session('email')) }}" required>
                                </div>
                                <span class="form-text text-muted small" style="font-size: 0.75rem;">
                                    Utilizado para notificaciones y recuperación de acceso.
                                </span>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold text-secondary">Cambiar Contraseña (Opcional)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="bi bi-key"></i>
                                    </span>
                                    <input type="password" name="password" class="form-control rounded-end-3"
                                           placeholder="Dejar en blanco para conservar la actual" minlength="6">
                                </div>
                                <span class="form-text text-muted small" style="font-size: 0.75rem;">
                                    Ingrese una nueva clave únicamente si desea modificar su contraseña de acceso (mínimo 6 caracteres).
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="col-12 pt-3 border-top mt-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <a href="{{ route('dashboard', ['mod' => 'citas']) }}" class="btn btn-light border rounded-3 px-3 py-2 text-secondary d-flex align-items-center gap-1 shadow-none">
                            <i class="bi bi-arrow-left"></i> Volver a Mis Citas
                        </a>

                        <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 shadow-sm d-flex align-items-center gap-2 fw-semibold">
                            <i class="bi bi-check2-circle fs-5"></i>
                            <span>Guardar Cambios de Perfil</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
