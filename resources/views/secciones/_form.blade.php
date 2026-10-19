@php $seccion = $seccion ?? null; @endphp

@csrf
@if ($seccion)
    @method('PUT')
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <x-input-label for="grado_id" value="Grado" />
        <select id="grado_id" name="grado_id" required
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">Selecciona un grado</option>
            @foreach ($grados as $grado)
                <option value="{{ $grado->id }}" @selected((string) old('grado_id', $seccion?->grado_id) === (string) $grado->id)>
                    {{ $grado->nombre }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('grado_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="nombre" value="Sección (A, B, C...)" />
        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full uppercase"
                      :value="old('nombre', $seccion?->nombre)" maxlength="10" required />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="anio" value="Año lectivo" />
        <x-text-input id="anio" name="anio" type="number" class="mt-1 block w-full"
                      :value="old('anio', $seccion?->anio ?? request('anio', now()->year))" required />
        <x-input-error :messages="$errors->get('anio')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="docente_id" value="Docente orientador" />
        <select id="docente_id" name="docente_id"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">Sin orientador por ahora</option>
            @foreach ($docentes as $docente)
                <option value="{{ $docente->id }}" @selected((string) old('docente_id', $seccion?->docente_id) === (string) $docente->id)>
                    {{ $docente->user->name }}{{ $docente->especialidad ? ' — ' . $docente->especialidad : '' }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('docente_id')" class="mt-2" />
    </div>
</div>

<div class="flex items-center justify-end gap-4 mt-8">
    <a href="{{ route('secciones.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
    <x-primary-button>{{ $seccion ? 'Guardar cambios' : 'Crear sección' }}</x-primary-button>
</div>