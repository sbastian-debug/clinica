<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel de Control') - Clínica Vida Salud</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0284c7;
            --primary-dark: #0369a1;
            --primary-light: #e0f2fe;
            --sidebar-bg: #0f172a;
            --sidebar-card: #1e293b;
            --bg-body: #f8fafc;
            --card-border: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 290px;
            background-color: var(--sidebar-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            box-shadow: 4px 0 16px rgba(0, 0, 0, 0.05);
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 1.5rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.4rem;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
        }

        .sidebar-user {
            background-color: var(--sidebar-card);
            border-radius: 14px;
            margin: 1.25rem 1rem;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #334155;
            color: #38bdf8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .nav-pills .nav-link {
            color: #94a3b8;
            font-weight: 500;
            padding: 0.65rem 1rem;
            margin: 0.15rem 0.8rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 11px;
            font-size: 0.86rem;
            transition: all 0.2s ease;
        }

        .nav-pills .nav-link:hover {
            color: #f8fafc;
            background-color: rgba(255, 255, 255, 0.06);
        }

        .nav-pills .nav-link.active {
            color: #ffffff;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
            font-weight: 600;
        }

        .nav-pills .nav-link i {
            font-size: 1.1rem;
        }

        /* Main Content */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        .top-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--card-border);
            padding: 0.9rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .content-body {
            padding: 2rem;
            flex: 1;
        }

        /* KPI Card Styling */
        .kpi-card {
            background-color: #ffffff;
            border-radius: 16px;
            border: 1px solid var(--card-border);
            padding: 1.25rem 1.5rem;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
        }

        .kpi-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Tables & Standard Cards */
        .custom-card {
            background-color: #ffffff;
            border-radius: 16px;
            border: 1px solid var(--card-border);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }

        .table > :not(caption) > * > * {
            padding: 0.85rem 1rem;
            vertical-align: middle;
        }

        .table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }

        /* Badges */
        .badge-status {
            font-weight: 600;
            padding: 0.45em 0.85em;
            border-radius: 30px;
            font-size: 0.78rem;
            letter-spacing: 0.3px;
        }

        .badge-programada {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        .badge-atendida {
            background-color: #dcfce7;
            color: #15803d;
        }

        .badge-cancelada {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        /* Modals */
        .modal-content {
            border-radius: 18px;
            border: none;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-bottom: 1px solid #f1f5f9;
            padding: 1.25rem 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #f1f5f9;
            padding: 1.25rem 1.5rem;
        }

        /* Search input */
        .search-box {
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .search-box input {
            padding-left: 40px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
        }

        @media print {
            .sidebar, .top-navbar, .d-print-none, .btn, .modal {
                display: none !important;
            }
            .content-body {
                padding: 0 !important;
            }
            body {
                background-color: #ffffff !important;
            }
            .custom-card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body>

@php
    $rol = session('rol', 'Administrador');
    $badgeRolColor = match($rol) {
        'Administrador' => 'bg-danger',
        'Recepcionista' => 'bg-primary',
        'Medico'        => 'bg-success',
        'Paciente'      => 'bg-info text-dark',
        default         => 'bg-secondary'
    };
@endphp

<div class="d-flex">
    <!-- Sidebar -->
    <aside class="sidebar d-print-none">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="bi bi-heart-pulse-fill"></i>
            </div>
            <div>
                <h6 class="text-white fw-bold mb-0">Vida Salud</h6>
                <span class="text-info small" style="font-size: 0.75rem;">Sistema Clínico Integral</span>
            </div>
        </div>

        <!-- Usuario Conectado -->
        <div class="sidebar-user">
            <div class="user-avatar">
                {{ strtoupper(substr(session('username', 'U'), 0, 2)) }}
            </div>
            <div class="overflow-hidden">
                <div class="text-white fw-semibold small text-truncate">
                    {{ session('nombre_completo', session('username')) }}
                </div>
                <span class="badge {{ $badgeRolColor }} text-uppercase" style="font-size: 0.68rem;">
                    {{ $rol }}
                </span>
            </div>
        </div>

        <!-- Menú de Casos de Uso del Sistema -->
        <div class="px-3 mb-2 text-uppercase text-white-50 small fw-bold" style="font-size: 0.68rem; letter-spacing: 0.8px;">
            @if($rol === 'Paciente')
                Portal del Paciente
            @elseif($rol === 'Medico')
                Panel Asistencial Médico
            @else
                Módulos del Sistema
            @endif
        </div>

        <ul class="nav nav-pills flex-column gap-1">
            <!-- Citas Médicas -->
            <li class="nav-item">
                <a href="{{ route('dashboard', ['mod' => 'citas']) }}"
                   class="nav-link {{ ($modulo ?? 'citas') == 'citas' ? 'active' : '' }}">
                    <i class="bi bi-calendar2-check"></i>
                    <span>
                        @if($rol === 'Paciente')
                            Mis Citas Médicas
                        @elseif($rol === 'Medico')
                            Mi Agenda Médica
                        @elseif($rol === 'Administrador')
                            CU06/07. Citas Médicas
                        @else
                            Citas Médicas
                        @endif
                    </span>
                </a>
            </li>

            <!-- Agenda Médica (Admin, Recepción, Médico) -->
            @if(in_array($rol, ['Administrador', 'Recepcionista', 'Medico']))
            <li class="nav-item">
                <a href="{{ route('dashboard', ['mod' => 'agenda']) }}"
                   class="nav-link {{ ($modulo ?? '') == 'agenda' ? 'active' : '' }}">
                    <i class="bi bi-calendar2-range"></i>
                    <span>{{ $rol === 'Administrador' ? 'CU11. Agenda Médica' : 'Agenda Médica' }}</span>
                </a>
            </li>
            @endif

            <!-- Historial de Citas por Paciente (Todos los roles) -->
            <li class="nav-item">
                <a href="{{ route('dashboard', ['mod' => 'historial']) }}"
                   class="nav-link {{ ($modulo ?? '') == 'historial' ? 'active' : '' }}">
                    <i class="bi bi-folder2-open"></i>
                    <span>
                        @if($rol === 'Paciente')
                            Mi Historial Clínico
                        @elseif($rol === 'Administrador')
                            CU12. Historial Paciente
                        @else
                            Historial Paciente
                        @endif
                    </span>
                </a>
            </li>

            <!-- Reportes Administrativos (Admin y Recepción) -->
            @if(in_array($rol, ['Administrador', 'Recepcionista']))
            <li class="nav-item">
                <a href="{{ route('dashboard', ['mod' => 'reportes']) }}"
                   class="nav-link {{ ($modulo ?? '') == 'reportes' ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>{{ $rol === 'Administrador' ? 'CU13. Reportes Administrativos' : 'Reportes Administrativos' }}</span>
                </a>
            </li>
            @endif

            <!-- Separador para Catálogos y Turnos -->
            @if(in_array($rol, ['Administrador', 'Recepcionista']))
            <div class="px-3 mt-3 mb-1 text-uppercase text-white-50 small fw-bold" style="font-size: 0.65rem; letter-spacing: 0.8px;">
                Catálogos y Turnos
            </div>
            @endif

            <!-- Gestión de Pacientes -->
            @if(in_array($rol, ['Administrador', 'Recepcionista']))
            <li class="nav-item">
                <a href="{{ route('dashboard', ['mod' => 'pacientes']) }}"
                   class="nav-link {{ ($modulo ?? '') == 'pacientes' ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>{{ $rol === 'Administrador' ? 'CU02. Pacientes' : 'Pacientes' }}</span>
                </a>
            </li>
            @endif

            <!-- Gestión de Médicos (Admin) -->
            @if($rol === 'Administrador')
            <li class="nav-item">
                <a href="{{ route('dashboard', ['mod' => 'medicos']) }}"
                   class="nav-link {{ ($modulo ?? '') == 'medicos' ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i>
                    <span>CU03. Médicos</span>
                </a>
            </li>
            @endif

            <!-- Especialidades (Admin) -->
            @if($rol === 'Administrador')
            <li class="nav-item">
                <a href="{{ route('dashboard', ['mod' => 'especialidades']) }}"
                   class="nav-link {{ ($modulo ?? '') == 'especialidades' ? 'active' : '' }}">
                    <i class="bi bi-diagram-3"></i>
                    <span>CU04. Especialidades</span>
                </a>
            </li>
            @endif

            <!-- Horarios de Atención -->
            @if(in_array($rol, ['Administrador', 'Recepcionista', 'Medico']))
            <li class="nav-item">
                <a href="{{ route('dashboard', ['mod' => 'horarios']) }}"
                   class="nav-link {{ ($modulo ?? '') == 'horarios' ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i>
                    <span>
                        @if($rol === 'Medico')
                            Mis Horarios de Turno
                        @elseif($rol === 'Administrador')
                            CU05. Horarios Atención
                        @else
                            Horarios de Atención
                        @endif
                    </span>
                </a>
            </li>
            @endif
        </ul>

        <!-- Cierre de Sesión -->
        <div class="mt-auto p-3 border-top border-secondary border-opacity-25">
            <a href="{{ route('logout') }}" class="btn btn-outline-danger w-100 btn-sm rounded-pill py-2 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-right"></i>
                <span class="fw-semibold">Cerrar Sesión</span>
            </a>
        </div>
    </aside>

    <!-- Área Principal -->
    <div class="main-wrapper">
        <!-- Top Navbar -->
        <header class="top-navbar d-print-none">
            <div class="d-flex align-items-center gap-3">
                <h5 class="fw-bold text-dark mb-0">@yield('page_title', 'Panel General')</h5>
                <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
                    <i class="bi bi-calendar3 me-1 text-primary"></i>
                    {{ date('d/m/Y') }}
                </span>
            </div>
            <div class="d-flex align-items-center gap-3">
                @if($rol !== 'Medico')
                <a href="{{ route('dashboard', ['mod' => 'citas']) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle"></i>
                    <span class="fw-semibold">
                        {{ $rol === 'Paciente' ? 'Solicitar Cita' : 'Nueva Cita' }}
                    </span>
                </a>
                <div class="vr"></div>
                @endif
                <div class="text-end d-none d-md-block">
                    <div class="fw-semibold small">{{ session('email') }}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Rol: <strong class="text-primary">{{ $rol }}</strong></div>
                </div>
            </div>
        </header>

        <!-- Cuerpo del Contenido -->
        <main class="content-body">
            <!-- Alertas Generales -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4 d-print-none" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4 d-print-none" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4 d-print-none" role="alert">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div>{{ session('info') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- KPIs Globales / Personalizados por Rol -->
            @if(isset($kpis) && count($kpis) > 0 && ($modulo ?? 'citas') === 'citas')
            <div class="row g-3 mb-4 d-print-none">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="kpi-card">
                        <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-folder-check"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $kpis['label_1'] ?? 'Total' }}</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $kpis['total_pacientes'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="kpi-card">
                        <div class="kpi-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $kpis['label_2'] ?? 'Secundario' }}</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $kpis['total_medicos'] ?? $kpis['citas_pendientes'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="kpi-card">
                        <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-calendar2-day-fill"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $kpis['label_3'] ?? 'Hoy' }}</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $kpis['citas_hoy'] ?? $kpis['citas_atendidas'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="kpi-card">
                        <div class="kpi-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $kpis['label_4'] ?? 'Completadas' }}</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $kpis['citas_atendidas'] ?? $kpis['citas_hoy'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Contenido del Módulo Activo -->
            @yield('content')
        </main>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script para Filtrado Rápido de Tablas en Vivo -->
<script>
    function setupTableFilter(inputId, tableId) {
        const input = document.getElementById(inputId);
        const table = document.getElementById(tableId);
        if (!input || !table) return;

        input.addEventListener('keyup', function () {
            const query = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }
</script>

@yield('scripts')

</body>
</html>
