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
            Schema::table('users', function (Blueprint $table) {
            Schema::table('users', function (Blueprint $table) {
            $table->string('documento')->unique()->nullable();
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('rol')->default('estudiante'); // admin, docente, estudiante, acudiente
            $table->boolean('activo')->default(true);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['documento', 'telefono', 'direccion', 'fecha_nacimiento', 'rol', 'activo']);
        });
    }
};
