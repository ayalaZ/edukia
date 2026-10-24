<?php

namespace App\Http\Controllers;

use App\Exports\PlantillaAcudientes;
use App\Imports\AcudientesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AcudienteImportController extends Controller
{
    public function plantilla()
    {
        return Excel::download(new PlantillaAcudientes, 'plantilla_acudientes.xlsx');
    }

    public function importar()
    {
        return view('acudientes.importar');
    }

    public function procesar(Request $request)
    {
        $request->validate([
            'archivo'           => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'password_temporal' => ['required', 'string', 'min:8'],
        ], [
            'archivo.required'           => 'Selecciona un archivo de Excel.',
            'archivo.mimes'              => 'El archivo debe ser .xlsx, .xls o .csv.',
            'archivo.max'                => 'El archivo no puede pesar más de 5 MB.',
            'password_temporal.required' => 'Escribe la contraseña temporal para los acudientes nuevos.',
            'password_temporal.min'      => 'La contraseña temporal debe tener al menos 8 caracteres.',
        ]);

        $import = new AcudientesImport($request->password_temporal);
        Excel::import($import, $request->file('archivo'));

        if ($import->errores) {
            return back()->with('errores_importacion', $import->errores);
        }

        return redirect()->route('estudiantes.index')
            ->with('ok', "Importación lista: {$import->acudientesNuevos} acudientes nuevos y {$import->vinculos} vínculos con estudiantes.");
    }
}