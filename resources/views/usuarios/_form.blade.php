@php $user = $user ?? null; @endphp

@csrf
@if ($user)
    @method('PUT')
@endif

<h3 class="text-lg font-medium text-gray-900 mb-4">Datos personales</h3>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="md:col-span-2">
        <x-input-label for="name" value="Nombre completo" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $user?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="documento" value="Documento de identidad" />
        <x-text-input id="documento" name="documento" type="text" class="mt-1 block w-full"
                      :value="old('documento', $user?->documento)" />
        <x-input-error :messages="$errors->get('documento')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
        <x-text-input id="fecha_nacimiento" name="fecha_nacimiento" type="date" class="mt-1 block w-full"
                      :value="old('fecha_nacimiento', $user?->fecha_nacimiento?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="telefono" value="Teléfono" />
        <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full"
                      :value="old('telefono', $user?->telefono)" />
        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="direccion" value="Dirección" />
        <x-text-input id="direccion" name="direccion" type="text" class="mt-1 block w-full"
                      :value="old('direccion', $user?->direccion)" />
        <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
    </div>
</div>

<h3 class="text-lg font-medium text-gray-900 mt-8 mb-4">Acceso al sistema</h3>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <x-input-label for="email" value="Correo electrónico" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                      :value="old('email', $user?->email)" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="rol" value="Rol" />
        <select id="rol" name="rol" required
                @disabled($user && $user->id === auth()->id())
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">Selecciona un rol</option>
            @foreach ($roles as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('rol', $user?->rol) === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('rol')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" :value="$user ? 'Nueva contraseña (opcional)' : 'Contraseña temporal'" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                      :required="! $user" autocomplete="new-password" />
        @if ($user)
            <p class="text-xs text-gray-500 mt-1">Déjala vacía para conservar la actual.</p>
        @endif
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Confirmar contraseña" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                      class="mt-1 block w-full" :required="! $user" autocomplete="new-password" />
    </div>
</div>

<div class="flex items-center justify-end gap-4 mt-8">
    <a href="{{ route('usuarios.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
    <x-primary-button>{{ $user ? 'Guardar cambios' : 'Registrar usuario' }}</x-primary-button>
</div>