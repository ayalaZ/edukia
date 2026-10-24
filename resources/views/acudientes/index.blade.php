<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Acudientes</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $total }} registrados</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('acudientes.importar') }}"
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-xs font-semibold uppercase tracking-widest rounded-md hover:bg-gray-50">
                    Importar desde Excel
                </a>

                @if (auth()->user()->rol === 'admin')
                    <a href="{{ route('usuarios.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase tracking-widest rounded-md hover:bg-gray-700">
                        Nuevo acudiente
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('ok'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md text-sm">{{ session('ok') }}</div>
            @endif

            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       placeholder="Buscar por nombre, documento, correo o teléfono"
                       class="flex-1 border-gray-300 rounded-md shadow-sm">

                <select name="vinculo" class="border-gray-300 rounded-md shadow-sm">
                    <option value="">Con o sin estudiantes</option>
                    <option value="con" @selected(request('vinculo') === 'con')>Con estudiantes</option>
                    <option value="sin" @selected(request('vinculo') === 'sin')>Sin estudiantes</option>
                </select>

                <select name="estado" class="border-gray-300 rounded-md shadow-sm">
                    <option value="">Todos los estados</option>
                    <option value="activo" @selected(request('estado') === 'activo')>Activos</option>
                    <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivos</option>
                </select>

                <x-primary-button>Buscar</x-primary-button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Acudiente</th>
                            <th class="px-4 py-3">Documento</th>
                            <th class="px-4 py-3">Teléfono</th>
                            <th class="px-4 py-3">Estudiantes a su cargo</th>
                            <th class="px-4 py-3">Estado</th>
                            @if (auth()->user()->rol === 'admin')
                                <th class="px-4 py-3 text-right">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($acudientes as $usuario)
                            @php $hijos = $usuario->acudiente?->estudiantes ?? collect(); @endphp
                            <tr class="align-top">
                                <td class="px-4 py-3">
                                    <span class="font-medium text-gray-900">{{ $usuario->name }}</span>
                                    <span class="block text-xs text-gray-500">{{ $usuario->email }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $usuario->documento ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $usuario->telefono ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @forelse ($hijos as $estudiante)
                                        <div class="mb-1 last:mb-0">
                                            <a href="{{ route('estudiantes.acudientes.index', $estudiante) }}"
                                               class="text-indigo-600 hover:underline">
                                                {{ $estudiante->apellidos }}, {{ $estudiante->nombres }}
                                            </a>
                                            <span class="text-xs text-gray-500">
                                                · {{ $parentescos[$estudiante->pivot->parentesco] ?? $estudiante->pivot->parentesco }}
                                                @if ($estudiante->pivot->es_principal)
                                                    · <span class="text-indigo-700 font-medium">Principal</span>
                                                @endif
                                            </span>
                                        </div>
                                    @empty
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-800">
                                            Sin estudiantes
                                        </span>
                                    @endforelse
                                </td>
                                <td class="px-4 py-3">
                                    @if ($usuario->activo)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Activo</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Inactivo</span>
                                    @endif
                                </td>
                                @if (auth()->user()->rol === 'admin')
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('usuarios.edit', $usuario) }}"
                                           class="text-indigo-600 hover:underline">Editar</a>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No se encontraron acudientes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $acudientes->links() }}
        </div>
    </div>
</x-app-layout>