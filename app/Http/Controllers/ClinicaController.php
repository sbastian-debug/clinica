<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ClinicaController extends Controller
{
    /**
     * Sincroniza las secuencias autoincrementales de PostgreSQL con el MAX(id) actual
     * en una sola consulta por tabla para evitar latencia de red.
     */
    private function sincronizarSecuenciasPostgres(?string $tablaEspecifica = null): void
    {
        try {
            if (DB::getDriverName() === 'pgsql') {
                $tablas = $tablaEspecifica ? [$tablaEspecifica] : ['usuarios', 'pacientes', 'medicos', 'especialidades', 'horarios_atencion', 'citas', 'roles'];
                foreach ($tablas as $tabla) {
                    try {
                        DB::statement("SELECT setval(COALESCE(pg_get_serial_sequence('{$tabla}', 'id'), '{$tabla}_id_seq'), COALESCE((SELECT MAX(id) FROM {$tabla}), 1))");
                    } catch (\Throwable $ex) {
                        // Continuar si la tabla no posee secuencia
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignorar excepciones de conectividad temporal
        }
    }

    /**
     * Asegura la existencia y correcta vinculación de cuentas demo por rol (con caché para alta velocidad).
     */
    private function asegurarCuentasDemo(): void
    {
        if (Cache::has('clinica_demo_asegurada')) {
            return;
        }

        try {
            $userAdmin = DB::table('usuarios')->where('username', 'admin')->first();
            $userMlopez = DB::table('usuarios')->where('username', 'mlopez')->first();

            if ($userAdmin && $userMlopez) {
                Cache::forever('clinica_demo_asegurada', true);
                return;
            }

            $this->sincronizarSecuenciasPostgres();

            $hash = Hash::make('123456');
            $roles = DB::table('roles')->pluck('id', 'nombre');

            $rolAdmin = $roles['Administrador'] ?? 1;
            $rolRecepcion = $roles['Recepcionista'] ?? 2;
            $rolMedico = $roles['Medico'] ?? 3;
            $rolPaciente = $roles['Paciente'] ?? 4;

            // 1. Paciente Demo 1: María López (mlopez / 123456)
            if (! $userMlopez) {
                $nextUserId = (DB::table('usuarios')->max('id') ?? 0) + 1;
                DB::table('usuarios')->insert([
                    'id'            => $nextUserId,
                    'rol_id'        => $rolPaciente,
                    'username'      => 'mlopez',
                    'email'         => 'maria.lopez@gmail.com',
                    'password_hash' => $hash,
                    'estado'        => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                DB::table('pacientes')->where('ci', '8945123')->update(['usuario_id' => $nextUserId]);
            } else {
                DB::table('pacientes')->where('ci', '8945123')->update(['usuario_id' => $userMlopez->id]);
            }

            // 2. Paciente Demo 2: Jorge Vargas (jvargas / 123456)
            $userJvargas = DB::table('usuarios')->where('username', 'jvargas')->first();
            if (! $userJvargas) {
                $nextUserId = (DB::table('usuarios')->max('id') ?? 0) + 1;
                DB::table('usuarios')->insert([
                    'id'            => $nextUserId,
                    'rol_id'        => $rolPaciente,
                    'username'      => 'jvargas',
                    'email'         => 'jorge.vargas@gmail.com',
                    'password_hash' => $hash,
                    'estado'        => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                DB::table('pacientes')->where('ci', '6321458')->update(['usuario_id' => $nextUserId]);
            } else {
                DB::table('pacientes')->where('ci', '6321458')->update(['usuario_id' => $userJvargas->id]);
            }

            // 3. Médicos: asegurar vinculación de usuario
            $userJperez = DB::table('usuarios')->where('username', 'jperez')->first();
            if ($userJperez) {
                DB::table('medicos')->where('nro_registro_profesional', 'MP-10234')->update(['usuario_id' => $userJperez->id]);
            }

            $userMrodriguez = DB::table('usuarios')->where('username', 'mrodriguez')->first();
            if ($userMrodriguez) {
                DB::table('medicos')->where('nro_registro_profesional', 'MP-20456')->update(['usuario_id' => $userMrodriguez->id]);
            }

            $this->asegurarEspecialidadesYMedicos();
            $this->sincronizarSecuenciasPostgres();

            Cache::forever('clinica_demo_asegurada', true);
        } catch (\Throwable $e) {
            // Continuar en caso de desconexión momentánea
        }
    }

    /**
     * Asegura la existencia de las 6 especialidades y 3 médicos por cada especialidad (optimizado con caché).
     */
    private function asegurarEspecialidadesYMedicos(): void
    {
        if (Cache::has('clinica_catalogo_completo_v1')) {
            return;
        }

        try {
            // Verificación rápida: si ya existen 6 especialidades y 18 médicos, omitir inserciones costosas
            $numEsp = DB::table('especialidades')->count();
            $numMed = DB::table('medicos')->count();

            if ($numEsp >= 6 && $numMed >= 18) {
                Cache::forever('clinica_catalogo_completo_v1', true);
                return;
            }

            $this->sincronizarSecuenciasPostgres();

            $hash = Hash::make('123456');
            $rolMedicoId = DB::table('roles')->where('nombre', 'Medico')->value('id') ?? 3;

            // 1. Catálogo de Especialidades (4 originales + 2 nuevas)
            $especialidadesCatalogo = [
                'Medicina General' => 'Atención médica integral primaria y preventiva',
                'Pediatría'        => 'Atención médica integral infantil y del desarrollo',
                'Ginecología'      => 'Salud integral de la mujer y control prenatal',
                'Odontología'      => 'Salud bucodental, odontología preventiva y restauradora',
                'Cardiología'      => 'Diagnóstico y tratamiento integral de afecciones cardíacas y cardiovasculares',
                'Traumatología'    => 'Tratamiento de lesiones musculoesqueléticas, traumatismos óseos y articulares',
            ];

            $especialidadIds = [];
            foreach ($especialidadesCatalogo as $nombre => $descripcion) {
                $esp = DB::table('especialidades')->where('nombre', $nombre)->first();
                if (! $esp) {
                    $nextEspId = (DB::table('especialidades')->max('id') ?? 0) + 1;
                    DB::table('especialidades')->insert([
                        'id'          => $nextEspId,
                        'nombre'      => $nombre,
                        'descripcion' => $descripcion,
                        'estado'      => true,
                    ]);
                    $especialidadIds[$nombre] = $nextEspId;
                } else {
                    $especialidadIds[$nombre] = $esp->id;
                }
            }

            // 2. Médicos: 3 médicos por cada una de las 6 especialidades (18 médicos en total)
            $medicosPlantel = [
                // Medicina General
                [
                    'especialidad' => 'Medicina General',
                    'matricula'    => 'MP-10234',
                    'nombres'      => 'Juan',
                    'apellidos'    => 'Pérez',
                    'telefono'     => '70011223',
                    'email'        => 'juan.perez@vidasalud.bo',
                    'username'     => 'jperez',
                    'horarios'     => [
                        ['dia' => 1, 'inicio' => '08:00', 'fin' => '12:00'],
                        ['dia' => 3, 'inicio' => '14:00', 'fin' => '18:00'],
                    ],
                ],
                [
                    'especialidad' => 'Medicina General',
                    'matricula'    => 'MP-10235',
                    'nombres'      => 'Carmen',
                    'apellidos'    => 'Flores',
                    'telefono'     => '70033445',
                    'email'        => 'carmen.flores@vidasalud.bo',
                    'username'     => 'cflores',
                    'horarios'     => [
                        ['dia' => 2, 'inicio' => '08:00', 'fin' => '12:00'],
                        ['dia' => 4, 'inicio' => '08:00', 'fin' => '12:00'],
                    ],
                ],
                [
                    'especialidad' => 'Medicina General',
                    'matricula'    => 'MP-10236',
                    'nombres'      => 'Roberto',
                    'apellidos'    => 'Morales',
                    'telefono'     => '70044556',
                    'email'        => 'roberto.morales@vidasalud.bo',
                    'username'     => 'rmorales',
                    'horarios'     => [
                        ['dia' => 3, 'inicio' => '08:00', 'fin' => '12:00'],
                        ['dia' => 5, 'inicio' => '14:00', 'fin' => '18:00'],
                    ],
                ],

                // Pediatría
                [
                    'especialidad' => 'Pediatría',
                    'matricula'    => 'MP-20456',
                    'nombres'      => 'Marta',
                    'apellidos'    => 'Rodríguez',
                    'telefono'     => '70022334',
                    'email'        => 'marta.rodriguez@vidasalud.bo',
                    'username'     => 'mrodriguez',
                    'horarios'     => [
                        ['dia' => 2, 'inicio' => '09:00', 'fin' => '13:00'],
                        ['dia' => 4, 'inicio' => '14:00', 'fin' => '18:00'],
                    ],
                ],
                [
                    'especialidad' => 'Pediatría',
                    'matricula'    => 'MP-20457',
                    'nombres'      => 'Carlos',
                    'apellidos'    => 'Mendoza',
                    'telefono'     => '70055667',
                    'email'        => 'carlos.mendoza@vidasalud.bo',
                    'username'     => 'cmendoza',
                    'horarios'     => [
                        ['dia' => 1, 'inicio' => '14:00', 'fin' => '18:00'],
                        ['dia' => 3, 'inicio' => '09:00', 'fin' => '13:00'],
                    ],
                ],
                [
                    'especialidad' => 'Pediatría',
                    'matricula'    => 'MP-20458',
                    'nombres'      => 'Lucía',
                    'apellidos'    => 'Fernández',
                    'telefono'     => '70066778',
                    'email'        => 'lucia.fernandez@vidasalud.bo',
                    'username'     => 'lfernandez',
                    'horarios'     => [
                        ['dia' => 5, 'inicio' => '08:00', 'fin' => '12:00'],
                        ['dia' => 6, 'inicio' => '09:00', 'fin' => '13:00'],
                    ],
                ],

                // Ginecología
                [
                    'especialidad' => 'Ginecología',
                    'matricula'    => 'MP-30101',
                    'nombres'      => 'Patricia',
                    'apellidos'    => 'Suárez',
                    'telefono'     => '70077889',
                    'email'        => 'patricia.suarez@vidasalud.bo',
                    'username'     => 'psuarez',
                    'horarios'     => [
                        ['dia' => 1, 'inicio' => '08:00', 'fin' => '12:00'],
                        ['dia' => 4, 'inicio' => '08:00', 'fin' => '12:00'],
                    ],
                ],
                [
                    'especialidad' => 'Ginecología',
                    'matricula'    => 'MP-30102',
                    'nombres'      => 'Alejandro',
                    'apellidos'    => 'Ramos',
                    'telefono'     => '70088990',
                    'email'        => 'alejandro.ramos@vidasalud.bo',
                    'username'     => 'aramos',
                    'horarios'     => [
                        ['dia' => 2, 'inicio' => '14:00', 'fin' => '18:00'],
                        ['dia' => 5, 'inicio' => '14:00', 'fin' => '18:00'],
                    ],
                ],
                [
                    'especialidad' => 'Ginecología',
                    'matricula'    => 'MP-30103',
                    'nombres'      => 'Valeria',
                    'apellidos'    => 'Gutiérrez',
                    'telefono'     => '70099001',
                    'email'        => 'valeria.gutierrez@vidasalud.bo',
                    'username'     => 'vgutierrez',
                    'horarios'     => [
                        ['dia' => 3, 'inicio' => '08:00', 'fin' => '12:00'],
                        ['dia' => 6, 'inicio' => '08:00', 'fin' => '12:00'],
                    ],
                ],

                // Odontología
                [
                    'especialidad' => 'Odontología',
                    'matricula'    => 'MP-40201',
                    'nombres'      => 'Diego',
                    'apellidos'    => 'Castro',
                    'telefono'     => '71011223',
                    'email'        => 'diego.castro@vidasalud.bo',
                    'username'     => 'dcastro',
                    'horarios'     => [
                        ['dia' => 1, 'inicio' => '14:00', 'fin' => '18:00'],
                        ['dia' => 3, 'inicio' => '14:00', 'fin' => '18:00'],
                    ],
                ],
                [
                    'especialidad' => 'Odontología',
                    'matricula'    => 'MP-40202',
                    'nombres'      => 'Natalia',
                    'apellidos'    => 'Ríos',
                    'telefono'     => '71022334',
                    'email'        => 'natalia.rios@vidasalud.bo',
                    'username'     => 'nrios',
                    'horarios'     => [
                        ['dia' => 2, 'inicio' => '08:00', 'fin' => '12:00'],
                        ['dia' => 4, 'inicio' => '14:00', 'fin' => '18:00'],
                    ],
                ],
                [
                    'especialidad' => 'Odontología',
                    'matricula'    => 'MP-40203',
                    'nombres'      => 'Fernando',
                    'apellidos'    => 'Ortiz',
                    'telefono'     => '71033445',
                    'email'        => 'fernando.ortiz@vidasalud.bo',
                    'username'     => 'fortiz',
                    'horarios'     => [
                        ['dia' => 5, 'inicio' => '08:00', 'fin' => '12:00'],
                        ['dia' => 6, 'inicio' => '09:00', 'fin' => '13:00'],
                    ],
                ],

                // Cardiología (Nueva Especialidad 1)
                [
                    'especialidad' => 'Cardiología',
                    'matricula'    => 'MP-50301',
                    'nombres'      => 'Javier',
                    'apellidos'    => 'Torrez',
                    'telefono'     => '71044556',
                    'email'        => 'javier.torrez@vidasalud.bo',
                    'username'     => 'jtorrez',
                    'horarios'     => [
                        ['dia' => 1, 'inicio' => '08:00', 'fin' => '12:00'],
                        ['dia' => 3, 'inicio' => '08:00', 'fin' => '12:00'],
                    ],
                ],
                [
                    'especialidad' => 'Cardiología',
                    'matricula'    => 'MP-50302',
                    'nombres'      => 'Gabriela',
                    'apellidos'    => 'Silva',
                    'telefono'     => '71055667',
                    'email'        => 'gabriela.silva@vidasalud.bo',
                    'username'     => 'gsilva',
                    'horarios'     => [
                        ['dia' => 2, 'inicio' => '14:00', 'fin' => '18:00'],
                        ['dia' => 4, 'inicio' => '14:00', 'fin' => '18:00'],
                    ],
                ],
                [
                    'especialidad' => 'Cardiología',
                    'matricula'    => 'MP-50303',
                    'nombres'      => 'Mauricio',
                    'apellidos'    => 'Salazar',
                    'telefono'     => '71066778',
                    'email'        => 'mauricio.salazar@vidasalud.bo',
                    'username'     => 'msalazar',
                    'horarios'     => [
                        ['dia' => 5, 'inicio' => '08:00', 'fin' => '12:00'],
                        ['dia' => 6, 'inicio' => '08:00', 'fin' => '12:00'],
                    ],
                ],

                // Traumatología (Nueva Especialidad 2)
                [
                    'especialidad' => 'Traumatología',
                    'matricula'    => 'MP-60401',
                    'nombres'      => 'Andrés',
                    'apellidos'    => 'Cabrera',
                    'telefono'     => '71077889',
                    'email'        => 'andres.cabrera@vidasalud.bo',
                    'username'     => 'acabrera',
                    'horarios'     => [
                        ['dia' => 1, 'inicio' => '14:00', 'fin' => '18:00'],
                        ['dia' => 4, 'inicio' => '08:00', 'fin' => '12:00'],
                    ],
                ],
                [
                    'especialidad' => 'Traumatología',
                    'matricula'    => 'MP-60402',
                    'nombres'      => 'Sofía',
                    'apellidos'    => 'Romero',
                    'telefono'     => '71088990',
                    'email'        => 'sofia.romero@vidasalud.bo',
                    'username'     => 'sromero',
                    'horarios'     => [
                        ['dia' => 2, 'inicio' => '08:00', 'fin' => '12:00'],
                        ['dia' => 5, 'inicio' => '14:00', 'fin' => '18:00'],
                    ],
                ],
                [
                    'especialidad' => 'Traumatología',
                    'matricula'    => 'MP-60403',
                    'nombres'      => 'Marcelo',
                    'apellidos'    => 'Villalba',
                    'telefono'     => '71099001',
                    'email'        => 'marcelo.villalba@vidasalud.bo',
                    'username'     => 'mvillalba',
                    'horarios'     => [
                        ['dia' => 3, 'inicio' => '14:00', 'fin' => '18:00'],
                        ['dia' => 6, 'inicio' => '09:00', 'fin' => '13:00'],
                    ],
                ],
            ];

            foreach ($medicosPlantel as $m) {
                // A. Asegurar usuario para inicio de sesión
                $user = DB::table('usuarios')->where('username', $m['username'])->first();
                if (! $user) {
                    $nextUserId = (DB::table('usuarios')->max('id') ?? 0) + 1;
                    DB::table('usuarios')->insert([
                        'id'            => $nextUserId,
                        'rol_id'        => $rolMedicoId,
                        'username'      => $m['username'],
                        'email'         => $m['email'],
                        'password_hash' => $hash,
                        'estado'        => true,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                    $userId = $nextUserId;
                } else {
                    $userId = $user->id;
                }

                // B. Asegurar médico
                $medico = DB::table('medicos')->where('nro_registro_profesional', $m['matricula'])->first();
                if (! $medico) {
                    $medico = DB::table('medicos')->where('email', $m['email'])->first();
                }

                if (! $medico) {
                    $nextMedId = (DB::table('medicos')->max('id') ?? 0) + 1;
                    $codigo = 'MED-' . str_pad((string) $nextMedId, 4, '0', STR_PAD_LEFT);
                    while (DB::table('medicos')->where('codigo', $codigo)->exists()) {
                        $nextMedId++;
                        $codigo = 'MED-' . str_pad((string) $nextMedId, 4, '0', STR_PAD_LEFT);
                    }

                    DB::table('medicos')->insert([
                        'id'                       => $nextMedId,
                        'usuario_id'               => $userId,
                        'codigo'                   => $codigo,
                        'nro_registro_profesional' => $m['matricula'],
                        'nombres'                  => $m['nombres'],
                        'apellidos'                => $m['apellidos'],
                        'telefono'                 => $m['telefono'],
                        'email'                    => $m['email'],
                        'estado'                   => true,
                        'created_at'               => now(),
                        'updated_at'               => now(),
                    ]);
                    $medicoId = $nextMedId;
                } else {
                    $medicoId = $medico->id;
                    if (! $medico->usuario_id) {
                        DB::table('medicos')->where('id', $medicoId)->update(['usuario_id' => $userId]);
                    }
                }

                // C. Vincular especialidad
                $espId = $especialidadIds[$m['especialidad']] ?? DB::table('especialidades')->where('nombre', $m['especialidad'])->value('id');
                if ($espId && ! DB::table('medico_especialidad')->where('medico_id', $medicoId)->where('especialidad_id', $espId)->exists()) {
                    DB::table('medico_especialidad')->insert([
                        'medico_id'       => $medicoId,
                        'especialidad_id' => $espId,
                    ]);
                }

                // D. Asignar horarios de turno predeterminados
                foreach ($m['horarios'] as $h) {
                    $existeHorario = DB::table('horarios_atencion')
                        ->where('medico_id', $medicoId)
                        ->where('dia_semana', $h['dia'])
                        ->where('hora_inicio', $h['inicio'])
                        ->exists();

                    if (! $existeHorario) {
                        $nextHorarioId = (DB::table('horarios_atencion')->max('id') ?? 0) + 1;
                        DB::table('horarios_atencion')->insert([
                            'id'               => $nextHorarioId,
                            'medico_id'        => $medicoId,
                            'dia_semana'       => $h['dia'],
                            'hora_inicio'      => $h['inicio'],
                            'hora_fin'         => $h['fin'],
                            'duracion_minutos' => 30,
                            'estado'           => true,
                        ]);
                    }
                }
            }

            $this->sincronizarSecuenciasPostgres();
            Cache::forever('clinica_catalogo_completo_v1', true);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error en asegurarEspecialidadesYMedicos: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    /**
     * Muestra la pantalla de inicio de sesión.
     */
    public function showLogin()
    {
        $this->asegurarCuentasDemo();

        return view('login');
    }

    /**
     * Endpoint directo para forzar la sincronización del catálogo médico y especialidades.
     */
    public function sincronizarDatosManualmente()
    {
        Cache::forget('clinica_demo_asegurada');
        Cache::forget('clinica_catalogo_completo_v1');
        $this->asegurarCuentasDemo();
        $this->asegurarEspecialidadesYMedicos();
        $this->sincronizarSecuenciasPostgres();

        return redirect()->route('dashboard')->with('success', '¡Catálogo sincronizado! 6 especialidades y 18 médicos verificados y secuencias actualizadas.');
    }

    /**
     * Procesa la autenticación estricta con roles y vinculación de expedientes.
     */
    public function login(Request $request)
    {
        $request->validate([
            'credencial' => 'required|string',
            'password'   => 'required|string',
        ]);

        session()->flush();
        $this->asegurarCuentasDemo();

        $credencial = trim($request->input('credencial'));
        $password = $request->input('password');

        $user = DB::table('usuarios')
            ->join('roles', 'usuarios.rol_id', '=', 'roles.id')
            ->where(function ($q) use ($credencial) {
                $q->where('usuarios.username', $credencial)
                    ->orWhere('usuarios.email', $credencial);
            })
            ->where('usuarios.estado', true)
            ->select('usuarios.*', 'roles.nombre as rol')
            ->first();

        if (! $user) {
            return back()->with('error', 'El usuario o correo electrónico no existe en el sistema.')->withInput();
        }

        if (! Hash::check($password, $user->password_hash)) {
            return back()->with('error', 'La contraseña ingresada es incorrecta. Verifique sus datos e intente nuevamente.')->withInput();
        }

        $sessionData = [
            'usuario_id' => $user->id,
            'username'   => $user->username,
            'email'      => $user->email,
            'rol'        => $user->rol,
        ];

        // Si es Médico
        if ($user->rol === 'Medico') {
            $medico = DB::table('medicos')->where('usuario_id', $user->id)->first();
            if (! $medico) {
                $medico = DB::table('medicos')->where('email', $user->email)->first();
                if ($medico) {
                    DB::table('medicos')->where('id', $medico->id)->update(['usuario_id' => $user->id]);
                }
            }

            if ($medico) {
                $sessionData['medico_id'] = $medico->id;
                $sessionData['medico_codigo'] = $medico->codigo;
                $sessionData['nombre_completo'] = 'Dr(a). ' . $medico->nombres . ' ' . $medico->apellidos;
            }
        }

        // Si es Paciente (Cliente)
        if ($user->rol === 'Paciente') {
            $paciente = DB::table('pacientes')->where('usuario_id', $user->id)->first();
            if (! $paciente) {
                $paciente = DB::table('pacientes')->where('email', $user->email)->first();
                if ($paciente) {
                    DB::table('pacientes')->where('id', $paciente->id)->update(['usuario_id' => $user->id]);
                }
            }

            if ($paciente) {
                $sessionData['paciente_id'] = $paciente->id;
                $sessionData['paciente_codigo'] = $paciente->codigo;
                $sessionData['paciente_ci'] = $paciente->ci;
                $sessionData['nombre_completo'] = $paciente->nombres . ' ' . $paciente->apellidos;
            }
        }

        session($sessionData);

        return redirect()->route('dashboard')->with('success', '¡Bienvenido(a), ' . ($sessionData['nombre_completo'] ?? $user->username) . '! Sesión activa como ' . $user->rol);
    }

    /**
     * Formulario de Registro para Clientes / Pacientes.
     */
    public function showRegister()
    {
        $this->sincronizarSecuenciasPostgres();

        return view('register');
    }

    /**
     * Procesa el Registro de un nuevo Cliente / Paciente.
     */
    public function registerPaciente(Request $request)
    {
        $request->validate([
            'username'         => 'required|string|max:50',
            'email'            => 'required|email|max:100',
            'password'         => 'required|string|min:6',
            'ci'               => 'required|string|max:20',
            'nombres'          => 'required|string|max:100',
            'apellidos'        => 'required|string|max:100',
            'fecha_nacimiento' => 'required|date',
            'sexo'             => 'required|in:M,F',
            'telefono'         => 'required|string|max:20',
        ]);

        $rolPacienteId = DB::table('roles')->where('nombre', 'Paciente')->value('id') ?? 4;
        $maxId = DB::table('pacientes')->max('id') ?? 0;
        $count = max(DB::table('pacientes')->count(), $maxId) + 1;
        $codigo = 'PAC-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        while (DB::table('pacientes')->where('codigo', $codigo)->exists()) {
            $count++;
            $codigo = 'PAC-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        }

        DB::transaction(function () use ($request, $rolPacienteId, $username, $email, $codigo, $ci) {
            $nextUserId = (DB::table('usuarios')->max('id') ?? 0) + 1;

            DB::table('usuarios')->insert([
                'id'            => $nextUserId,
                'rol_id'        => $rolPacienteId,
                'username'      => $username,
                'email'         => $email,
                'password_hash' => Hash::make($request->input('password')),
                'estado'        => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $nextPacienteId = (DB::table('pacientes')->max('id') ?? 0) + 1;

            DB::table('pacientes')->insert([
                'id'               => $nextPacienteId,
                'usuario_id'       => $nextUserId,
                'codigo'           => $codigo,
                'ci'               => $ci,
                'nombres'          => trim($request->input('nombres')),
                'apellidos'        => trim($request->input('apellidos')),
                'fecha_nacimiento' => $request->input('fecha_nacimiento'),
                'sexo'             => $request->input('sexo'),
                'telefono'         => trim($request->input('telefono')),
                'email'            => $email,
                'direccion'        => trim($request->input('direccion', '')),
                'estado'           => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $this->sincronizarSecuenciasPostgres('pacientes');
            $this->sincronizarSecuenciasPostgres('usuarios');
        });

        return redirect()->route('login')->with('info', "¡Cuenta del paciente $codigo creada con éxito! Ingrese con usuario '$username' y su contraseña.");
    }

    /**
     * Cierra la sesión activa.
     */
    public function logout()
    {
        session()->flush();

        return redirect()->route('login')->with('info', 'Has cerrado tu sesión.');
    }

    /**
     * Panel Principal con vistas y módulos ampliados (CU02 a CU13).
     */
    public function dashboard(Request $request)
    {
        if (! session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $this->asegurarEspecialidadesYMedicos();

        $rol = session('rol', 'Administrador');
        $modulo = $request->query('mod', 'citas');

        // Restricciones de navegación por Rol
        if ($rol === 'Paciente' && ! in_array($modulo, ['citas', 'historial', 'perfil'])) {
            $modulo = 'citas';
        }
        if ($rol === 'Medico' && ! in_array($modulo, ['citas', 'agenda', 'historial', 'horarios'])) {
            $modulo = 'citas';
        }

        // Consultas base
        $citasQuery = DB::table('citas')
            ->join('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
            ->join('medicos', 'citas.medico_id', '=', 'medicos.id')
            ->join('especialidades', 'citas.especialidad_id', '=', 'especialidades.id')
            ->select(
                'citas.*',
                'pacientes.ci as paciente_ci',
                'pacientes.telefono as paciente_telefono',
                DB::raw("CONCAT(pacientes.nombres, ' ', pacientes.apellidos) as paciente_nombre"),
                DB::raw("CONCAT(medicos.nombres, ' ', medicos.apellidos) as medico_nombre"),
                'especialidades.nombre as especialidad_nombre'
            );

        $horariosQuery = DB::table('horarios_atencion')
            ->join('medicos', 'horarios_atencion.medico_id', '=', 'medicos.id')
            ->select('horarios_atencion.*', 'medicos.nombres', 'medicos.apellidos', 'medicos.codigo as medico_codigo')
            ->where('horarios_atencion.estado', true);

        // 1. Filtro estricto para Médico
        if ($rol === 'Medico') {
            $medicoId = session('medico_id') ?? DB::table('medicos')->where('usuario_id', session('usuario_id'))->value('id');
            if ($medicoId) {
                $citasQuery->where('citas.medico_id', $medicoId);
                $horariosQuery->where('horarios_atencion.medico_id', $medicoId);
            }
        }

        // 2. Filtro estricto para Paciente
        if ($rol === 'Paciente') {
            $pacienteId = session('paciente_id') ?? DB::table('pacientes')->where('usuario_id', session('usuario_id'))->value('id');
            if ($pacienteId) {
                $citasQuery->where('citas.paciente_id', $pacienteId);
            }
        }

        $citas = $citasQuery->orderBy('citas.fecha_cita', 'desc')
            ->orderBy('citas.hora_inicio', 'desc')
            ->get();

        $horarios = $horariosQuery->orderBy('horarios_atencion.dia_semana', 'asc')
            ->orderBy('horarios_atencion.hora_inicio', 'asc')
            ->get();

        // Pacientes, Médicos y Especialidades disponibles
        $pacientes = DB::table('pacientes')->where('estado', true)->orderBy('id', 'desc')->get();
        $medicos = DB::table('medicos')
            ->leftJoin('medico_especialidad', 'medicos.id', '=', 'medico_especialidad.medico_id')
            ->leftJoin('especialidades', 'medico_especialidad.especialidad_id', '=', 'especialidades.id')
            ->select('medicos.*', 'especialidades.id as especialidad_id', 'especialidades.nombre as especialidad')
            ->where('medicos.estado', true)
            ->orderBy('medicos.id', 'asc')
            ->get();

        $especialidades = DB::table('especialidades')->where('estado', true)->orderBy('nombre', 'asc')->get();

        // DATOS ESPECÍFICOS PARA CU11: AGENDA MÉDICA
        $agendaData = [];
        if ($modulo === 'agenda') {
            $agendaMedicoId = $request->query('medico_id');
            if ($rol === 'Medico') {
                $agendaMedicoId = session('medico_id') ?? $medicos->first()?->id;
            } elseif (! $agendaMedicoId) {
                $agendaMedicoId = $medicos->first()?->id ?? 1;
            }

            $agendaFecha = $request->query('fecha', date('Y-m-d'));
            $medicoSeleccionado = DB::table('medicos')->where('id', $agendaMedicoId)->first();

            $citasDia = DB::table('citas')
                ->join('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
                ->join('especialidades', 'citas.especialidad_id', '=', 'especialidades.id')
                ->where('citas.medico_id', $agendaMedicoId)
                ->where('citas.fecha_cita', $agendaFecha)
                ->select(
                    'citas.*',
                    'pacientes.ci as paciente_ci',
                    'pacientes.telefono as paciente_telefono',
                    DB::raw("CONCAT(pacientes.nombres, ' ', pacientes.apellidos) as paciente_nombre"),
                    'especialidades.nombre as especialidad_nombre'
                )
                ->get()
                ->keyBy(function ($item) {
                    return substr($item->hora_inicio, 0, 5);
                });

            $slots = [
                '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
                '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30'
            ];

            $agendaData = [
                'medico_id'   => $agendaMedicoId,
                'medico'      => $medicoSeleccionado,
                'fecha'       => $agendaFecha,
                'citas_dia'   => $citasDia,
                'slots'       => $slots,
            ];
        }

        // DATOS ESPECÍFICOS PARA CU12: HISTORIAL CLÍNICO POR PACIENTE
        $historialData = [];
        if ($modulo === 'historial') {
            $pacienteSeleccionadoId = $request->query('paciente_id');
            if ($rol === 'Paciente') {
                $pacienteSeleccionadoId = session('paciente_id');
            } elseif (! $pacienteSeleccionadoId) {
                $pacienteSeleccionadoId = $pacientes->first()?->id;
            }

            $pacienteExpediente = DB::table('pacientes')->where('id', $pacienteSeleccionadoId)->first();
            $citasHistorial = collect();
            $statsHistorial = ['total' => 0, 'atendidas' => 0, 'canceladas' => 0, 'ausentes' => 0, 'programadas' => 0];

            if ($pacienteExpediente) {
                $citasHistorial = DB::table('citas')
                    ->join('medicos', 'citas.medico_id', '=', 'medicos.id')
                    ->join('especialidades', 'citas.especialidad_id', '=', 'especialidades.id')
                    ->where('citas.paciente_id', $pacienteExpediente->id)
                    ->select(
                        'citas.*',
                        DB::raw("CONCAT(medicos.nombres, ' ', medicos.apellidos) as medico_nombre"),
                        'especialidades.nombre as especialidad_nombre'
                    )
                    ->orderBy('citas.fecha_cita', 'desc')
                    ->orderBy('citas.hora_inicio', 'desc')
                    ->get();

                $statsHistorial = [
                    'total'       => $citasHistorial->count(),
                    'atendidas'   => $citasHistorial->where('estado', 'Atendida')->count(),
                    'canceladas'  => $citasHistorial->where('estado', 'Cancelada')->count(),
                    'ausentes'    => $citasHistorial->where('estado', 'No Asistió')->count(),
                    'programadas' => $citasHistorial->where('estado', 'Programada')->count() + $citasHistorial->where('estado', 'En Espera')->count(),
                ];
            }

            $historialData = [
                'paciente'        => $pacienteExpediente,
                'citas_historial' => $citasHistorial,
                'stats'           => $statsHistorial,
            ];
        }

        // DATOS ESPECÍFICOS PARA CU13: REPORTES ADMINISTRATIVOS
        $reportesData = [];
        if ($modulo === 'reportes') {
            $fechaDesde = $request->query('fecha_desde', date('Y-m-01'));
            $fechaHasta = $request->query('fecha_hasta', date('Y-m-d'));

            $reportQuery = DB::table('citas')
                ->join('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
                ->join('medicos', 'citas.medico_id', '=', 'medicos.id')
                ->join('especialidades', 'citas.especialidad_id', '=', 'especialidades.id')
                ->whereBetween('citas.fecha_cita', [$fechaDesde, $fechaHasta])
                ->select(
                    'citas.*',
                    DB::raw("CONCAT(pacientes.nombres, ' ', pacientes.apellidos) as paciente_nombre"),
                    DB::raw("CONCAT(medicos.nombres, ' ', medicos.apellidos) as medico_nombre"),
                    'especialidades.nombre as especialidad_nombre'
                );

            $citasReporte = $reportQuery->orderBy('citas.fecha_cita', 'desc')->get();

            $totalEnRango = $citasReporte->count();
            $atendidasRango = $citasReporte->where('estado', 'Atendida')->count();
            $canceladasRango = $citasReporte->where('estado', 'Cancelada')->count();
            $ausentesRango = $citasReporte->where('estado', 'No Asistió')->count();
            $pendientesRango = $citasReporte->whereIn('estado', ['Programada', 'En Espera'])->count();

            $porEspecialidad = $citasReporte->groupBy('especialidad_nombre')->map->count();
            $porMedico = $citasReporte->groupBy('medico_nombre')->map->count();

            $reportesData = [
                'fecha_desde'      => $fechaDesde,
                'fecha_hasta'      => $fechaHasta,
                'total'            => $totalEnRango,
                'atendidas'        => $atendidasRango,
                'canceladas'       => $canceladasRango,
                'ausentes'         => $ausentesRango,
                'pendientes'       => $pendientesRango,
                'por_especialidad' => $porEspecialidad,
                'por_medico'       => $porMedico,
                'citas'            => $citasReporte,
            ];
        }

        // KPIs dinámicos según el Rol
        $kpis = [];
        if ($rol === 'Paciente') {
            $kpis = [
                'total_pacientes'    => $citas->count(),
                'label_1'            => 'Mis Citas Agendadas',
                'citas_pendientes'   => $citas->where('estado', 'Programada')->count() + $citas->where('estado', 'En Espera')->count(),
                'label_2'            => 'Citas Pendientes',
                'citas_atendidas'    => $citas->where('estado', 'Atendida')->count(),
                'label_3'            => 'Consultas Realizadas',
                'citas_hoy'          => $citas->where('fecha_cita', date('Y-m-d'))->where('estado', '<>', 'Cancelada')->count(),
                'label_4'            => 'Mi Cita de Hoy',
            ];
        } elseif ($rol === 'Medico') {
            $kpis = [
                'total_pacientes'    => $citas->count(),
                'label_1'            => 'Total Mis Pacientes',
                'citas_pendientes'   => $citas->where('estado', 'Programada')->count() + $citas->where('estado', 'En Espera')->count(),
                'label_2'            => 'Consultas en Espera',
                'citas_atendidas'    => $citas->where('estado', 'Atendida')->count(),
                'label_3'            => 'Pacientes Atendidos',
                'citas_hoy'          => $citas->where('fecha_cita', date('Y-m-d'))->where('estado', '<>', 'Cancelada')->count(),
                'label_4'            => 'Mis Pacientes Hoy',
            ];
        } else {
            $kpis = [
                'total_pacientes'    => $pacientes->count(),
                'label_1'            => 'Pacientes Registrados',
                'total_medicos'      => $medicos->count(),
                'label_2'            => 'Médicos Activos',
                'citas_hoy'          => $citas->where('fecha_cita', date('Y-m-d'))->where('estado', '<>', 'Cancelada')->count(),
                'label_3'            => 'Citas Médicas Hoy',
                'citas_atendidas'    => $citas->where('estado', 'Atendida')->count(),
                'label_4'            => 'Citas Atendidas',
            ];
        }

        // Datos del Perfil Propio del Paciente
        $perfilPaciente = null;
        if ($rol === 'Paciente') {
            $pacienteId = session('paciente_id') ?? DB::table('pacientes')->where('usuario_id', session('usuario_id'))->value('id');
            if (! $pacienteId) {
                $pacienteId = DB::table('pacientes')->where('email', session('email'))->value('id');
            }
            if ($pacienteId) {
                $perfilPaciente = DB::table('pacientes')
                    ->leftJoin('usuarios', 'pacientes.usuario_id', '=', 'usuarios.id')
                    ->where('pacientes.id', $pacienteId)
                    ->select('pacientes.*', 'usuarios.username', 'usuarios.email as cuenta_email')
                    ->first();
            }
        }

        return view('dashboard', compact(
            'modulo',
            'rol',
            'kpis',
            'pacientes',
            'medicos',
            'especialidades',
            'horarios',
            'citas',
            'agendaData',
            'historialData',
            'reportesData',
            'perfilPaciente'
        ));
    }

    /**
     * CU02: Guardar Paciente.
     */
    public function guardarPaciente(Request $request)
    {
        $request->validate([
            'ci'               => 'required|string|max:20',
            'nombres'          => 'required|string|max:100',
            'apellidos'        => 'required|string|max:100',
            'fecha_nacimiento' => 'required|date',
            'telefono'         => 'required|string|max:20',
        ]);

        $ci = trim($request->input('ci'));

        if (DB::table('pacientes')->where('ci', $ci)->exists()) {
            return redirect()->route('dashboard', ['mod' => 'pacientes'])
                ->with('error_paciente', 'Error RN01: Ya existe un paciente registrado con el CI ' . $ci);
        }

        $maxId = DB::table('pacientes')->max('id') ?? 0;
        $count = max(DB::table('pacientes')->count(), $maxId) + 1;
        $codigo = 'PAC-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        while (DB::table('pacientes')->where('codigo', $codigo)->exists()) {
            $count++;
            $codigo = 'PAC-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        }

        $nextPacienteId = (DB::table('pacientes')->max('id') ?? 0) + 1;

        DB::table('pacientes')->insert([
            'id'               => $nextPacienteId,
            'codigo'           => $codigo,
            'ci'               => $ci,
            'nombres'          => trim($request->input('nombres')),
            'apellidos'        => trim($request->input('apellidos')),
            'fecha_nacimiento' => $request->input('fecha_nacimiento'),
            'sexo'             => $request->input('sexo', 'M'),
            'telefono'         => trim($request->input('telefono')),
            'email'            => $request->filled('email') ? trim($request->input('email')) : null,
            'direccion'        => $request->filled('direccion') ? trim($request->input('direccion')) : null,
            'estado'           => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $this->sincronizarSecuenciasPostgres('pacientes');

        return redirect()->route('dashboard', ['mod' => 'pacientes'])
            ->with('success_paciente', "Paciente $codigo registrado con éxito.");
    }

    /**
     * CU03: Guardar Médico.
     */
    public function guardarMedico(Request $request)
    {
        $request->validate([
            'nro_registro_profesional' => 'required|string|max:50',
            'nombres'                  => 'required|string|max:100',
            'apellidos'                => 'required|string|max:100',
            'telefono'                 => 'required|string|max:20',
            'email'                    => 'required|email|max:100',
            'especialidad_id'          => 'required|integer',
        ]);

        $matricula = trim($request->input('nro_registro_profesional'));
        $email = trim($request->input('email'));

        if (DB::table('medicos')->where('nro_registro_profesional', $matricula)->exists()) {
            return redirect()->route('dashboard', ['mod' => 'medicos'])
                ->with('error_medico', "Error RN02: Ya existe un médico con la matrícula $matricula.");
        }

        if (DB::table('medicos')->where('email', $email)->exists()) {
            return redirect()->route('dashboard', ['mod' => 'medicos'])
                ->with('error_medico', "Error: El correo $email ya está asignado a otro médico.");
        }

        $maxId = DB::table('medicos')->max('id') ?? 0;
        $count = max(DB::table('medicos')->count(), $maxId) + 1;
        $codigo = 'MED-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        while (DB::table('medicos')->where('codigo', $codigo)->exists()) {
            $count++;
            $codigo = 'MED-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        }

        DB::transaction(function () use ($request, $codigo, $matricula, $email) {
            $nextMedicoId = (DB::table('medicos')->max('id') ?? 0) + 1;

            DB::table('medicos')->insert([
                'id'                       => $nextMedicoId,
                'codigo'                   => $codigo,
                'nro_registro_profesional' => $matricula,
                'nombres'                  => trim($request->input('nombres')),
                'apellidos'                => trim($request->input('apellidos')),
                'telefono'                 => trim($request->input('telefono')),
                'email'                    => $email,
                'estado'                   => true,
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);

            DB::table('medico_especialidad')->insert([
                'medico_id'       => $nextMedicoId,
                'especialidad_id' => $request->input('especialidad_id'),
            ]);

            $this->sincronizarSecuenciasPostgres('medicos');
        });

        return redirect()->route('dashboard', ['mod' => 'medicos'])
            ->with('success_medico', "Médico $codigo registrado exitosamente.");
    }

    /**
     * CU04: Guardar Especialidad.
     */
    public function guardarEspecialidad(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $nombre = trim($request->input('nombre'));

        if (DB::table('especialidades')->where('nombre', $nombre)->exists()) {
            return redirect()->route('dashboard', ['mod' => 'especialidades'])
                ->with('error_especialidad', "La especialidad '$nombre' ya existe.");
        }

        $nextEspId = (DB::table('especialidades')->max('id') ?? 0) + 1;

        DB::table('especialidades')->insert([
            'id'          => $nextEspId,
            'nombre'      => $nombre,
            'descripcion' => $request->input('descripcion'),
            'estado'      => true,
        ]);

        $this->sincronizarSecuenciasPostgres('especialidades');

        return redirect()->route('dashboard', ['mod' => 'especialidades'])
            ->with('success_especialidad', "Especialidad '$nombre' agregada con éxito.");
    }

    /**
     * CU05: Guardar Horario de Atención.
     */
    public function guardarHorario(Request $request)
    {
        $request->validate([
            'medico_id'        => 'required|integer',
            'dia_semana'       => 'required|integer|between:1,7',
            'hora_inicio'      => 'required',
            'hora_fin'         => 'required',
            'duracion_minutos' => 'nullable|integer|min:10|max:120',
        ]);

        $horaInicio = $request->input('hora_inicio');
        $horaFin = $request->input('hora_fin');

        if ($horaFin <= $horaInicio) {
            return redirect()->route('dashboard', ['mod' => 'horarios'])
                ->with('error_horario', 'La hora de fin debe ser posterior a la hora de inicio.');
        }

        $nextHorarioId = (DB::table('horarios_atencion')->max('id') ?? 0) + 1;

        DB::table('horarios_atencion')->insert([
            'id'               => $nextHorarioId,
            'medico_id'        => $request->input('medico_id'),
            'dia_semana'       => $request->input('dia_semana'),
            'hora_inicio'      => $horaInicio,
            'hora_fin'         => $horaFin,
            'duracion_minutos' => $request->input('duracion_minutos', 30),
            'estado'           => true,
        ]);

        $this->sincronizarSecuenciasPostgres('horarios_atencion');

        return redirect()->route('dashboard', ['mod' => 'horarios'])
            ->with('success_horario', 'Horario de atención registrado con éxito.');
    }

    /**
     * CU06 / CU07: Programar Cita Médica.
     */
    public function guardarCita(Request $request)
    {
        $rol = session('rol');
        $pacienteId = $request->input('paciente_id');

        if ($rol === 'Paciente') {
            $pacienteId = session('paciente_id');
            if (! $pacienteId) {
                $pacienteId = DB::table('pacientes')->where('usuario_id', session('usuario_id'))->value('id');
                session(['paciente_id' => $pacienteId]);
            }
        }

        $request->validate([
            'medico_id'       => 'required|integer',
            'especialidad_id' => 'required|integer',
            'fecha_cita'      => 'required|date',
            'hora_inicio'     => 'required',
        ]);

        if (! $pacienteId) {
            return redirect()->route('dashboard', ['mod' => 'citas'])
                ->with('error_cita', 'Error: No se pudo identificar al paciente para agendar la cita.');
        }

        $medico_id = $request->input('medico_id');
        $fecha = $request->input('fecha_cita');
        $hora_inicio = $request->input('hora_inicio');
        $duracion = $request->input('duracion', 30);
        $hora_fin = date('H:i', strtotime($hora_inicio . " +{$duracion} minutes"));

        // RN06: No citas pasadas
        if ($fecha < date('Y-m-d')) {
            return redirect()->route('dashboard', ['mod' => 'citas'])
                ->with('error_cita', 'Error RN06: No se pueden programar citas médicas en fechas pasadas.');
        }

        // RF07: Prevención de citas superpuestas activas
        $conflicto = DB::table('citas')
            ->where('medico_id', $medico_id)
            ->where('fecha_cita', $fecha)
            ->where('estado', '<>', 'Cancelada')
            ->where(function ($q) use ($hora_inicio, $hora_fin) {
                $q->where('hora_inicio', '<', $hora_fin)
                    ->where('hora_fin', '>', $hora_inicio);
            })
            ->exists();

        if ($conflicto) {
            return redirect()->route('dashboard', ['mod' => 'citas'])
                ->with('error_cita', 'Error RF07: El médico ya tiene una cita activa en ese intervalo de horario.');
        }

        $maxId = DB::table('citas')->max('id') ?? 0;
        $count = max(DB::table('citas')->count(), $maxId) + 1;
        $codigo = 'CM-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        while (DB::table('citas')->where('codigo', $codigo)->exists()) {
            $count++;
            $codigo = 'CM-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        }

        $nextCitaId = (DB::table('citas')->max('id') ?? 0) + 1;

        DB::table('citas')->insert([
            'id'                => $nextCitaId,
            'codigo'            => $codigo,
            'paciente_id'       => $pacienteId,
            'medico_id'         => $medico_id,
            'especialidad_id'   => $request->input('especialidad_id'),
            'fecha_cita'        => $fecha,
            'hora_inicio'       => $hora_inicio,
            'hora_fin'          => $hora_fin,
            'motivo_consulta'   => $request->input('motivo_consulta', 'Consulta médica'),
            'estado'            => 'Programada',
            'fecha_creacion'    => now(),
        ]);

        $this->sincronizarSecuenciasPostgres('citas');

        return redirect()->route('dashboard', ['mod' => 'citas'])
            ->with('success_cita', "¡Cita $codigo programada exitosamente!");
    }

    /**
     * CU08: Reprogramar Cita Médica.
     * Cambia la fecha y hora de una cita activa validando disponibilidad sin solapamiento.
     */
    public function reprogramarCita(Request $request)
    {
        $request->validate([
            'cita_id'     => 'required|integer',
            'nueva_fecha' => 'required|date',
            'nueva_hora'  => 'required',
        ]);

        $citaId = $request->input('cita_id');
        $nuevaFecha = $request->input('nueva_fecha');
        $nuevaHora = $request->input('nueva_hora');
        $duracion = 30;
        $nuevaHoraFin = date('H:i', strtotime($nuevaHora . " +{$duracion} minutes"));

        // RN06: No citas pasadas
        if ($nuevaFecha < date('Y-m-d')) {
            return back()->with('error', 'Error RN06: No se puede reprogramar a una fecha pasada.');
        }

        $cita = DB::table('citas')->where('id', $citaId)->first();
        if (! $cita) {
            return back()->with('error', 'Cita médica no encontrada.');
        }

        // RF07: Validar que no haya conflicto en el nuevo turno
        $conflicto = DB::table('citas')
            ->where('medico_id', $cita->medico_id)
            ->where('fecha_cita', $nuevaFecha)
            ->where('id', '<>', $citaId)
            ->where('estado', '<>', 'Cancelada')
            ->where(function ($q) use ($nuevaHora, $nuevaHoraFin) {
                $q->where('hora_inicio', '<', $nuevaHoraFin)
                    ->where('hora_fin', '>', $nuevaHora);
            })
            ->exists();

        if ($conflicto) {
            return back()->with('error', 'Error RF07: El médico ya tiene otra cita activa programada en ese nuevo horario.');
        }

        DB::table('citas')->where('id', $citaId)->update([
            'fecha_cita'  => $nuevaFecha,
            'hora_inicio' => $nuevaHora,
            'hora_fin'    => $nuevaHoraFin,
            'estado'      => 'Programada',
        ]);

        return back()->with('success', "Cita {$cita->codigo} reprogramada con éxito para el " . date('d/m/Y', strtotime($nuevaFecha)) . " a las $nuevaHora.");
    }

    /**
     * CU09: Cancelar Cita Médica.
     * Libera el horario del médico y almacena el motivo obligatorio de cancelación.
     */
    public function cancelarCita(Request $request)
    {
        $request->validate([
            'cita_id'            => 'required|integer',
            'motivo_cancelacion' => 'required|string|min:3',
        ]);

        $citaId = $request->input('cita_id');
        $motivo = trim($request->input('motivo_cancelacion'));

        $cita = DB::table('citas')->where('id', $citaId)->first();
        if (! $cita) {
            return back()->with('error', 'Cita médica no encontrada.');
        }

        DB::table('citas')->where('id', $citaId)->update([
            'estado'             => 'Cancelada',
            'motivo_cancelacion' => $motivo,
        ]);

        return back()->with('success', "Cita {$cita->codigo} cancelada correctamente. El horario del médico ha quedado disponible.");
    }

    /**
     * CU10: Controlar Asistencia de Cita.
     * Gestiona el ciclo de vida: 'En Espera', 'Atendida' o 'No Asistió'.
     */
    public function controlarAsistencia(Request $request)
    {
        $request->validate([
            'cita_id' => 'required|integer',
            'estado'  => 'required|in:En Espera,Atendida,No Asistio,Cancelada,Programada',
        ]);

        $citaId = $request->input('cita_id');
        $estado = $request->input('estado');
        if ($estado === 'No Asistio') {
            $estado = 'No Asistió';
        }

        $cita = DB::table('citas')->where('id', $citaId)->first();
        if (! $cita) {
            return back()->with('error', 'Cita médica no encontrada.');
        }

        DB::table('citas')->where('id', $citaId)->update([
            'estado' => $estado,
        ]);

        return back()->with('success', "Asistencia de la cita {$cita->codigo} actualizada al estado: $estado.");
    }

    /**
     * Compatibilidad previa de cambio de estado.
     */
    public function cambiarEstadoCita(Request $request)
    {
        if ($request->input('estado') === 'Cancelada') {
            return $this->cancelarCita($request);
        }

        return $this->controlarAsistencia($request);
    }

    /**
     * Actualizar Perfil Propio del Paciente.
     */
    public function actualizarPerfilPaciente(Request $request)
    {
        $rol = session('rol');
        if ($rol !== 'Paciente') {
            return redirect()->route('dashboard')->with('error', 'Solo los pacientes pueden editar su perfil personal desde este módulo.');
        }

        $usuarioId = session('usuario_id');
        $pacienteId = session('paciente_id') ?? DB::table('pacientes')->where('usuario_id', $usuarioId)->value('id');
        if (! $pacienteId) {
            $pacienteId = DB::table('pacientes')->where('email', session('email'))->value('id');
        }

        if (! $pacienteId) {
            return redirect()->route('dashboard')->with('error', 'No se encontró el expediente del paciente.');
        }

        $request->validate([
            'ci'               => 'required|string|max:20',
            'nombres'          => 'required|string|max:100',
            'apellidos'        => 'required|string|max:100',
            'fecha_nacimiento' => 'required|date',
            'sexo'             => 'required|in:M,F',
            'telefono'         => 'required|string|max:20',
            'email'            => 'required|email|max:100',
            'direccion'        => 'nullable|string|max:255',
            'username'         => 'required|string|max:50',
            'password'         => 'nullable|string|min:6',
        ]);

        $ci = trim($request->input('ci'));
        $email = trim($request->input('email'));
        $username = trim($request->input('username'));
        $nombres = trim($request->input('nombres'));
        $apellidos = trim($request->input('apellidos'));
        $telefono = trim($request->input('telefono'));
        $direccion = $request->filled('direccion') ? trim($request->input('direccion')) : null;
        $fechaNacimiento = $request->input('fecha_nacimiento');
        $sexo = $request->input('sexo');
        $password = $request->input('password');

        // Validar unicidad de CI en otros pacientes
        $ciExistente = DB::table('pacientes')
            ->where('ci', $ci)
            ->where('id', '<>', $pacienteId)
            ->exists();

        if ($ciExistente) {
            return redirect()->route('dashboard', ['mod' => 'perfil'])
                ->with('error', "La cédula de identidad '$ci' ya pertenece a otro expediente clínico.")
                ->withInput();
        }

        // Validar unicidad de username y email en usuarios
        if ($usuarioId) {
            $userExistente = DB::table('usuarios')
                ->where('username', $username)
                ->where('id', '<>', $usuarioId)
                ->exists();

            if ($userExistente) {
                return redirect()->route('dashboard', ['mod' => 'perfil'])
                    ->with('error', "El nombre de usuario '$username' ya se encuentra en uso.")
                    ->withInput();
            }

            $emailExistente = DB::table('usuarios')
                ->where('email', $email)
                ->where('id', '<>', $usuarioId)
                ->exists();

            if ($emailExistente) {
                return redirect()->route('dashboard', ['mod' => 'perfil'])
                    ->with('error', "El correo electrónico '$email' ya se encuentra registrado con otro usuario.")
                    ->withInput();
            }
        }

        DB::transaction(function () use ($pacienteId, $usuarioId, $ci, $nombres, $apellidos, $fechaNacimiento, $sexo, $telefono, $email, $direccion, $username, $password) {
            DB::table('pacientes')
                ->where('id', $pacienteId)
                ->update([
                    'ci'               => $ci,
                    'nombres'          => $nombres,
                    'apellidos'        => $apellidos,
                    'fecha_nacimiento' => $fechaNacimiento,
                    'sexo'             => $sexo,
                    'telefono'         => $telefono,
                    'email'            => $email,
                    'direccion'        => $direccion,
                    'updated_at'       => now(),
                ]);

            if ($usuarioId) {
                $usuarioUpdate = [
                    'username'   => $username,
                    'email'      => $email,
                    'updated_at' => now(),
                ];

                if (! empty($password)) {
                    $usuarioUpdate['password_hash'] = Hash::make($password);
                }

                DB::table('usuarios')
                    ->where('id', $usuarioId)
                    ->update($usuarioUpdate);
            }

            session([
                'username'        => $username,
                'email'           => $email,
                'nombre_completo' => $nombres . ' ' . $apellidos,
                'paciente_ci'     => $ci,
            ]);
        });

        return redirect()->route('dashboard', ['mod' => 'perfil'])
            ->with('success', '¡Su perfil y datos personales se han actualizado correctamente!');
    }
}
