<x-app-layout>
    <div class="max-w-6xl mx-auto p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Torneos</h1>
            <div class="flex gap-2">
                <a href="{{ route('equipos.index') }}" class="px-4 py-2 bg-gray-100 rounded-md text-sm">Equipos</a>
                <a href="{{ route('torneos.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm">Crear torneo</a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if($torneos->isEmpty())
            <div class="p-6 bg-white rounded shadow text-center text-gray-600">
                No hay torneos creados aún. Crea uno nuevo.
            </div>
        @else
            <div class="overflow-x-auto bg-white rounded shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">#</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Deporte</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Participantes</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Formato</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Fechas</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Creado</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($torneos as $torneo)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ ucfirst($torneo->deporte ?? '—') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $torneo->numero_participantes ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    @if(($torneo->fase ?? '') === 'unica')
                                        {{ $torneo->formato_fase_unica ?? '—' }}
                                    @else
                                        {{ $torneo->formato_fase_1 ? $torneo->formato_fase_1 . ' / ' : '' }}
                                        {{ $torneo->formato_fase_2 ?? '' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ optional($torneo)->fecha_inicio ? \Carbon\Carbon::parse($torneo->fecha_inicio)->format('Y-m-d') : '—' }}
                                    @if(!empty($torneo->fecha_fin))
                                        — {{ \Carbon\Carbon::parse($torneo->fecha_fin)->format('Y-m-d') }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ optional($torneo->created_at)->format('Y-m-d') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <a href="{{ route('torneos.show', $torneo) }}" class="inline-block px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded mr-2">Ver</a>
                                    <a href="{{ route('torneos.edit', $torneo) }}" class="inline-block px-3 py-1 text-sm bg-yellow-100 text-yellow-800 rounded mr-2">Editar</a>

                                    <form action="{{ route('torneos.destroy', $torneo) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Eliminar torneo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if(method_exists($torneos, 'links'))
                    <div class="p-4">
                        {{ $torneos->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>