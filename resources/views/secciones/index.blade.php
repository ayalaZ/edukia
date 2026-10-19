<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Secciones</h2>
            <a href="{{ route('secciones.create', ['anio' => $anio]) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase tracking-widest rounded-md hover:bg-gray-700">
                Nueva sección
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('ok'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md text-sm">{{ session('ok') }}</div>
            @endif

            @if ($errors->has('seccion'))
                <div class="p-4 bg-red-100 text-red-800 rounded-md text-sm">{{ $errors->first('seccion') }}</div>
            @endif

            <form method="GET" class="flex items-center gap-3">
                <label for="anio" class="text-sm text-gray-600">Año lectivo</label>
                <select id="anio" name="anio" onchange="this.form.submit()"
                        class="border-gray-300 rounded-md shadow-sm">
                    @foreach ($anios as $a)
                        <option value="{{ $a }}" @selected($a == $anio)>{{ $a }}</option>
                    @endforeach
                </select>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Sección</th>
                            <th class="px-4 py-3">Orientador</th>
                            <th class="px-4 py-3">Estudiantes</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($secciones as $seccion)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $seccion->nombre_completo }}</td>
                                <td class="px-4 py-3">
                                    @if ($seccion->orientador)
                                        {{ $seccion->orientador->user->name }}
                                    @else
                                        <span class="text-gray-400">Sin asignar</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $seccion->matriculas_count }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('matriculas.index', $seccion) }}" class="text-emerald-600 hover:underline">Estudiantes</a>
                                        <a href="{{ route('secciones.edit', $seccion) }}"
                                           class="text-indigo-600 hover:underline">Editar</a>

                                        <form method="POST" action="{{ route('secciones.destroy', $seccion) }}"
                                              onsubmit="return confirm('¿Eliminar la sección {{ $seccion->nombre_completo }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                    No hay secciones creadas para {{ $anio }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>