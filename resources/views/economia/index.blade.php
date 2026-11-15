<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Configuración Económica
        </h2>
    </x-slot>

    <div class="py-8">

        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">

                <div class="p-6">

                    <form method="POST"
                          action="{{ route('economia.update', $config) }}">

                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Valor de Matrícula
                                </label>

                                <input
                                    type="number"
                                    name="matricula"
                                    step="0.01"
                                    min="0"
                                    value="{{ old('matricula', $config->matricula) }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm"
                                    required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Valor de Mensualidad
                                </label>

                                <input
                                    type="number"
                                    name="mensualidad"
                                    step="0.01"
                                    min="0"
                                    value="{{ old('mensualidad', $config->mensualidad) }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm"
                                    required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    % Descuento Becado
                                </label>

                                <input
                                    type="number"
                                    name="descuento_becado"
                                    min="0"
                                    max="100"
                                    value="{{ old('descuento_becado', $config->descuento_becado) }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm"
                                    required>

                                <p class="mt-1 text-xs text-gray-500">
                                    Ejemplo: 50 equivale al 50% de descuento.
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    % Descuento Beca Especial
                                </label>

                                <input
                                    type="number"
                                    name="descuento_beca_especial"
                                    min="0"
                                    max="100"
                                    value="{{ old('descuento_beca_especial', $config->descuento_beca_especial) }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm"
                                    required>

                                <p class="mt-1 text-xs text-gray-500">
                                    Ejemplo: 100 equivale a cuota completamente gratuita.
                                </p>
                            </div>

                        </div>

                        <div class="mt-8 flex justify-end">

                            <x-primary-button>
                                Guardar Configuración
                            </x-primary-button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>