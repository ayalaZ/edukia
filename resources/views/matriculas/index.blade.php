<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Estudiantes de {{ $seccion->nombre_completo }} — {{ $seccion->anio }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Orientador: {{ $seccion->orientador?->user->name ?? 'Sin asignar' }}
                </p>
            </div>
            <a href="{{ route('secciones.index', ['anio' => $seccion->anio]) }}"
               class="text-sm text-gray-600 hover:underline">← Volver a secciones</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('ok'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md text-sm">{{ session('ok') }}</div>
            @endif

            @if ($errors->has('estudiantes'))
                <div class="p-4 bg-red-100 text-red-800 rounded-md text-sm">{{ $errors->first('estudiantes') }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Matriculados --}}
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-medium text-gray-900">Matriculados</h3>
                        <span class="text-sm text-gray-500">{{ $matriculas->count() }}</span>
                    </div>

                    <div class="max-h-[32rem] overflow-y-auto">
                        <table class="min-w-full text-sm">
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($matriculas as $matricula)
                                    <tr>
                                        <td class="px-4 py-2">
                                            <span class="font-medium text-gray-900">
                                                {{ $matricula->estudiante->apellidos }}, {{ $matricula->estudiante->nombres }}
                                            </span>
                                            <span class="block text-xs text-gray-500">
                                                {{ $matricula->estudiante->codigo ?? 'Sin código' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <form method="POST" action="{{ route('matriculas.destroy', $matricula) }}"
                                                  onsubmit="return confirm('¿Quitar a {{ $matricula->estudiante->nombres }} de esta sección?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">Quitar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-4 py-8 text-center text-gray-500">Todavía no hay estudiantes en esta sección.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Disponibles --}}
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="font-medium text-gray-900">Estudiantes sin sección en {{ $seccion->anio }}</h3>

                        <form method="GET" class="flex gap-2 mt-3">
                            <input type="text" name="buscar" value="{{ request('buscar') }}"
                                   placeholder="Buscar por nombre, documento o código"
                                   class="flex-1 border-gray-300 rounded-md shadow-sm text-sm">
                            <x-primary-button>Buscar</x-primary-button>
                        </form>
                    </div>

                    <form method="POST" action="{{ route('matriculas.store', $seccion) }}">
                        @csrf

                        <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between text-sm">
                            <label class="flex items-center gap-2 text-gray-600">
                                <input type="checkbox" id="marcar-todos" class="rounded border-gray-300">
                                Marcar todos los que se ven
                            </label>
                            <span class="text-gray-500">{{ $disponibles->count() }} disponibles</span>
                        </div>

                        <div class="max-h-[26rem] overflow-y-auto">
                            <table class="min-w-full text-sm">
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($disponibles as $estudiante)
                                        <tr>
                                            <td class="px-4 py-2 w-8">
                                                <input type="checkbox" name="estudiantes[]" value="{{ $estudiante->id }}"
                                                       class="fila-check rounded border-gray-300">
                                            </td>
                                            <td class="px-4 py-2">
                                                <span class="font-medium text-gray-900">
                                                    {{ $estudiante->apellidos }}, {{ $estudiante->nombres }}
                                                </span>
                                                <span class="block text-xs text-gray-500">
                                                    {{ $estudiante->codigo ?? 'Sin código' }} · {{ $estudiante->fecha_nacimiento->age }} años
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-4 py-8 text-center text-gray-500">
                                                No hay estudiantes activos sin sección.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($disponibles->isNotEmpty())
                            <div class="px-4 py-3 border-t border-gray-100 flex justify-end">
                                <x-primary-button>Matricular seleccionados</x-primary-button>
                            </div>
                        @endif
                    </form>

                    @if ($disponibles->count() === 200)
                        <p class="px-4 pb-3 text-xs text-gray-500">
                            Se muestran los primeros 200. Usa el buscador para encontrar a otros.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('marcar-todos')?.addEventListener('change', function () {
            document.querySelectorAll('.fila-check').forEach(c => c.checked = this.checked);
        });
    </script>
</x-app-layout>