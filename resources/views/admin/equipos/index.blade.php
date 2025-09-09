<x-app-layout>
    <div class="max-w-6xl mx-auto p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Equipos</h1>
            <div class="flex gap-2">
                @if($equipos->count() >= 4)
                    <a href="{{ route('grupos.generate') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm">
                        Generar grupos
                    </a>
                @endif
                <a href="{{ route('torneos.index') }}" class="px-4 py-2 bg-gray-100 rounded-md text-sm">Torneos</a>
                <a href="{{ route('equipos.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm">Crear equipo</a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if($equipos->isEmpty())
            <div class="p-6 bg-white rounded shadow text-center text-gray-600">
                No hay equipos aún. Crea uno nuevo.
            </div>
        @else
            <div class="overflow-x-auto bg-white rounded shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">#</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Nombre</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Torneo</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Categoría</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Jugadores</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($equipos as $equipo)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $equipo->nombre }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ optional($equipo->torneo)->deporte ?? optional($equipo->torneo)->nombre ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $equipo->categoria ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ is_array($equipo->jugadores) ? count($equipo->jugadores) : ($equipo->jugadores ? 1 : 0) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <a href="{{ route('equipos.edit', $equipo) }}" class="inline-block px-3 py-1 text-sm bg-yellow-100 text-yellow-800 rounded mr-2">Editar</a>

                                    <form action="{{ route('equipos.destroy', $equipo) }}" method="POST" class="inline-block" onsubmit="return confirm('Eliminar equipo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>