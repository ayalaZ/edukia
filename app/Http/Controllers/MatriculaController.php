<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Seccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MatriculaController extends Controller
{
    public function index(Request $request, Seccion $seccion)
    {
        $seccion->load('grado', 'orientador.user');

        $matriculas = $seccion->matriculas()
            ->with(['estudiante' => fn ($q) => $q->withTrashed()])
            ->get()
            ->sortBy(fn ($m) => $m->estudiante->apellidos . ' ' . $m->estudiante->nombres)
            ->values();

        // Activos que todavía no tienen sección en ese año lectivo
        $disponibles = Estudiante::query()
            ->where('estado', 'activo')
            ->whereDoesntHave('matriculas', fn ($q) => $q->where('anio', $seccion->anio))
            ->when($request->buscar, function ($q, $buscar) {
                $q->where(function ($q) use ($buscar) {
                    $q->where('nombres', 'like', "%{$buscar}%")
                      ->orWhere('apellidos', 'like', "%{$buscar}%")
                      ->orWhere('documento', 'like', "%{$buscar}%")
                      ->orWhere('codigo', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->limit(200)
            ->get();

        return view('matriculas.index', compact('seccion', 'matriculas', 'disponibles'));
    }

    public function store(Request $request, Seccion $seccion)
    {
        $datos = $request->validate([
            'estudiantes'   => ['required', 'array', 'min:1'],
            'estudiantes.*' => ['integer', Rule::exists('estudiantes', 'id')->whereNull('deleted_at')],
        ], [
            'estudiantes.required' => 'Selecciona al menos un estudiante.',
            'estudiantes.min'      => 'Selecciona al menos un estudiante.',
        ]);

        $ids = collect($datos['estudiantes'])->unique()->values();

        $yaMatriculados = Matricula::where('anio', $seccion->anio)
            ->whereIn('estudiante_id', $ids)
            ->exists();

        if ($yaMatriculados) {
            return back()->withErrors([
                'estudiantes' => 'Alguno de los estudiantes seleccionados ya está matriculado en este año.',
            ]);
        }

        DB::transaction(function () use ($ids, $seccion) {
            foreach ($ids as $id) {
                Matricula::create([
                    'estudiante_id' => $id,
                    'seccion_id'    => $seccion->id,
                    'anio'          => $seccion->anio,
                    'estado'        => 'activa',
                    'fecha'         => now(),
                ]);
            }
        });

        return redirect()->route('matriculas.index', $seccion)
            ->with('ok', "Se matricularon {$ids->count()} estudiantes en {$seccion->load('grado')->nombre_completo}.");
    }

    public function destroy(Matricula $matricula)
    {
        $seccionId = $matricula->seccion_id;

        $matricula->delete();

        return redirect()->route('matriculas.index', $seccionId)
            ->with('ok', 'Estudiante quitado de la sección.');
    }
}
