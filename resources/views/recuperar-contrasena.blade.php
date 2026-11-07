<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Clínica Vida Salud</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- SweetAlert2 -->
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

        .recovery-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 2.5rem;
            width: 100%;
            max-width: 460px;
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
            margin: 0 auto 1.25rem;
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
            padding: 0.85rem;
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

        @media (max-width: 480px) {
            body {
                padding: 1.25rem 0.85rem;
            }

            .recovery-card {
                padding: 1.75rem 1.25rem;
                border-radius: 20px;
            }

            .brand-badge {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>

<body>

    <div class="recovery-card">
        <div class="text-center mb-3">
            <div class="brand-badge">
                <i class="bi bi-key-fill"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">Recuperar Contraseña</h4>
            <p class="text-muted small mb-3">Portal del Paciente - Clínica Vida Salud</p>
        </div>

        <div class="alert alert-light border border-info border-opacity-25 rounded-3 p-3 small text-secondary mb-4">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-info-circle-fill text-primary fs-5 mt-n1"></i>
                <div>
                    Ingrese su <strong>correo electrónico</strong> o <strong>nombre de usuario</strong> registrado.
                    Le enviaremos una <strong>contraseña temporal</strong> con la que podrá volver a ingresar al sistema y actualizarla desde su perfil.
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger py-2 px-3 small rounded-3 d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-exclamation-circle-fill"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="form-label small fw-semibold text-secondary">Correo Electrónico o Usuario *</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"
                        style="border-radius: 12px 0 0 12px;">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="text" name="credencial" class="form-control border-start-0"
                        style="border-radius: 0 12px 12px 0;" value="{{ old('credencial') }}" required autofocus
                        placeholder="ejemplo@correo.com o usuario">
                </div>
                <div class="form-text text-muted small" style="font-size: 0.72rem;">
                    La cuenta debe estar previamente registrada en la clínica.
                </div>
            </div>

            <button type="submit"
                class="btn btn-submit w-100 d-flex align-items-center justify-content-center gap-2 mb-4">
                <i class="bi bi-send-fill"></i>
                <span>Enviar Contraseña Temporal</span>
            </button>

            <div class="text-center pt-3 border-top d-flex flex-column gap-2">
                <div>
                    <a href="{{ route('login') }}" class="text-decoration-none small fw-semibold text-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Volver a Iniciar Sesión
                    </a>
                </div>
                <div class="small text-muted" style="font-size: 0.78rem;">
                    ¿Aún no tiene una cuenta?
                    <a href="{{ route('register') }}" class="text-primary text-decoration-none fw-semibold ms-1">
                        Crear cuenta aquí
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script>
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Cuenta no encontrada',
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
