<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Usuarios</h2>
            <a href="{{ route('usuarios.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase tracking-widest rounded-md hover:bg-gray-700">
                Nuevo usuario
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('ok'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md text-sm">{{ session('ok') }}</div>
            @endif
            @if ($errors->has('estado'))
                <div class="p-4 bg-red-100 text-red-800 rounded-md text-sm">{{ $errors->first('estado') }}</div>
            @endif

            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       placeholder="Buscar por nombre, correo o documento"
                       class="flex-1 border-gray-300 rounded-md shadow-sm">
                <select name="rol" class="border-gray-300 rounded-md shadow-sm">
                    <option value="">Todos los roles</option>
                    @foreach ($roles as $valor => $etiqueta)
                        <option value="{{ $valor }}" @selected(request('rol') === $valor)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <x-primary-button>Buscar</x-primary-button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Correo</th>
                            <th class="px-4 py-3">Documento</th>
                            <th class="px-4 py-3">Rol</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($usuarios as $usuario)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $usuario->name }}</td>
                                <td class="px-4 py-3">{{ $usuario->email }}</td>
                                <td class="px-4 py-3">{{ $usuario->documento ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $roles[$usuario->rol] ?? $usuario->rol }}</td>
                                <td class="px-4 py-3">
                                    @if ($usuario->activo)
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Activo</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('usuarios.edit', $usuario) }}"
           class="text-indigo-600 hover:underline">Editar</a>

        @if ($usuario->id !== auth()->id())
            <form method="POST" action="{{ route('usuarios.estado', $usuario) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="text-amber-600 hover:underline">
                    {{ $usuario->activo ? 'Desactivar' : 'Activar' }}
                </button>
            </form>

            <form method="POST" action="{{ route('usuarios.destroy', $usuario) }}"
                  onsubmit="return confirm('¿Seguro que quieres eliminar a {{ $usuario->name }}? Esta acción no se puede deshacer.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
            </form>
        @endif
    </div>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No se encontraron usuarios.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $usuarios->links() }}
        </div>
    </div>
</x-app-layout>