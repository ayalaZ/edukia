<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes');
            $table->foreignId('seccion_id')->constrained('secciones');
            $table->unsignedSmallInteger('anio');
            $table->string('estado')->default('activa');    // activa, retirada, aprobada, reprobada
            $table->date('fecha')->nullable();
            $table->timestamps();

             $table->unique(['estudiante_id', 'anio']);      // una matrícula por año
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};
