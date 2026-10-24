<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Acudientes de {{ $estudiante->apellidos }}, {{ $estudiante->nombres }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">{{ $estudiante->codigo ?? 'Sin código' }}</p>
            </div>
            <a href="{{ route('estudiantes.index') }}" class="text-sm text-gray-600 hover:underline">← Volver a estudiantes</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('ok'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md text-sm">{{ session('ok') }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Vinculados --}}
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-medium text-gray-900">Acudientes vinculados</h3>
                        <span class="text-sm text-gray-500">{{ $vinculados->count() }}</span>
                    </div>

                    <ul class="divide-y divide-gray-100">
                        @forelse ($vinculados as $acudiente)
                            <li class="px-4 py-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ $acudiente->user->name }}
                                            @if ($acudiente->pivot->es_principal)
                                                <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">Principal</span>
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-600">{{ $parentescos[$acudiente->pivot->parentesco] ?? $acudiente->pivot->parentesco }}</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $acudiente->user->telefono ?? 'Sin teléfono' }} · {{ $acudiente->user->email }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-3 text-sm">
                                        @unless ($acudiente->pivot->es_principal)
                                            <form method="POST" action="{{ route('estudiantes.acudientes.principal', [$estudiante, $acudiente]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-indigo-600 hover:underline">Hacer principal</button>
                                            </form>
                                        @endunless

                                        <form method="POST" action="{{ route('estudiantes.acudientes.destroy', [$estudiante, $acudiente]) }}"
                                              onsubmit="return confirm('¿Desvincular a {{ $acudiente->user->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Quitar</button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="px-4 py-8 text-center text-gray-500">Este estudiante todavía no tiene acudientes.</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Agregar --}}
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="font-medium text-gray-900">Vincular un acudiente</h3>

                        <form method="GET" class="flex gap-2 mt-3">
                            <input type="text" name="buscar" value="{{ request('buscar') }}"
                                   placeholder="Buscar por nombre, documento o correo"
                                   class="flex-1 border-gray-300 rounded-md shadow-sm text-sm">
                            <x-primary-button>Buscar</x-primary-button>
                        </form>
                    </div>

                    <form method="POST" action="{{ route('estudiantes.acudientes.store', $estudiante) }}" class="p-4 space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="user_id" value="Acudiente" />
                            <select id="user_id" name="user_id" required
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">
                                    {{ $disponibles->isEmpty() ? 'No hay acudientes disponibles' : 'Selecciona un acudiente' }}
                                </option>
                                @foreach ($disponibles as $usuario)
                                    <option value="{{ $usuario->id }}" @selected((string) old('user_id') === (string) $usuario->id)>
                                        {{ $usuario->name }}{{ $usuario->documento ? ' — ' . $usuario->documento : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="parentesco" value="Parentesco" />
                            <select id="parentesco" name="parentesco" required
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Selecciona</option>
                                @foreach ($parentescos as $valor => $etiqueta)
                                    <option value="{{ $valor }}" @selected(old('parentesco') === $valor)>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('parentesco')" class="mt-2" />
                        </div>

                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="es_principal" value="1" class="rounded border-gray-300">
                            Marcar como acudiente principal
                        </label>

                        <div class="flex items-center justify-between">
                            @if (auth()->user()->rol === 'admin')
                                <a href="{{ route('usuarios.create') }}" class="text-sm text-indigo-600 hover:underline">
                                    Registrar un acudiente nuevo
                                </a>
                            @else
                                <span></span>
                            @endif

                            <x-primary-button>Vincular</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>