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
        Schema::create('seccions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grado_id')->constrained('grados');
            $table->string('nombre');             // "A", "B"
            $table->unsignedSmallInteger('anio'); // año lectivo
            $table->timestamps();

             $table->unique(['grado_id', 'nombre', 'anio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seccions');
    }
};
