<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Grado;
use App\Models\Seccion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SeccionController extends Controller
{
    public function index(Request $request)
{
    $anio = (int) ($request->anio ?: now()->year);

    $secciones = Seccion::query()
        ->select('secciones.*')
        ->withCount('matriculas')
        ->with(['grado', 'orientador.user'])
        ->join('grados', 'grados.id', '=', 'secciones.grado_id')
        ->where('secciones.anio', $anio)
        ->orderBy('grados.orden')
        ->orderBy('secciones.nombre')
        ->get();

    $anios = Seccion::query()->distinct()->pluck('anio')
        ->push(now()->year)->unique()->sortDesc()->values();

    return view('secciones.index', compact('secciones', 'anio', 'anios'));
}

    public function create()
    {
        return view('secciones.create', $this->datosFormulario());
    }

    public function store(Request $request)
    {
        Seccion::create($this->validar($request));

        return redirect()->route('secciones.index', ['anio' => $request->anio])
            ->with('ok', 'Sección creada correctamente.');
    }

    public function edit(Seccion $seccion)
    {
        return view('secciones.edit', ['seccion' => $seccion] + $this->datosFormulario());
    }

    public function update(Request $request, Seccion $seccion)
    {
        $seccion->update($this->validar($request, $seccion));

        return redirect()->route('secciones.index', ['anio' => $seccion->anio])
            ->with('ok', 'Sección actualizada correctamente.');
    }

    public function destroy(Seccion $seccion)
    {
        if ($seccion->matriculas()->exists()) {
            return back()->withErrors([
                'seccion' => 'No se puede eliminar: la sección tiene estudiantes matriculados.',
            ]);
        }

        $anio = $seccion->anio;
        $seccion->delete();

        return redirect()->route('secciones.index', ['anio' => $anio])
            ->with('ok', 'Sección eliminada.');
    }

    private function datosFormulario(): array
    {
        $docentes = Docente::with('user')
            ->whereHas('user', fn ($q) => $q->where('activo', true))
            ->get()
            ->sortBy(fn ($d) => $d->user->name)
            ->values();

        return [
            'grados'   => Grado::orderBy('orden')->get(),
            'docentes' => $docentes,
        ];
    }

    private function validar(Request $request, ?Seccion $seccion = null): array
    {
        $datos = $request->validate([
            'grado_id'   => ['required', 'exists:grados,id'],
            'nombre'     => [
                'required', 'string', 'max:10',
                Rule::unique('secciones', 'nombre')
                    ->where('grado_id', $request->grado_id)
                    ->where('anio', $request->anio)
                    ->ignore($seccion?->id),
            ],
            'anio'       => ['required', 'integer', 'min:2000', 'max:' . (now()->year + 1)],
            'docente_id' => [
                'nullable', 'exists:docentes,id',
                Rule::unique('secciones', 'docente_id')
                    ->where('anio', $request->anio)
                    ->ignore($seccion?->id),
            ],
        ], [
            'nombre.unique'     => 'Ya existe una sección con ese nombre en ese grado y año.',
            'docente_id.unique' => 'Ese docente ya es orientador de otra sección en ese año.',
        ], [
            'grado_id'   => 'grado',
            'anio'       => 'año',
            'docente_id' => 'orientador',
        ]);

        $datos['nombre'] = mb_strtoupper(trim($datos['nombre']));

        return $datos;
    }
}
