<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    public const CONTRATOS = [
        'permanente' => 'Permanente',
        'temporal'   => 'Temporal',
        'por_horas'  => 'Por horas',
    ];

    protected $fillable = [
        'user_id', 'especialidad', 'fecha_contratacion', 'tipo_contrato',
    ];

    protected $casts = [
        'fecha_contratacion' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
