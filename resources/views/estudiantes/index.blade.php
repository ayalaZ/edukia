<x-app-layout>
    <x-slot name="header">
       <div class="flex items-center gap-2">
    <a href="{{ route('acudientes.importar') }}"
       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-xs font-semibold uppercase tracking-widest rounded-md hover:bg-gray-50">
        Importar acudientes
    </a>
    <a href="{{ route('estudiantes.importar') }}"
       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-xs font-semibold uppercase tracking-widest rounded-md hover:bg-gray-50">
        Importar estudiantes
    </a>
    <a href="{{ route('estudiantes.create') }}"
       class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase tracking-widest rounded-md hover:bg-gray-700">
        Nuevo estudiante
    </a>
</div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('ok'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md text-sm">{{ session('ok') }}</div>
            @endif

            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       placeholder="Buscar por nombre, documento o código"
                       class="flex-1 border-gray-300 rounded-md shadow-sm">
                <select name="estado" class="border-gray-300 rounded-md shadow-sm">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $valor => $etiqueta)
                        <option value="{{ $valor }}" @selected(request('estado') === $valor)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <select name="beca" class="border-gray-300 rounded-md shadow-sm">
    <option value="">Con o sin beca</option>
    <option value="ninguna" @selected(request('beca') === 'ninguna')>Sin beca</option>
    @foreach (\App\Models\Estudiante::BECAS as $valor => $etiqueta)
        <option value="{{ $valor }}" @selected(request('beca') === $valor)>{{ $etiqueta }}</option>
    @endforeach
</select>
                <x-primary-button>Buscar</x-primary-button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Código</th>
                            <th class="px-4 py-3">Estudiante</th>
                            <th class="px-4 py-3">Documento</th>
                            <th class="px-4 py-3">Edad</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($estudiantes as $estudiante)
                            <tr>
                                <td class="px-4 py-3">{{ $estudiante->codigo ?? '—' }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $estudiante->apellidos }}, {{ $estudiante->nombres }}
                                </td>
                                <td class="px-4 py-3">{{ $estudiante->documento ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $estudiante->fecha_nacimiento->age }} años</td>
                                <td class="px-4 py-3">
                                    @php
                                        $color = match ($estudiante->estado) {
                                            'activo'   => 'bg-green-100 text-green-800',
                                            'graduado' => 'bg-blue-100 text-blue-800',
                                            default    => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $color }}">
                                        {{ $estados[$estudiante->estado] ?? $estudiante->estado }}
                                    </span>
                                    @if ($estudiante->beca)
                                        <span class="ml-1 px-2 py-1 text-xs font-medium rounded-full {{ $estudiante->beca === 'beca_especial' ? 'bg-purple-100 text-purple-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ \App\Models\Estudiante::BECAS[$estudiante->beca] ?? $estudiante->beca }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('estudiantes.acudientes.index', $estudiante) }}"
                                        class="text-emerald-600 hover:underline">Acudientes</a>
                                        <a href="{{ route('estudiantes.edit', $estudiante) }}"
                                           class="text-indigo-600 hover:underline">Editar</a>

                                        <form method="POST" action="{{ route('estudiantes.destroy', $estudiante) }}"
                                              onsubmit="return confirm('¿Eliminar a {{ $estudiante->nombres }} {{ $estudiante->apellidos }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No se encontraron estudiantes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $estudiantes->links() }}
        </div>
    </div>
</x-app-layout>