<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfiguracionEconomica;

class ConfiguracionEconomicaController extends Controller
{
    public function index()
    {
        $config = ConfiguracionEconomica::first();

        return view(
            'Economia.index',
            compact('config')
        );
    }

    public function update(Request $request)
    {
        $request->validate([
            'matricula' => 'required|numeric|min:0',
            'mensualidad' => 'required|numeric|min:0',
            'descuento_becado' => 'required|numeric|min:0|max:100',
            'descuento_beca_especial' => 'required|numeric|min:0|max:100'
        ]);

        $config = ConfiguracionEconomica::first();

        $config->update($request->all());

        return back()
            ->with('success', 'Configuración actualizada');
    }
}
