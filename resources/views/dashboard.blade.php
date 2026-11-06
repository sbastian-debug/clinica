@extends('layouts.app')

@php
    $rol = session('rol', 'Administrador');
    $titulos = [
        'citas'          => $rol === 'Paciente' ? 'Mis Citas Médicas' : ($rol === 'Medico' ? 'Mi Agenda Médica' : 'Gestión de Citas Médicas (CU06 / CU07)'),
        'agenda'         => 'CU11: Consultar Agenda Médica Diaria',
        'historial'      => $rol === 'Paciente' ? 'Mi Historial Clínico de Citas' : 'CU12: Consultar Historial de Citas por Paciente',
        'reportes'       => 'CU13: Generar Reportes Administrativos y Estadísticos',
        'pacientes'      => 'CU02: Directorio de Pacientes',
        'medicos'        => 'CU03: Plantel Médico Especializado',
        'especialidades' => 'CU04: Especialidades Médicas',
        'horarios'       => 'CU05: Horarios de Atención Médica',
    ];
    $moduloActual = $modulo ?? 'citas';
    $tituloActual = $titulos[$moduloActual] ?? 'Panel de Control';
@endphp

@section('title', $tituloActual)
@section('page_title', $tituloActual)

@section('content')
    <!-- Banner Personalizado para Paciente -->
    @if($rol === 'Paciente')
        <div class="card border-0 bg-primary bg-opacity-10 rounded-4 p-4 mb-4 shadow-sm d-print-none">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-white text-primary rounded-circle shadow-sm">
                        <i class="bi bi-person-heart fs-3"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">Expediente Clínico: {{ session('nombre_completo', session('username')) }}</h5>
                        <div class="text-secondary small">
                            <span class="me-2"><strong class="text-dark">Código de Paciente:</strong> {{ session('paciente_codigo', 'PAC-0001') }}</span>
                            @if(session('paciente_ci'))
                                <span>| <strong class="text-dark">CI:</strong> {{ session('paciente_ci') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <span class="badge bg-primary px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-shield-check me-1"></i> Sesión de Paciente Verificada
                </span>
            </div>
        </div>
    @endif

    <!-- Banner Personalizado para Médico -->
    @if($rol === 'Medico')
        <div class="card border-0 bg-success bg-opacity-10 rounded-4 p-4 mb-4 shadow-sm d-print-none">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-white text-success rounded-circle shadow-sm">
                        <i class="bi bi-stethoscope fs-3"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">Consultorio del {{ session('nombre_completo', session('username')) }}</h5>
                        <div class="text-secondary small">
                            <span><strong class="text-dark">Código Profesional:</strong> {{ session('medico_codigo', 'MED-XXXX') }}</span>
                        </div>
                    </div>
                </div>
                <span class="badge bg-success px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-patch-check me-1"></i> Sesión Médica Activa
                </span>
            </div>
        </div>
    @endif

    <!-- Renderizado Dinámico de Casos de Uso -->
    @if($moduloActual === 'agenda')
        @include('modules.agenda')
    @elseif($moduloActual === 'historial')
        @include('modules.historial')
    @elseif($moduloActual === 'reportes')
        @include('modules.reportes')
    @elseif($moduloActual === 'pacientes')
        @include('modules.pacientes')
    @elseif($moduloActual === 'medicos')
        @include('modules.medicos')
    @elseif($moduloActual === 'especialidades')
        @include('modules.especialidades')
    @elseif($moduloActual === 'horarios')
        @include('modules.horarios')
    @else
        @include('modules.citas')
    @endif
@endsection
