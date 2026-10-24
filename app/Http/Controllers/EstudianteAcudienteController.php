<?php

namespace App\Http\Controllers;

use App\Models\Acudiente;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EstudianteAcudienteController extends Controller
{
    public function index(Request $request, Estudiante $estudiante)
    {
        $vinculados = $estudiante->acudientes()->with('user')->get()
            ->sortByDesc(fn ($a) => (int) $a->pivot->es_principal)
            ->values();

        $disponibles = User::query()
            ->where('rol', 'acudiente')
            ->where('activo', true)
            ->whereDoesntHave('acudiente.estudiantes', fn ($q) => $q->where('estudiantes.id', $estudiante->id))
            ->when($request->buscar, function ($q, $buscar) {
                $q->where(function ($q) use ($buscar) {
                    $q->where('name', 'like', "%{$buscar}%")
                      ->orWhere('documento', 'like', "%{$buscar}%")
                      ->orWhere('email', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get();

        return view('estudiantes.acudientes', [
            'estudiante'  => $estudiante,
            'vinculados'  => $vinculados,
            'disponibles' => $disponibles,
            'parentescos' => Acudiente::PARENTESCOS,
        ]);
    }

    public function store(Request $request, Estudiante $estudiante)
    {
        $datos = $request->validate([
            'user_id'      => ['required', Rule::exists('users', 'id')->where('rol', 'acudiente')->where('activo', true)],
            'parentesco'   => ['required', Rule::in(array_keys(Acudiente::PARENTESCOS))],
            'es_principal' => ['nullable', 'boolean'],
        ], [
            'user_id.required'    => 'Selecciona un acudiente.',
            'parentesco.required' => 'Indica el parentesco.',
        ]);

        $acudiente = Acudiente::firstOrCreate(['user_id' => $datos['user_id']]);

        if ($estudiante->acudientes()->where('acudientes.id', $acudiente->id)->exists()) {
            return back()->withErrors(['user_id' => 'Ese acudiente ya está vinculado a este estudiante.']);
        }

        $esPrimero = ! $estudiante->acudientes()->exists();
        $principal = $esPrimero || $request->boolean('es_principal');

        DB::transaction(function () use ($estudiante, $acudiente, $datos, $principal) {
            if ($principal) {
                DB::table('acudiente_estudiante')
                    ->where('estudiante_id', $estudiante->id)
                    ->update(['es_principal' => false]);
            }

            $estudiante->acudientes()->attach($acudiente->id, [
                'parentesco'   => $datos['parentesco'],
                'es_principal' => $principal,
            ]);
        });

        return redirect()->route('estudiantes.acudientes.index', $estudiante)
            ->with('ok', 'Acudiente vinculado correctamente.');
    }

    public function principal(Estudiante $estudiante, Acudiente $acudiente)
    {
        abort_unless($estudiante->acudientes()->where('acudientes.id', $acudiente->id)->exists(), 404);

        DB::transaction(function () use ($estudiante, $acudiente) {
            DB::table('acudiente_estudiante')
                ->where('estudiante_id', $estudiante->id)
                ->update(['es_principal' => false]);

            DB::table('acudiente_estudiante')
                ->where('estudiante_id', $estudiante->id)
                ->where('acudiente_id', $acudiente->id)
                ->update(['es_principal' => true]);
        });

        return back()->with('ok', 'Acudiente principal actualizado.');
    }

    public function destroy(Estudiante $estudiante, Acudiente $acudiente)
    {
        DB::transaction(function () use ($estudiante, $acudiente) {
            $estudiante->acudientes()->detach($acudiente->id);

            // Si se quitó al principal y quedan otros, el más antiguo pasa a ser principal
            $hayPrincipal = DB::table('acudiente_estudiante')
                ->where('estudiante_id', $estudiante->id)
                ->where('es_principal', true)
                ->exists();

            if (! $hayPrincipal) {
                $siguiente = DB::table('acudiente_estudiante')
                    ->where('estudiante_id', $estudiante->id)
                    ->orderBy('id')
                    ->first();

                if ($siguiente) {
                    DB::table('acudiente_estudiante')
                        ->where('id', $siguiente->id)
                        ->update(['es_principal' => true]);
                }
            }
        });

        return back()->with('ok', 'Acudiente desvinculado.');
    }
}