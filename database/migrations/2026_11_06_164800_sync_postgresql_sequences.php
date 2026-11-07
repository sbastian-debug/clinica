<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sincroniza las secuencias de PostgreSQL con el valor máximo real de ID en cada tabla
     * para evitar errores de clave duplicada (UniqueConstraintViolationException).
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            $tablas = ['usuarios', 'pacientes', 'medicos', 'especialidades', 'horarios_atencion', 'citas', 'roles'];

            foreach ($tablas as $tabla) {
                try {
                    $maxId = DB::table($tabla)->max('id') ?? 0;
                    if ($maxId > 0) {
                        DB::statement("SELECT setval(COALESCE(pg_get_serial_sequence('{$tabla}', 'id'), '{$tabla}_id_seq'), ?)", [$maxId]);
                    }
                } catch (\Throwable $e) {
                    // Continuar si la tabla no existe aún
                }
            }
        }
    }

    public function down(): void
    {
        // No requiere acción reversible
    }
};
