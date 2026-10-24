<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Acudiente extends Model
{
    public const PARENTESCOS = [
        'madre'   => 'Madre',
        'padre'   => 'Padre',
        'abuelo'  => 'Abuelo/a',
        'tio'     => 'Tío/a',
        'hermano' => 'Hermano/a',
        'tutor'   => 'Tutor legal',
        'otro'    => 'Otro',
    ];

    protected $fillable = ['user_id', 'ocupacion', 'lugar_trabajo'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'acudiente_estudiante')
            ->withPivot('parentesco', 'es_principal')
            ->withTimestamps();
    }
}