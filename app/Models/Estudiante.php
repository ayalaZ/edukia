<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estudiante extends Model
{
    use SoftDeletes;

    protected $fillable = ['codigo', 'nombres', 'apellidos', 'documento',
        'fecha_nacimiento', 'sexo', 'direccion', 'telefono', 'estado'];

    protected $casts = ['fecha_nacimiento' => 'date'];

    public function matriculas() { return $this->hasMany(Matricula::class); }

    public function acudientes()
    {
        return $this->belongsToMany(Acudiente::class, 'acudiente_estudiante')
            ->withPivot('parentesco', 'es_principal')->withTimestamps();
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }
}
