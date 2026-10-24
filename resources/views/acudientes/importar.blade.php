<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Importar acudientes desde Excel</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('errores_importacion'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="font-medium text-red-800">
                        No se importó nada. Corrige estos errores y vuelve a subir el archivo:
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
                    Cada fila vincula un acudiente con un estudiante. Los estudiantes deben estar registrados antes.
                </p>

                <a href="{{ route('acudientes.plantilla') }}"
                   class="inline-flex items-center mt-4 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-xs font-semibold uppercase tracking-widest rounded-md hover:bg-gray-50">
                    Descargar plantilla
                </a>

                <ul class="mt-5 text-sm text-gray-600 list-disc list-inside space-y-1">
                    <li>Borra las filas de ejemplo antes de subir el archivo.</li>
                    <li>No cambies los nombres de las columnas de la primera fila.</li>
                    <li>Si un acudiente tiene <strong>varios hijos</strong>, repite su <strong>documento_acudiente</strong> en una fila por hijo; en las filas repetidas puedes dejar vacíos sus datos personales.</li>
                    <li>La primera vez que aparece un acudiente son obligatorios su <strong>nombre</strong> y su <strong>correo</strong>.</li>
                    <li>Identifica al estudiante con <strong>codigo_estudiante</strong> o <strong>documento_estudiante</strong> (basta uno).</li>
                    <li>Parentesco: Madre, Padre, Abuelo/a, Tío/a, Hermano/a, Tutor legal u Otro.</li>
                    <li>En <strong>principal</strong> escribe <strong>si</strong> para marcarlo. El primer acudiente de cada estudiante queda principal automáticamente.</li>
                    <li>Si el acudiente ya existe en el sistema, solo se vincula; sus datos no se modifican.</li>
                    <li>Máximo 1000 filas. Si una fila tiene un error, no se guarda ninguna.</li>
                </ul>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900">Paso 2: Sube el archivo</h3>

                <form method="POST" action="{{ route('acudientes.importar.procesar') }}"
                      enctype="multipart/form-data" class="mt-4 space-y-5">
                    @csrf

                    <div>
                        <input type="file" name="archivo" accept=".xlsx,.xls,.csv" required
                               class="block w-full text-sm text-gray-700 border border-gray-300 rounded-md cursor-pointer">
                        <x-input-error :messages="$errors->get('archivo')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password_temporal" value="Contraseña temporal para los acudientes nuevos" />
                        <x-text-input id="password_temporal" name="password_temporal" type="text"
                                      class="mt-1 block w-full" required autocomplete="off" />
                        <p class="text-xs text-gray-500 mt-1">
                            Mínimo 8 caracteres. Cada acudiente deberá cambiarla en su primer ingreso.
                            Entrégala solo a las familias, no la publiques.
                        </p>
                        <x-input-error :messages="$errors->get('password_temporal')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('estudiantes.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
                        <x-primary-button>Importar acudientes</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>