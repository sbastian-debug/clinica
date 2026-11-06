<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        DB::table('roles')->insert([
            ['id' => 1, 'nombre' => 'Administrador'],
            ['id' => 2, 'nombre' => 'Recepcionista'],
            ['id' => 3, 'nombre' => 'Medico'],
            ['id' => 4, 'nombre' => 'Paciente'],
        ]);

        // 2. Usuarios (Contraseña para todos: 123456)
        $hash = Hash::make('123456');
        DB::table('usuarios')->insert([
            ['rol_id' => 1, 'username' => 'admin', 'email' => 'admin@vidasalud.bo', 'password_hash' => $hash, 'estado' => true],
            ['rol_id' => 2, 'username' => 'recepcion', 'email' => 'recepcion@vidasalud.bo', 'password_hash' => $hash, 'estado' => true],
            ['rol_id' => 3, 'username' => 'jperez', 'email' => 'juan.perez@vidasalud.bo', 'password_hash' => $hash, 'estado' => true],
            ['rol_id' => 3, 'username' => 'mrodriguez', 'email' => 'marta.rodriguez@vidasalud.bo', 'password_hash' => $hash, 'estado' => true],
        ]);

        // 3. Especialidades
        DB::table('especialidades')->insert([
            ['id' => 1, 'nombre' => 'Medicina General', 'descripcion' => 'Atención médica integral primaria', 'estado' => true],
            ['id' => 2, 'nombre' => 'Pediatría', 'descripcion' => 'Atención médica infantil', 'estado' => true],
            ['id' => 3, 'nombre' => 'Ginecología', 'descripcion' => 'Salud integral de la mujer', 'estado' => true],
            ['id' => 4, 'nombre' => 'Odontología', 'descripcion' => 'Salud bucodental', 'estado' => true],
        ]);

        // 4. Médicos
        DB::table('medicos')->insert([
            ['id' => 1, 'usuario_id' => 3, 'codigo' => 'MED-0001', 'nro_registro_profesional' => 'MP-10234', 'nombres' => 'Juan', 'apellidos' => 'Pérez', 'telefono' => '70011223', 'email' => 'juan.perez@vidasalud.bo', 'estado' => true],
            ['id' => 2, 'usuario_id' => 4, 'codigo' => 'MED-0002', 'nro_registro_profesional' => 'MP-20456', 'nombres' => 'Marta', 'apellidos' => 'Rodríguez', 'telefono' => '70022334', 'email' => 'marta.rodriguez@vidasalud.bo', 'estado' => true],
        ]);

        DB::table('medico_especialidad')->insert([
            ['medico_id' => 1, 'especialidad_id' => 1],
            ['medico_id' => 2, 'especialidad_id' => 2],
        ]);

        // 5. Horarios de Atención
        DB::table('horarios_atencion')->insert([
            ['medico_id' => 1, 'dia_semana' => 1, 'hora_inicio' => '08:00', 'hora_fin' => '12:00', 'duracion_minutos' => 30, 'estado' => true],
            ['medico_id' => 1, 'dia_semana' => 3, 'hora_inicio' => '14:00', 'hora_fin' => '18:00', 'duracion_minutos' => 30, 'estado' => true],
            ['medico_id' => 2, 'dia_semana' => 2, 'hora_inicio' => '09:00', 'hora_fin' => '13:00', 'duracion_minutos' => 30, 'estado' => true],
        ]);

        // 6. Pacientes
        DB::table('pacientes')->insert([
            ['id' => 1, 'codigo' => 'PAC-0001', 'ci' => '8945123', 'nombres' => 'María', 'apellidos' => 'López', 'fecha_nacimiento' => '1995-04-12', 'sexo' => 'F', 'telefono' => '75011122', 'email' => 'maria.lopez@gmail.com', 'direccion' => 'Av. Beni #120', 'estado' => true],
            ['id' => 2, 'codigo' => 'PAC-0002', 'ci' => '6321458', 'nombres' => 'Jorge', 'apellidos' => 'Vargas', 'fecha_nacimiento' => '1988-11-23', 'sexo' => 'M', 'telefono' => '76022233', 'email' => 'jorge.vargas@gmail.com', 'direccion' => 'Calle Sucre #45', 'estado' => true],
        ]);

        // 7. Cita de ejemplo
        DB::table('citas')->insert([
            ['codigo' => 'CM-0001', 'paciente_id' => 1, 'medico_id' => 1, 'especialidad_id' => 1, 'fecha_cita' => '2026-11-16', 'hora_inicio' => '09:00', 'hora_fin' => '09:30', 'motivo_consulta' => 'Consulta general', 'estado' => 'Programada'],
        ]);
    }
}