<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        ];
    }
}
