@php $estudiante = $estudiante ?? null; @endphp

@csrf
@if ($estudiante)
    @method('PUT')
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <x-input-label for="nombres" value="Nombres" />
        <x-text-input id="nombres" name="nombres" type="text" class="mt-1 block w-full"
                      :value="old('nombres', $estudiante?->nombres)" required autofocus />
        <x-input-error :messages="$errors->get('nombres')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="apellidos" value="Apellidos" />
        <x-text-input id="apellidos" name="apellidos" type="text" class="mt-1 block w-full"
                      :value="old('apellidos', $estudiante?->apellidos)" required />
        <x-input-error :messages="$errors->get('apellidos')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="codigo" value="Código de estudiante" />
        <x-text-input id="codigo" name="codigo" type="text" class="mt-1 block w-full"
                      :value="old('codigo', $estudiante?->codigo)" />
        <x-input-error :messages="$errors->get('codigo')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="documento" value="Documento de identidad" />
        <x-text-input id="documento" name="documento" type="text" class="mt-1 block w-full"
                      :value="old('documento', $estudiante?->documento)" />
        <x-input-error :messages="$errors->get('documento')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
        <x-text-input id="fecha_nacimiento" name="fecha_nacimiento" type="date" class="mt-1 block w-full"
                      :value="old('fecha_nacimiento', $estudiante?->fecha_nacimiento?->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="sexo" value="Sexo" />
        <select id="sexo" name="sexo"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">Selecciona</option>
            <option value="M" @selected(old('sexo', $estudiante?->sexo) === 'M')>Masculino</option>
            <option value="F" @selected(old('sexo', $estudiante?->sexo) === 'F')>Femenino</option>
        </select>
        <x-input-error :messages="$errors->get('sexo')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="telefono" value="Teléfono" />
        <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full"
                      :value="old('telefono', $estudiante?->telefono)" />
        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="estado" value="Estado" />
        <select id="estado" name="estado" required
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            @foreach ($estados as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('estado', $estudiante?->estado ?? 'activo') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('estado')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="direccion" value="Dirección" />
        <x-text-input id="direccion" name="direccion" type="text" class="mt-1 block w-full"
                      :value="old('direccion', $estudiante?->direccion)" />
        <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
    </div>
</div>

<div class="flex items-center justify-end gap-4 mt-8">
    <a href="{{ route('estudiantes.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
    <x-primary-button>{{ $estudiante ? 'Guardar cambios' : 'Registrar estudiante' }}</x-primary-button>
</div>