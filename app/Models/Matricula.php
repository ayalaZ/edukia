<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    public function estudiante() { return $this->belongsTo(Estudiante::class); }
public function seccion() { return $this->belongsTo(Seccion::class); }
}
