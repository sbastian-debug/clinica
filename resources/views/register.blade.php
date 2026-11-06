<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Paciente - Clínica Vida Salud</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0369a1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .register-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 2.5rem;
            width: 100%;
            max-width: 680px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
        }

        .brand-badge {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: #ffffff;
            box-shadow: 0 6px 16px rgba(2, 132, 199, 0.35);
        }

        .btn-submit {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: white;
            font-weight: 600;
            padding: 0.85rem;
            border-radius: 12px;
            border: none;
            transition: all 0.25s ease;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #0369a1, #075985);
            transform: translateY(-1px);
            color: white;
        }
    </style>
</head>
<body>

<div class="register-card">
    <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
        <div class="brand-badge">
            <i class="bi bi-person-heart"></i>
        </div>
        <div>
            <h4 class="fw-bold text-dark mb-0">Portal del Paciente</h4>
            <p class="text-muted small mb-0">Cree su cuenta para agendar y consultar sus citas médicas</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger py-2 px-3 small rounded-3 d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-exclamation-circle-fill"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <form action="{{ route('register.post') }}" method="POST" class="row g-3">
        @csrf

        <!-- Datos de Cuenta -->
        <div class="col-12">
            <div class="text-uppercase fw-bold text-primary small mb-1" style="font-size: 0.72rem; letter-spacing: 0.8px;">
                1. Credenciales de Acceso
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-semibold text-secondary">Nombre de Usuario *</label>
            <input type="text" name="username" class="form-control rounded-3" value="{{ old('username') }}" placeholder="Ej. mlopez" required>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-semibold text-secondary">Correo Electrónico *</label>
            <input type="email" name="email" class="form-control rounded-3" value="{{ old('email') }}" placeholder="correo@gmail.com" required>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-semibold text-secondary">Contraseña *</label>
            <input type="password" name="password" class="form-control rounded-3" placeholder="Mínimo 6 caracteres" required>
        </div>

        <!-- Datos Personales -->
        <div class="col-12 mt-4">
            <div class="text-uppercase fw-bold text-primary small mb-1" style="font-size: 0.72rem; letter-spacing: 0.8px;">
                2. Expediente del Paciente (CU02)
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-semibold text-secondary">Cédula de Identidad (CI) *</label>
            <input type="text" name="ci" class="form-control rounded-3" value="{{ old('ci') }}" placeholder="Ej. 8945123" required>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-semibold text-secondary">Nombres *</label>
            <input type="text" name="nombres" class="form-control rounded-3" value="{{ old('nombres') }}" placeholder="Ej. María" required>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-semibold text-secondary">Apellidos *</label>
            <input type="text" name="apellidos" class="form-control rounded-3" value="{{ old('apellidos') }}" placeholder="Ej. López Suárez" required>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-semibold text-secondary">Fecha de Nacimiento *</label>
            <input type="date" name="fecha_nacimiento" class="form-control rounded-3" value="{{ old('fecha_nacimiento') }}" required>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-semibold text-secondary">Sexo *</label>
            <select name="sexo" class="form-select rounded-3" required>
                <option value="F" selected>Femenino</option>
                <option value="M">Masculino</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-semibold text-secondary">Teléfono Celular *</label>
            <input type="text" name="telefono" class="form-control rounded-3" value="{{ old('telefono') }}" placeholder="Ej. 75011122" required>
        </div>

        <div class="col-12">
            <label class="form-label small fw-semibold text-secondary">Dirección Domiciliaria</label>
            <input type="text" name="direccion" class="form-control rounded-3" value="{{ old('direccion') }}" placeholder="Ej. Av. Beni #120">
        </div>

        <div class="col-12 mt-4">
            <button type="submit" class="btn btn-submit w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                <span>Completar Registro de Paciente</span>
            </button>
        </div>

        <div class="col-12 text-center mt-3">
            <span class="text-muted small">¿Ya tiene una cuenta?</span>
            <a href="{{ route('login') }}" class="text-primary fw-semibold small text-decoration-none ms-1">
                Iniciar Sesión aquí
            </a>
        </div>
    </form>
</div>

</body>
</html>
