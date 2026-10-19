<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    protected $table = 'secciones';

    protected $fillable = ['grado_id', 'nombre', 'anio', 'docente_id'];

    public function grado()
    {
        return $this->belongsTo(Grado::class);
    }

    public function orientador()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->grado->nombre} {$this->nombre}";
    }
}
