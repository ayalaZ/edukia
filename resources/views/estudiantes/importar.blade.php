<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Importar estudiantes desde Excel</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('errores_importacion'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="font-medium text-red-800">
                        No se importó ningún estudiante. Corrige estos errores y vuelve a subir el archivo:
                    </p>
                    <ul class="mt-3 space-y-1 text-sm text-red-700 max-h-96 overflow-y-auto">
                        @foreach (session('errores_importacion') as $error)
                            <li>
                                <span class="font-semibold">Fila {{ $error['fila'] }}:</span>
                                {{ implode(' ', $error['mensajes']) }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900">Paso 1: Descarga la plantilla</h3>
                <p class="text-sm text-gray-600 mt-1">
                    Llénala con los datos de los estudiantes, una fila por estudiante.
                </p>

                <a href="{{ route('estudiantes.plantilla') }}"
                   class="inline-flex items-center mt-4 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-xs font-semibold uppercase tracking-widest rounded-md hover:bg-gray-50">
                    Descargar plantilla
                </a>

                <ul class="mt-5 text-sm text-gray-600 list-disc list-inside space-y-1">
                    <li>No cambies los nombres de las columnas de la primera fila.</li>
                    <li>Obligatorios: <strong>nombres</strong>, <strong>apellidos</strong> y <strong>fecha_nacimiento</strong>.</li>
                    <li>La fecha va como <strong>AAAA-MM-DD</strong> (por ejemplo 2009-03-15).</li>
                    <li>El sexo es <strong>M</strong> o <strong>F</strong> (opcional).</li>
                    <li>El código y el documento no se pueden repetir, ni en el archivo ni en el sistema.</li>
                    <li>Máximo 1000 filas por archivo.</li>
                    <li>Si una fila tiene un error, no se guarda ninguna: así evitas duplicados al volver a subirlo.</li>
                </ul>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900">Paso 2: Sube el archivo</h3>

                <form method="POST" action="{{ route('estudiantes.importar.procesar') }}"
                      enctype="multipart/form-data" class="mt-4">
                    @csrf

                    <input type="file" name="archivo" accept=".xlsx,.xls,.csv" required
                           class="block w-full text-sm text-gray-700 border border-gray-300 rounded-md cursor-pointer">
                    <x-input-error :messages="$errors->get('archivo')" class="mt-2" />

                    <div class="flex items-center justify-end gap-4 mt-6">
                        <a href="{{ route('estudiantes.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
                        <x-primary-button>Importar estudiantes</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>