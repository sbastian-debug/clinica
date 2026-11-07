<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClinicaController;

// Autenticación y Registro (CU01)
Route::get('/', [ClinicaController::class, 'showLogin'])->name('login');
Route::post('/login', [ClinicaController::class, 'login'])->name('login.post');
Route::get('/logout', [ClinicaController::class, 'logout'])->name('logout');

Route::get('/registro', [ClinicaController::class, 'showRegister'])->name('register');
Route::post('/registro', [ClinicaController::class, 'registerPaciente'])->name('register.post');

// Panel Principal
Route::get('/dashboard', [ClinicaController::class, 'dashboard'])->name('dashboard');
Route::get('/sincronizar-datos', [ClinicaController::class, 'sincronizarDatosManualmente'])->name('sincronizar');

// Módulo Pacientes (CU02)
Route::post('/pacientes/guardar', [ClinicaController::class, 'guardarPaciente'])->name('pacientes.store');

// Módulo Médicos (CU03)
Route::post('/medicos/guardar', [ClinicaController::class, 'guardarMedico'])->name('medicos.store');

// Módulo Especialidades (CU04)
Route::post('/especialidades/guardar', [ClinicaController::class, 'guardarEspecialidad'])->name('especialidades.store');

// Módulo Horarios (CU05)
Route::post('/horarios/guardar', [ClinicaController::class, 'guardarHorario'])->name('horarios.store');

// Módulo Citas Médicas (CU06 / CU07)
Route::post('/citas/guardar', [ClinicaController::class, 'guardarCita'])->name('citas.store');
Route::post('/citas/cambiar-estado', [ClinicaController::class, 'cambiarEstadoCita'])->name('citas.cambiarEstado');

// CU08: Reprogramar Cita Médica
Route::post('/citas/reprogramar', [ClinicaController::class, 'reprogramarCita'])->name('citas.reprogramar');

// CU09: Cancelar Cita Médica
Route::post('/citas/cancelar', [ClinicaController::class, 'cancelarCita'])->name('citas.cancelar');

// CU10: Controlar Asistencia de Cita (En Espera, Atendida, No Asistió)
Route::post('/citas/controlar-asistencia', [ClinicaController::class, 'controlarAsistencia'])->name('citas.controlarAsistencia');

// Perfil de Paciente (Edición y Actualización de Información)
Route::post('/perfil/actualizar', [ClinicaController::class, 'actualizarPerfilPaciente'])->name('perfil.actualizar');
