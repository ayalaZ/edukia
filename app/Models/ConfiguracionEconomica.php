<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionEconomica extends Model
{
    protected $table = 'configuracion_economica';

    protected $fillable = [
        'matricula',
        'mensualidad',
        'descuento_becado',
        'descuento_beca_especial'
    ];
}

ConfiguracionEconomica::create([
    'matricula' => 35.00,
    'mensualidad' => 25.00,
    'descuento_becado' => 50,
    'descuento_beca_especial' => 100
]);
