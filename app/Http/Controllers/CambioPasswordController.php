<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CambioPasswordController extends Controller
{
     public function edit(Request $request)
    {
        // Si no está obligado, no tiene nada que hacer aquí
        if (! $request->user()->cambiar_password) {
            return redirect()->route('dashboard');
        }

        return view('auth.cambiar-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password_actual' => ['required', 'current_password'],
            'password'        => ['required', 'confirmed', 'different:password_actual',
                                  Password::min(8)->mixedCase()->numbers()],
        ], [
            'password_actual.current_password' => 'La contraseña temporal no es correcta.',
            'password.different'               => 'La nueva contraseña debe ser distinta a la temporal.',
        ]);

        $request->user()->update([
            'password'         => Hash::make($request->password),
            'cambiar_password' => false,
        ]);

        return redirect()->route('dashboard')
            ->with('status', 'Contraseña actualizada correctamente.');
    }
}
