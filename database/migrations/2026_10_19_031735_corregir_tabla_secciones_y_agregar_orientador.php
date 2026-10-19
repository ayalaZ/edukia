<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Corregir el nombre de la tabla
        if (Schema::hasTable('seccions') && ! Schema::hasTable('secciones')) {
            Schema::rename('seccions', 'secciones');
        }

        // 2. Agregar el orientador (docente de la sección)
        if (! Schema::hasColumn('secciones', 'docente_id')) {
            Schema::table('secciones', function (Blueprint $table) {
                $table->foreignId('docente_id')
                    ->nullable()
                    ->after('nombre')
                    ->constrained('docentes')
                    ->nullOnDelete();

                // Un docente orienta como máximo una sección por año
                $table->unique(['docente_id', 'anio']);
            });
        }
    }

    public function down(): void
    {
        // Sin reversa: evita perder datos por accidente
    }
};