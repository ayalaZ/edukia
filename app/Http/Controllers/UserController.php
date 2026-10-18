<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const ROLES = [
        'admin'          => 'Administrador',
        'administrativo' => 'Administrativo',
        'docente'        => 'Docente',
        'acudiente'      => 'Acudiente',
    ];

    private const CAMPOS_DOCENTE = ['especialidad', 'fecha_contratacion', 'tipo_contrato'];

    public function index(Request $request)
    {
        $usuarios = User::query()
            ->when($request->buscar, function ($q, $buscar) {
                $q->where(function ($q) use ($buscar) {
                    $q->where('name', 'like', "%{$buscar}%")
                      ->orWhere('email', 'like', "%{$buscar}%")
                      ->orWhere('documento', 'like', "%{$buscar}%");
                });
            })
            ->when($request->rol, fn ($q, $rol) => $q->where('rol', $rol))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'roles'    => self::ROLES,
        ]);
    }

    public function create()
    {
        return view('usuarios.create', ['roles' => self::ROLES]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas());

        $ficha = Arr::only($datos, self::CAMPOS_DOCENTE);
        $datos = Arr::except($datos, self::CAMPOS_DOCENTE);

        $datos['password']          = Hash::make($datos['password']);
        $datos['activo']            = true;
        $datos['cambiar_password']  = true;
        $datos['email_verified_at'] = now();

        DB::transaction(function () use ($datos, $ficha) {
            $user = User::create($datos);

            if ($user->rol === 'docente') {
                $user->docente()->create($ficha);
            }
        });

        return redirect()->route('usuarios.index')
            ->with('ok', 'Usuario registrado correctamente.');
    }

    public function edit(User $user)
    {
        return view('usuarios.edit', [
            'user'  => $user->load('docente'),
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $datos = $request->validate($this->reglas($user));

        $ficha = Arr::only($datos, self::CAMPOS_DOCENTE);
        $datos = Arr::except($datos, self::CAMPOS_DOCENTE);

        // Un administrador no puede quitarse su propio rol
        if ($user->id === Auth::id()) {
            $datos['rol'] = $user->rol;
        }

        if (empty($datos['password'])) {
            unset($datos['password']);
        } else {
            $datos['password'] = Hash::make($datos['password']);
            $datos['cambiar_password'] = $user->id !== Auth::id();
        }

        DB::transaction(function () use ($user, $datos, $ficha) {
            $user->update($datos);

            if ($user->rol === 'docente') {
                $user->docente()->updateOrCreate(['user_id' => $user->id], $ficha);
            }
        });

        return redirect()->route('usuarios.index')
            ->with('ok', 'Usuario actualizado correctamente.');
    }

    public function cambiarEstado(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['estado' => 'No puedes desactivar tu propia cuenta.']);
        }

        $user->update(['activo' => ! $user->activo]);

        return back()->with('ok', $user->activo ? 'Usuario activado.' : 'Usuario desactivado.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['estado' => 'No puedes eliminar tu propia cuenta.']);
        }

        try {
            $user->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withErrors([
                'estado' => 'No se puede eliminar: el usuario tiene registros asociados. Desactívalo en su lugar.',
            ]);
        }

        return redirect()->route('usuarios.index')
            ->with('ok', 'Usuario eliminado.');
    }

    private function reglas(?User $user = null): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'documento'        => ['nullable', 'string', 'max:20', Rule::unique('users', 'documento')->ignore($user?->id)],
            'telefono'         => ['nullable', 'string', 'max:20'],
            'direccion'        => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'rol'              => ['required', Rule::in(array_keys(self::ROLES))],
            'password'         => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],

            // Ficha del docente
            'especialidad'       => ['nullable', 'string', 'max:255'],
            'fecha_contratacion' => ['nullable', 'date', 'before_or_equal:today'],
            'tipo_contrato'      => ['nullable', Rule::in(array_keys(Docente::CONTRATOS))],
        ];
    }
}