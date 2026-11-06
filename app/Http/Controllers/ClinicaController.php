<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClinicaController extends Controller
{
    /**
     * Asegura la existencia y correcta vinculación de cuentas demo por rol.
     */
    private function asegurarCuentasDemo(): void
    {
        try {
            $hash = Hash::make('123456');
            $roles = DB::table('roles')->pluck('id', 'nombre');

            $rolAdmin = $roles['Administrador'] ?? 1;
            $rolRecepcion = $roles['Recepcionista'] ?? 2;
            $rolMedico = $roles['Medico'] ?? 3;
            $rolPaciente = $roles['Paciente'] ?? 4;

            // 1. Paciente Demo 1: María López (mlopez / 123456)
            $userMlopez = DB::table('usuarios')->where('username', 'mlopez')->first();
            if (! $userMlopez) {
                $userMlopezId = DB::table('usuarios')->insertGetId([
                    'rol_id'        => $rolPaciente,
                    'username'      => 'mlopez',
                    'email'         => 'maria.lopez@gmail.com',
                    'password_hash' => $hash,
                    'estado'        => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                DB::table('pacientes')->where('ci', '8945123')->update(['usuario_id' => $userMlopezId]);
            } else {
                DB::table('pacientes')->where('ci', '8945123')->update(['usuario_id' => $userMlopez->id]);
            }

            // 2. Paciente Demo 2: Jorge Vargas (jvargas / 123456)
            $userJvargas = DB::table('usuarios')->where('username', 'jvargas')->first();
            if (! $userJvargas) {
                $userJvargasId = DB::table('usuarios')->insertGetId([
                    'rol_id'        => $rolPaciente,
                    'username'      => 'jvargas',
                    'email'         => 'jorge.vargas@gmail.com',
                    'password_hash' => $hash,
                    'estado'        => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                DB::table('pacientes')->where('ci', '6321458')->update(['usuario_id' => $userJvargasId]);
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
        } catch (\Throwable $e) {
            // Continuar en caso de desconexión momentánea
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

        $username = trim($request->input('username'));
        $email = trim($request->input('email'));
        $ci = trim($request->input('ci'));

        if (DB::table('usuarios')->where('username', $username)->exists()) {
            return back()->with('error', "El nombre de usuario '$username' ya se encuentra registrado.")->withInput();
        }

        if (DB::table('usuarios')->where('email', $email)->exists()) {
            return back()->with('error', "El correo electrónico '$email' ya se encuentra en uso.")->withInput();
        }

        if (DB::table('pacientes')->where('ci', $ci)->exists()) {
            return back()->with('error', "Ya existe un expediente clínico registrado con el CI $ci.")->withInput();
        }

        $rolPacienteId = DB::table('roles')->where('nombre', 'Paciente')->value('id') ?? 4;
        $count = DB::table('pacientes')->count() + 1;
        $codigo = 'PAC-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($request, $rolPacienteId, $username, $email, $codigo, $ci) {
            $newUserId = DB::table('usuarios')->insertGetId([
                'rol_id'        => $rolPacienteId,
                'username'      => $username,
                'email'         => $email,
                'password_hash' => Hash::make($request->input('password')),
                'estado'        => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            DB::table('pacientes')->insert([
                'usuario_id'       => $newUserId,
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

        $rol = session('rol', 'Administrador');
        $modulo = $request->query('mod', 'citas');

        // Restricciones de navegación por Rol
        if ($rol === 'Paciente' && ! in_array($modulo, ['citas', 'historial'])) {
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

            // Citas del médico para ese día
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

            // Franjas horarias del consultorio
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

            // Estadísticas por estado
            $totalEnRango = $citasReporte->count();
            $atendidasRango = $citasReporte->where('estado', 'Atendida')->count();
            $canceladasRango = $citasReporte->where('estado', 'Cancelada')->count();
            $ausentesRango = $citasReporte->where('estado', 'No Asistió')->count();
            $pendientesRango = $citasReporte->whereIn('estado', ['Programada', 'En Espera'])->count();

            // Agrupado por Especialidad
            $porEspecialidad = $citasReporte->groupBy('especialidad_nombre')->map->count();

            // Agrupado por Médico
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
                'total_pacientes'    => DB::table('pacientes')->where('estado', true)->count(),
                'label_1'            => 'Pacientes Registrados',
                'total_medicos'      => DB::table('medicos')->where('estado', true)->count(),
                'label_2'            => 'Médicos Activos',
                'citas_hoy'          => DB::table('citas')->where('fecha_cita', date('Y-m-d'))->where('estado', '<>', 'Cancelada')->count(),
                'label_3'            => 'Citas Médicas Hoy',
                'citas_atendidas'    => DB::table('citas')->where('estado', 'Atendida')->count(),
                'label_4'            => 'Citas Atendidas',
            ];
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
            'reportesData'
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
            return redirect('dashboard?mod=pacientes')
                ->with('error_paciente', 'Error RN01: Ya existe un paciente registrado con el CI ' . $ci);
        }

        $count = DB::table('pacientes')->count() + 1;
        $codigo = 'PAC-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);

        DB::table('pacientes')->insert([
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

        return redirect('dashboard?mod=pacientes')
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
            return redirect('dashboard?mod=medicos')
                ->with('error_medico', "Error RN02: Ya existe un médico con la matrícula $matricula.");
        }

        if (DB::table('medicos')->where('email', $email)->exists()) {
            return redirect('dashboard?mod=medicos')
                ->with('error_medico', "Error: El correo $email ya está asignado a otro médico.");
        }

        $count = DB::table('medicos')->count() + 1;
        $codigo = 'MED-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($request, $codigo, $matricula, $email) {
            $medicoId = DB::table('medicos')->insertGetId([
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
                'medico_id'       => $medicoId,
                'especialidad_id' => $request->input('especialidad_id'),
            ]);
        });

        return redirect('dashboard?mod=medicos')
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
            return redirect('dashboard?mod=especialidades')
                ->with('error_especialidad', "La especialidad '$nombre' ya existe.");
        }

        DB::table('especialidades')->insert([
            'nombre'      => $nombre,
            'descripcion' => $request->input('descripcion'),
            'estado'      => true,
        ]);

        return redirect('dashboard?mod=especialidades')
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
            return redirect('dashboard?mod=horarios')
                ->with('error_horario', 'La hora de fin debe ser posterior a la hora de inicio.');
        }

        DB::table('horarios_atencion')->insert([
            'medico_id'        => $request->input('medico_id'),
            'dia_semana'       => $request->input('dia_semana'),
            'hora_inicio'      => $horaInicio,
            'hora_fin'         => $horaFin,
            'duracion_minutos' => $request->input('duracion_minutos', 30),
            'estado'           => true,
        ]);

        return redirect('dashboard?mod=horarios')
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
            return redirect('dashboard?mod=citas')
                ->with('error_cita', 'Error: No se pudo identificar al paciente para agendar la cita.');
        }

        $medico_id = $request->input('medico_id');
        $fecha = $request->input('fecha_cita');
        $hora_inicio = $request->input('hora_inicio');
        $duracion = $request->input('duracion', 30);
        $hora_fin = date('H:i', strtotime($hora_inicio . " +{$duracion} minutes"));

        // RN06: No citas pasadas
        if ($fecha < date('Y-m-d')) {
            return redirect('dashboard?mod=citas')
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
            return redirect('dashboard?mod=citas')
                ->with('error_cita', 'Error RF07: El médico ya tiene una cita activa en ese intervalo de horario.');
        }

        $count = DB::table('citas')->count() + 1;
        $codigo = 'CM-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);

        DB::table('citas')->insert([
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

        return redirect('dashboard?mod=citas')
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
}
