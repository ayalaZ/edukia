<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Acudiente extends Model
{
    public function user() { return $this->belongsTo(User::class); }

public function estudiantes()
{
    return $this->belongsToMany(Estudiante::class, 'acudiente_estudiante')
        ->withPivot('parentesco', 'es_principal')->withTimestamps();
}
}
