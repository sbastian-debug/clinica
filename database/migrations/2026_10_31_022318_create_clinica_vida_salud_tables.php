<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla de Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
        });

        // 2. Tabla de Usuarios (RF01)
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->restrictOnDelete();
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password_hash', 255);
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        // 3. Tabla de Pacientes (RF02, RN01, RN11)
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->unique()->constrained('usuarios')->nullOnDelete();
            $table->string('codigo', 20)->unique();
            $table->string('ci', 20)->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->date('fecha_nacimiento');
            $table->char('sexo', 1);
            $table->string('telefono', 20);
            $table->string('email', 100)->nullable();
            $table->text('direccion')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        // 4. Tabla de Especialidades (RF04)
        Schema::create('especialidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('estado')->default(true);
        });

        // 5. Tabla de Médicos (RF03, RN02, RN04, RN11)
        Schema::create('medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->unique()->constrained('usuarios')->nullOnDelete();
            $table->string('codigo', 20)->unique();
            $table->string('nro_registro_profesional', 50)->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('telefono', 20);
            $table->string('email', 100)->unique();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        // 6. Tabla Pivote Médico - Especialidad
        Schema::create('medico_especialidad', function (Blueprint $table) {
            $table->foreignId('medico_id')->constrained('medicos')->restrictOnDelete();
            $table->foreignId('especialidad_id')->constrained('especialidades')->restrictOnDelete();
            $table->primary(['medico_id', 'especialidad_id']);
        });

        // 7. Tabla de Horarios de Atención (RF05, RN05)
        Schema::create('horarios_atencion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')->constrained('medicos')->restrictOnDelete();
            $table->smallInteger('dia_semana'); // 1=Lunes ... 7=Domingo
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('duracion_minutos')->default(30);
            $table->boolean('estado')->default(true);
        });

        DB::statement('ALTER TABLE horarios_atencion ADD CONSTRAINT chk_horario_valido CHECK (hora_fin > hora_inicio)');

        // 8. Tabla de Citas Médicas (RF06, RF07, RF08, RF09, RF10)
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
            $table->foreignId('medico_id')->constrained('medicos')->restrictOnDelete();
            $table->foreignId('especialidad_id')->constrained('especialidades')->restrictOnDelete();
            $table->date('fecha_cita');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->text('motivo_consulta')->nullable();
            $table->string('estado', 20)->default('Programada');
            $table->text('motivo_cancelacion')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        // RN07 y RN09: Índice único en PostgreSQL para evitar dos citas activas a la misma hora con el mismo médico
        DB::statement("CREATE UNIQUE INDEX idx_cita_unica_activa ON citas (medico_id, fecha_cita, hora_inicio) WHERE (estado <> 'Cancelada')");
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
        Schema::dropIfExists('horarios_atencion');
        Schema::dropIfExists('medico_especialidad');
        Schema::dropIfExists('medicos');
        Schema::dropIfExists('especialidades');
        Schema::dropIfExists('pacientes');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('roles');
    }
};