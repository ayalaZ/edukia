<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Exports\PlantillaEstudiantes;
use App\Imports\EstudiantesImport;
use Maatwebsite\Excel\Facades\Excel;

class EstudianteController extends Controller
{
    private const ESTADOS = [
        'activo'   => 'Activo',
        'retirado' => 'Retirado',
        'graduado' => 'Graduado',
    ];

    public function index(Request $request)
    {
        $estudiantes = Estudiante::query()
            ->when($request->buscar, function ($q, $buscar) {
                $q->where(function ($q) use ($buscar) {
                    $q->where('nombres', 'like', "%{$buscar}%")
                      ->orWhere('apellidos', 'like', "%{$buscar}%")
                      ->orWhere('documento', 'like', "%{$buscar}%")
                      ->orWhere('codigo', 'like', "%{$buscar}%");
                });
            })
            ->when($request->estado, fn ($q, $estado) => $q->where('estado', $estado))
            ->when($request->beca === 'ninguna', fn ($q) => $q->whereNull('beca'))
            ->when(in_array($request->beca, array_keys(Estudiante::BECAS), true), fn ($q) => $q->where('beca', $request->beca))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(15)
            ->withQueryString();

        return view('estudiantes.index', [
            'estudiantes' => $estudiantes,
            'estados'     => self::ESTADOS,
        ]);
    }

    public function create()
    {
        return view('estudiantes.create', ['estados' => self::ESTADOS]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas());

        Estudiante::create($datos);

        return redirect()->route('estudiantes.index')
            ->with('ok', 'Estudiante registrado correctamente.');
    }

    public function edit(Estudiante $estudiante)
    {
        return view('estudiantes.edit', [
            'estudiante' => $estudiante,
            'estados'    => self::ESTADOS,
        ]);
    }

    public function update(Request $request, Estudiante $estudiante)
    {
        $estudiante->update($request->validate($this->reglas($estudiante)));

        return redirect()->route('estudiantes.index')
            ->with('ok', 'Estudiante actualizado correctamente.');
    }

    public function destroy(Estudiante $estudiante)
    {
        $estudiante->delete(); // SoftDeletes: se oculta, no se borra de la base

        return redirect()->route('estudiantes.index')
            ->with('ok', 'Estudiante eliminado.');
    }

    private function reglas(?Estudiante $estudiante = null): array
    {
        return [
            'codigo'           => ['nullable', 'string', 'max:30', Rule::unique('estudiantes', 'codigo')->ignore($estudiante?->id)],
            'nombres'          => ['required', 'string', 'max:255'],
            'apellidos'        => ['required', 'string', 'max:255'],
            'documento'        => ['nullable', 'string', 'max:20', Rule::unique('estudiantes', 'documento')->ignore($estudiante?->id)],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'sexo'             => ['nullable', Rule::in(['M', 'F'])],
            'telefono'         => ['nullable', 'string', 'max:20'],
            'direccion'        => ['nullable', 'string', 'max:255'],
            'estado'           => ['required', Rule::in(array_keys(self::ESTADOS))],
            'beca' => ['nullable', Rule::in(array_keys(Estudiante::BECAS))],
        ];
    }
    public function plantilla()
{
    return Excel::download(new PlantillaEstudiantes, 'plantilla_estudiantes.xlsx');
}

public function importar()
{
    return view('estudiantes.importar');
}

public function procesarImportacion(Request $request)
{
    $request->validate([
        'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
    ], [
        'archivo.required' => 'Selecciona un archivo de Excel.',
        'archivo.mimes'    => 'El archivo debe ser .xlsx, .xls o .csv.',
        'archivo.max'      => 'El archivo no puede pesar más de 5 MB.',
    ]);

    $import = new EstudiantesImport;
    Excel::import($import, $request->file('archivo'));

    if ($import->errores) {
        return back()->with('errores_importacion', $import->errores);
    }

    return redirect()->route('estudiantes.index')
        ->with('ok', "Se importaron {$import->importados} estudiantes correctamente.");
}
}
