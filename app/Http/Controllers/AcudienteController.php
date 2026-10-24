<?php

namespace App\Http\Controllers;

use App\Models\Acudiente;
use App\Models\User;
use Illuminate\Http\Request;

class AcudienteController extends Controller
{
    public function index(Request $request)
    {
        $acudientes = User::query()
            ->where('rol', 'acudiente')
            ->with(['acudiente.estudiantes' => fn ($q) => $q->orderBy('apellidos')->orderBy('nombres')])
            ->when($request->buscar, function ($q, $buscar) {
                $q->where(function ($q) use ($buscar) {
                    $q->where('name', 'like', "%{$buscar}%")
                      ->orWhere('email', 'like', "%{$buscar}%")
                      ->orWhere('documento', 'like', "%{$buscar}%")
                      ->orWhere('telefono', 'like', "%{$buscar}%");
                });
            })
            ->when($request->estado === 'activo', fn ($q) => $q->where('activo', true))
            ->when($request->estado === 'inactivo', fn ($q) => $q->where('activo', false))
            ->when($request->vinculo === 'con', fn ($q) => $q->whereHas('acudiente.estudiantes'))
            ->when($request->vinculo === 'sin', fn ($q) => $q->whereDoesntHave('acudiente.estudiantes'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('acudientes.index', [
            'acudientes'  => $acudientes,
            'total'       => User::where('rol', 'acudiente')->count(),
            'parentescos' => Acudiente::PARENTESCOS,
        ]);
    }
}