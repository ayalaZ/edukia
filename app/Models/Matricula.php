<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    protected $fillable = ['estudiante_id', 'seccion_id', 'anio', 'estado', 'fecha'];

    protected $casts = ['fecha' => 'date'];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function seccion()
    {
        return $this->belongsTo(Seccion::class);
    }
}