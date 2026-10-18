<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private const ROLES = [
        'admin'      => 'Administrador',
        'administrativo' => 'Administrativo',
        'docente'    => 'Docente',
        'estudiante' => 'Estudiante',
        'acudiente'  => 'Acudiente',
    ];

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

        $datos['password'] = Hash::make($datos['password']);
        $datos['activo'] = true;
        $datos['cambiar_password'] = true;
        $datos['email_verified_at'] = now();

        User::create($datos);

        return redirect()->route('usuarios.index')
            ->with('ok', 'Usuario registrado correctamente.');
    }

    public function edit(User $user)
    {
        return view('usuarios.edit', [
            'user'  => $user,
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $datos = $request->validate($this->reglas($user));

        // Un administrador no puede quitarse su propio rol
        if ($user->id === Auth::id()) {
            $datos['rol'] = $user->rol;
        }

        if (empty($datos['password'])) {
            unset($datos['password']);
        } else {
            $datos['password'] = Hash::make($datos['password']);
            // Si el admin cambia su propia clave, no se le obliga a cambiarla otra vez
            $datos['cambiar_password'] = $user->id !== Auth::id();
        }

        $user->update($datos);

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
        ];
    }
}
