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
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique()->nullable(); // NIE o código interno
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('documento')->unique()->nullable();
            $table->date('fecha_nacimiento');
            $table->string('sexo', 1)->nullable();          // M / F
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('estado')->default('activo');    // activo, retirado, graduado
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
