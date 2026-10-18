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
        Schema::create('acudiente_estudiante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acudiente_id')->constrained('acudientes');
            $table->foreignId('estudiante_id')->constrained('estudiantes');
            $table->string('parentesco');                   // madre, padre, tío, tutor...
            $table->boolean('es_principal')->default(false);
            $table->timestamps();

             $table->unique(['acudiente_id', 'estudiante_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acudiente_estudiante');
    }
};
