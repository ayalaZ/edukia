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
        Schema::create('configuracion_economica', function (Blueprint $table) {
            $table->id();
            $table->decimal('matricula', 8, 2);
            $table->decimal('mensualidad', 8, 2);
            $table->decimal('descuento_becado', 5, 2);
            $table->decimal('descuento_beca_especial', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_economica');
    }
};
