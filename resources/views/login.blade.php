<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema - Clínica Vida Salud</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- SweetAlert2 (Para ventana emergente de error) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0369a1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 2.25rem 2.5rem;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
        }

        .brand-badge {
            width: 58px;
            height: 58px;
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #ffffff;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 18px rgba(2, 132, 199, 0.35);
        }

        .form-control:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.15);
        }

        .btn-submit {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: white;
            font-weight: 600;
            padding: 0.8rem;
            border-radius: 12px;
            border: none;
            transition: all 0.25s ease;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #0369a1, #075985);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(2, 132, 199, 0.35);
            color: white;
        }

        .role-btn {
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            border-radius: 10px;
            padding: 0.5rem 0.6rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }

        .role-btn:hover {
            background-color: #e0f2fe;
            border-color: #0284c7;
            color: #0369a1;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center">
        <div class="brand-badge">
            <i class="bi bi-heart-pulse-fill"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1">Clínica Vida Salud</h4>
        <p class="text-muted small mb-3">Portal de Gestión y Citas Médicas</p>
    </div>

    @if(session('info'))
        <div class="alert alert-info py-2 px-3 small rounded-3 d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-info-circle-fill"></i>
            <div>{{ session('info') }}</div>
        </div>
    @endif

    <!-- Selector Rápido de Cuentas Demo -->
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small fw-bold text-secondary text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                <i class="bi bi-people-fill text-primary me-1"></i> Rellenar datos de prueba:
            </span>
        </div>
        <div class="row g-2">
            <div class="col-6 col-sm-3">
                <div class="role-btn" onclick="seleccionarRol('admin', '123456')">
                    <i class="bi bi-shield-lock d-block text-danger fs-6 mb-1"></i>
                    <span>Admin</span>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="role-btn" onclick="seleccionarRol('recepcion', '123456')">
                    <i class="bi bi-person-lines-fill d-block text-primary fs-6 mb-1"></i>
                    <span>Recepción</span>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="role-btn" onclick="seleccionarRol('jperez', '123456')">
                    <i class="bi bi-stethoscope d-block text-success fs-6 mb-1"></i>
                    <span>Médico</span>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="role-btn" onclick="seleccionarRol('mlopez', '123456')">
                    <i class="bi bi-person-heart d-block text-info fs-6 mb-1"></i>
                    <span>Paciente</span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('login.post') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label small fw-semibold text-secondary">Usuario o Correo Institucional</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 12px 0 0 12px;">
                    <i class="bi bi-person"></i>
                </span>
                <input type="text" name="credencial" id="credencial" class="form-control border-start-0"
                       style="border-radius: 0 12px 12px 0;"
                       value="{{ old('credencial', 'admin') }}" required autofocus placeholder="Usuario o correo electrónico">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-semibold text-secondary">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 12px 0 0 12px;">
                    <i class="bi bi-lock"></i>
                </span>
                <input type="password" name="password" id="password" class="form-control border-start-0"
                       style="border-radius: 0 12px 12px 0;"
                       value="" required placeholder="••••••••">
            </div>
        </div>

        <button type="submit" class="btn btn-submit w-100 d-flex align-items-center justify-content-center gap-2 mb-3">
            <span>Iniciar Sesión</span>
            <i class="bi bi-arrow-right"></i>
        </button>

        <div class="text-center pt-2 border-top">
            <span class="text-muted small">¿Eres un nuevo paciente?</span>
            <a href="{{ route('register') }}" class="text-primary fw-semibold small text-decoration-none ms-1">
                Crear una cuenta aquí <i class="bi bi-box-arrow-up-right"></i>
            </a>
        </div>
    </form>
</div>

<script>
    function seleccionarRol(user, pass) {
        document.getElementById('credencial').value = user;
        document.getElementById('password').value = pass;
    }

    // Ventana emergente de error con SweetAlert2
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Acceso Denegado',
            text: "{{ session('error') }}",
            confirmButtonColor: '#0284c7',
            confirmButtonText: 'Entendido',
            customClass: {
                popup: 'rounded-4 shadow'
            }
        });
    @endif
</script>

</body>
</html>
