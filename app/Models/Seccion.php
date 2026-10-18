<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    protected $table = 'secciones';

public function grado() { return $this->belongsTo(Grado::class); }
public function matriculas() { return $this->hasMany(Matricula::class); }
}
