<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $hash = Hash::make('123456');

        // 1. Roles
        $roles = [
            ['id' => 1, 'nombre' => 'Administrador'],
            ['id' => 2, 'nombre' => 'Recepcionista'],
            ['id' => 3, 'nombre' => 'Medico'],
            ['id' => 4, 'nombre' => 'Paciente'],
        ];
        foreach ($roles as $r) {
            if (! DB::table('roles')->where('id', $r['id'])->exists()) {
                DB::table('roles')->insert($r);
            }
        }

        // 2. Especialidades (6 especialidades: 4 base + 2 nuevas)
        $especialidades = [
            ['id' => 1, 'nombre' => 'Medicina General', 'descripcion' => 'Atención médica integral primaria', 'estado' => true],
            ['id' => 2, 'nombre' => 'Pediatría', 'descripcion' => 'Atención médica infantil y del desarrollo', 'estado' => true],
            ['id' => 3, 'nombre' => 'Ginecología', 'descripcion' => 'Salud integral de la mujer y maternidad', 'estado' => true],
            ['id' => 4, 'nombre' => 'Odontología', 'descripcion' => 'Salud bucodental y odontología preventiva', 'estado' => true],
            ['id' => 5, 'nombre' => 'Cardiología', 'descripcion' => 'Diagnóstico y tratamiento integral de afecciones cardíacas y cardiovasculares', 'estado' => true],
            ['id' => 6, 'nombre' => 'Traumatología', 'descripcion' => 'Tratamiento de lesiones musculoesqueléticas, óseas y articulares', 'estado' => true],
        ];
        foreach ($especialidades as $esp) {
            if (! DB::table('especialidades')->where('id', $esp['id'])->exists()) {
                DB::table('especialidades')->insert($esp);
            }
        }

        // 3. Usuarios base
        $usuariosBase = [
            ['id' => 1, 'rol_id' => 1, 'username' => 'admin', 'email' => 'admin@vidasalud.bo', 'password_hash' => $hash, 'estado' => true],
            ['id' => 2, 'rol_id' => 2, 'username' => 'recepcion', 'email' => 'recepcion@vidasalud.bo', 'password_hash' => $hash, 'estado' => true],
        ];
        foreach ($usuariosBase as $u) {
            if (! DB::table('usuarios')->where('username', $u['username'])->exists()) {
                DB::table('usuarios')->insert($u);
            }
        }

        // 4. Médicos: 3 médicos por cada una de las 6 especialidades (18 médicos en total)
        $medicosData = [
            // Medicina General (id: 1)
            [
                'id' => 1, 'esp_id' => 1, 'user_id' => 3, 'username' => 'jperez', 'codigo' => 'MED-0001',
                'matricula' => 'MP-10234', 'nombres' => 'Juan', 'apellidos' => 'Pérez', 'telefono' => '70011223',
                'email' => 'juan.perez@vidasalud.bo',
                'horarios' => [
                    ['dia' => 1, 'inicio' => '08:00', 'fin' => '12:00'],
                    ['dia' => 3, 'inicio' => '14:00', 'fin' => '18:00'],
                ],
            ],
            [
                'id' => 3, 'esp_id' => 1, 'user_id' => 5, 'username' => 'cflores', 'codigo' => 'MED-0003',
                'matricula' => 'MP-10235', 'nombres' => 'Carmen', 'apellidos' => 'Flores', 'telefono' => '70033445',
                'email' => 'carmen.flores@vidasalud.bo',
                'horarios' => [
                    ['dia' => 2, 'inicio' => '08:00', 'fin' => '12:00'],
                    ['dia' => 4, 'inicio' => '08:00', 'fin' => '12:00'],
                ],
            ],
            [
                'id' => 4, 'esp_id' => 1, 'user_id' => 6, 'username' => 'rmorales', 'codigo' => 'MED-0004',
                'matricula' => 'MP-10236', 'nombres' => 'Roberto', 'apellidos' => 'Morales', 'telefono' => '70044556',
                'email' => 'roberto.morales@vidasalud.bo',
                'horarios' => [
                    ['dia' => 3, 'inicio' => '08:00', 'fin' => '12:00'],
                    ['dia' => 5, 'inicio' => '14:00', 'fin' => '18:00'],
                ],
            ],

            // Pediatría (id: 2)
            [
                'id' => 2, 'esp_id' => 2, 'user_id' => 4, 'username' => 'mrodriguez', 'codigo' => 'MED-0002',
                'matricula' => 'MP-20456', 'nombres' => 'Marta', 'apellidos' => 'Rodríguez', 'telefono' => '70022334',
                'email' => 'marta.rodriguez@vidasalud.bo',
                'horarios' => [
                    ['dia' => 2, 'inicio' => '09:00', 'fin' => '13:00'],
                    ['dia' => 4, 'inicio' => '14:00', 'fin' => '18:00'],
                ],
            ],
            [
                'id' => 5, 'esp_id' => 2, 'user_id' => 7, 'username' => 'cmendoza', 'codigo' => 'MED-0005',
                'matricula' => 'MP-20457', 'nombres' => 'Carlos', 'apellidos' => 'Mendoza', 'telefono' => '70055667',
                'email' => 'carlos.mendoza@vidasalud.bo',
                'horarios' => [
                    ['dia' => 1, 'inicio' => '14:00', 'fin' => '18:00'],
                    ['dia' => 3, 'inicio' => '09:00', 'fin' => '13:00'],
                ],
            ],
            [
                'id' => 6, 'esp_id' => 2, 'user_id' => 8, 'username' => 'lfernandez', 'codigo' => 'MED-0006',
                'matricula' => 'MP-20458', 'nombres' => 'Lucía', 'apellidos' => 'Fernández', 'telefono' => '70066778',
                'email' => 'lucia.fernandez@vidasalud.bo',
                'horarios' => [
                    ['dia' => 5, 'inicio' => '08:00', 'fin' => '12:00'],
                    ['dia' => 6, 'inicio' => '09:00', 'fin' => '13:00'],
                ],
            ],

            // Ginecología (id: 3)
            [
                'id' => 7, 'esp_id' => 3, 'user_id' => 9, 'username' => 'psuarez', 'codigo' => 'MED-0007',
                'matricula' => 'MP-30101', 'nombres' => 'Patricia', 'apellidos' => 'Suárez', 'telefono' => '70077889',
                'email' => 'patricia.suarez@vidasalud.bo',
                'horarios' => [
                    ['dia' => 1, 'inicio' => '08:00', 'fin' => '12:00'],
                    ['dia' => 4, 'inicio' => '08:00', 'fin' => '12:00'],
                ],
            ],
            [
                'id' => 8, 'esp_id' => 3, 'user_id' => 10, 'username' => 'aramos', 'codigo' => 'MED-0008',
                'matricula' => 'MP-30102', 'nombres' => 'Alejandro', 'apellidos' => 'Ramos', 'telefono' => '70088990',
                'email' => 'alejandro.ramos@vidasalud.bo',
                'horarios' => [
                    ['dia' => 2, 'inicio' => '14:00', 'fin' => '18:00'],
                    ['dia' => 5, 'inicio' => '14:00', 'fin' => '18:00'],
                ],
            ],
            [
                'id' => 9, 'esp_id' => 3, 'user_id' => 11, 'username' => 'vgutierrez', 'codigo' => 'MED-0009',
                'matricula' => 'MP-30103', 'nombres' => 'Valeria', 'apellidos' => 'Gutiérrez', 'telefono' => '70099001',
                'email' => 'valeria.gutierrez@vidasalud.bo',
                'horarios' => [
                    ['dia' => 3, 'inicio' => '08:00', 'fin' => '12:00'],
                    ['dia' => 6, 'inicio' => '08:00', 'fin' => '12:00'],
                ],
            ],

            // Odontología (id: 4)
            [
                'id' => 10, 'esp_id' => 4, 'user_id' => 12, 'username' => 'dcastro', 'codigo' => 'MED-0010',
                'matricula' => 'MP-40201', 'nombres' => 'Diego', 'apellidos' => 'Castro', 'telefono' => '71011223',
                'email' => 'diego.castro@vidasalud.bo',
                'horarios' => [
                    ['dia' => 1, 'inicio' => '14:00', 'fin' => '18:00'],
                    ['dia' => 3, 'inicio' => '14:00', 'fin' => '18:00'],
                ],
            ],
            [
                'id' => 11, 'esp_id' => 4, 'user_id' => 13, 'username' => 'nrios', 'codigo' => 'MED-0011',
                'matricula' => 'MP-40202', 'nombres' => 'Natalia', 'apellidos' => 'Ríos', 'telefono' => '71022334',
                'email' => 'natalia.rios@vidasalud.bo',
                'horarios' => [
                    ['dia' => 2, 'inicio' => '08:00', 'fin' => '12:00'],
                    ['dia' => 4, 'inicio' => '14:00', 'fin' => '18:00'],
                ],
            ],
            [
                'id' => 12, 'esp_id' => 4, 'user_id' => 14, 'username' => 'fortiz', 'codigo' => 'MED-0012',
                'matricula' => 'MP-40203', 'nombres' => 'Fernando', 'apellidos' => 'Ortiz', 'telefono' => '71033445',
                'email' => 'fernando.ortiz@vidasalud.bo',
                'horarios' => [
                    ['dia' => 5, 'inicio' => '08:00', 'fin' => '12:00'],
                    ['dia' => 6, 'inicio' => '09:00', 'fin' => '13:00'],
                ],
            ],

            // Cardiología (id: 5 - Nueva Especialidad)
            [
                'id' => 13, 'esp_id' => 5, 'user_id' => 15, 'username' => 'jtorrez', 'codigo' => 'MED-0013',
                'matricula' => 'MP-50301', 'nombres' => 'Javier', 'apellidos' => 'Torrez', 'telefono' => '71044556',
                'email' => 'javier.torrez@vidasalud.bo',
                'horarios' => [
                    ['dia' => 1, 'inicio' => '08:00', 'fin' => '12:00'],
                    ['dia' => 3, 'inicio' => '08:00', 'fin' => '12:00'],
                ],
            ],
            [
                'id' => 14, 'esp_id' => 5, 'user_id' => 16, 'username' => 'gsilva', 'codigo' => 'MED-0014',
                'matricula' => 'MP-50302', 'nombres' => 'Gabriela', 'apellidos' => 'Silva', 'telefono' => '71055667',
                'email' => 'gabriela.silva@vidasalud.bo',
                'horarios' => [
                    ['dia' => 2, 'inicio' => '14:00', 'fin' => '18:00'],
                    ['dia' => 4, 'inicio' => '14:00', 'fin' => '18:00'],
                ],
            ],
            [
                'id' => 15, 'esp_id' => 5, 'user_id' => 17, 'username' => 'msalazar', 'codigo' => 'MED-0015',
                'matricula' => 'MP-50303', 'nombres' => 'Mauricio', 'apellidos' => 'Salazar', 'telefono' => '71066778',
                'email' => 'mauricio.salazar@vidasalud.bo',
                'horarios' => [
                    ['dia' => 5, 'inicio' => '08:00', 'fin' => '12:00'],
                    ['dia' => 6, 'inicio' => '08:00', 'fin' => '12:00'],
                ],
            ],

            // Traumatología (id: 6 - Nueva Especialidad)
            [
                'id' => 16, 'esp_id' => 6, 'user_id' => 18, 'username' => 'acabrera', 'codigo' => 'MED-0016',
                'matricula' => 'MP-60401', 'nombres' => 'Andrés', 'apellidos' => 'Cabrera', 'telefono' => '71077889',
                'email' => 'andres.cabrera@vidasalud.bo',
                'horarios' => [
                    ['dia' => 1, 'inicio' => '14:00', 'fin' => '18:00'],
                    ['dia' => 4, 'inicio' => '08:00', 'fin' => '12:00'],
                ],
            ],
            [
                'id' => 17, 'esp_id' => 6, 'user_id' => 19, 'username' => 'sromero', 'codigo' => 'MED-0017',
                'matricula' => 'MP-60402', 'nombres' => 'Sofía', 'apellidos' => 'Romero', 'telefono' => '71088990',
                'email' => 'sofia.romero@vidasalud.bo',
                'horarios' => [
                    ['dia' => 2, 'inicio' => '08:00', 'fin' => '12:00'],
                    ['dia' => 5, 'inicio' => '14:00', 'fin' => '18:00'],
                ],
            ],
            [
                'id' => 18, 'esp_id' => 6, 'user_id' => 20, 'username' => 'mvillalba', 'codigo' => 'MED-0018',
                'matricula' => 'MP-60403', 'nombres' => 'Marcelo', 'apellidos' => 'Villalba', 'telefono' => '71099001',
                'email' => 'marcelo.villalba@vidasalud.bo',
                'horarios' => [
                    ['dia' => 3, 'inicio' => '14:00', 'fin' => '18:00'],
                    ['dia' => 6, 'inicio' => '09:00', 'fin' => '13:00'],
                ],
            ],
        ];

        foreach ($medicosData as $m) {
            // Usuario del médico
            if (! DB::table('usuarios')->where('username', $m['username'])->exists()) {
                DB::table('usuarios')->insert([
                    'id'            => $m['user_id'],
                    'rol_id'        => 3,
                    'username'      => $m['username'],
                    'email'         => $m['email'],
                    'password_hash' => $hash,
                    'estado'        => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // Registro en médicos
            if (! DB::table('medicos')->where('nro_registro_profesional', $m['matricula'])->exists()) {
                DB::table('medicos')->insert([
                    'id'                       => $m['id'],
                    'usuario_id'               => $m['user_id'],
                    'codigo'                   => $m['codigo'],
                    'nro_registro_profesional' => $m['matricula'],
                    'nombres'                  => $m['nombres'],
                    'apellidos'                => $m['apellidos'],
                    'telefono'                 => $m['telefono'],
                    'email'                    => $m['email'],
                    'estado'                   => true,
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ]);
            }

            // Vinculación en medico_especialidad
            if (! DB::table('medico_especialidad')->where('medico_id', $m['id'])->where('especialidad_id', $m['esp_id'])->exists()) {
                DB::table('medico_especialidad')->insert([
                    'medico_id'       => $m['id'],
                    'especialidad_id' => $m['esp_id'],
                ]);
            }

            // Horarios de atención
            foreach ($m['horarios'] as $h) {
                if (! DB::table('horarios_atencion')->where('medico_id', $m['id'])->where('dia_semana', $h['dia'])->where('hora_inicio', $h['inicio'])->exists()) {
                    DB::table('horarios_atencion')->insert([
                        'medico_id'        => $m['id'],
                        'dia_semana'       => $h['dia'],
                        'hora_inicio'      => $h['inicio'],
                        'hora_fin'         => $h['fin'],
                        'duracion_minutos' => 30,
                        'estado'           => true,
                    ]);
                }
            }
        }

        // 5. Pacientes
        $pacientes = [
            ['id' => 1, 'codigo' => 'PAC-0001', 'ci' => '8945123', 'nombres' => 'María', 'apellidos' => 'López', 'fecha_nacimiento' => '1995-04-12', 'sexo' => 'F', 'telefono' => '75011122', 'email' => 'maria.lopez@gmail.com', 'direccion' => 'Av. Beni #120', 'estado' => true],
            ['id' => 2, 'codigo' => 'PAC-0002', 'ci' => '6321458', 'nombres' => 'Jorge', 'apellidos' => 'Vargas', 'fecha_nacimiento' => '1988-11-23', 'sexo' => 'M', 'telefono' => '76022233', 'email' => 'jorge.vargas@gmail.com', 'direccion' => 'Calle Sucre #45', 'estado' => true],
        ];
        foreach ($pacientes as $p) {
            if (! DB::table('pacientes')->where('id', $p['id'])->exists()) {
                DB::table('pacientes')->insert($p);
            }
        }

        // 6. Cita de ejemplo
        if (! DB::table('citas')->where('codigo', 'CM-0001')->exists()) {
            DB::table('citas')->insert([
                'codigo'            => 'CM-0001',
                'paciente_id'       => 1,
                'medico_id'         => 1,
                'especialidad_id'   => 1,
                'fecha_cita'        => '2026-11-16',
                'hora_inicio'       => '09:00',
                'hora_fin'          => '09:30',
                'motivo_consulta'   => 'Consulta general',
                'estado'            => 'Programada',
            ]);
        }
    }
}
