<x-guest-layout>
    <div class="mb-4">
        <h1 class="text-lg font-semibold text-gray-900">Cambia tu contraseña</h1>
        <p class="mt-1 text-sm text-gray-600">
            Por seguridad, debes reemplazar la contraseña temporal antes de continuar.
        </p>
    </div>

    <form method="POST" action="{{ route('password.forzar.update') }}">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="password_actual" value="Contraseña temporal" />
            <x-text-input id="password_actual" name="password_actual" type="password"
                          class="mt-1 block w-full" required autofocus autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password_actual')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Nueva contraseña" />
            <x-text-input id="password" name="password" type="password"
                          class="mt-1 block w-full" required autocomplete="new-password" />
            <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres, con mayúsculas, minúsculas y números.</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirmar nueva contraseña" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                          class="mt-1 block w-full" required autocomplete="new-password" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <x-primary-button>Guardar contraseña</x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm text-gray-600 hover:underline">Cerrar sesión</button>
    </form>
</x-guest-layout>