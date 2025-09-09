<x-app-layout>
    <div class="max-w-3xl mx-auto p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">
                {{ isset($equipo) ? 'Editar equipo' : 'Crear equipo' }}
            </h1>
            <a href="{{ route('equipos.index') }}" class="px-3 py-2 bg-gray-100 rounded text-sm">Volver</a>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ isset($equipo) ? route('equipos.update', $equipo) : route('equipos.store') }}"
              method="POST"
              id="equipoForm"
              class="space-y-6 bg-white p-6 rounded shadow">
            @csrf
            @if(isset($equipo))
                @method('PUT')
            @endif

            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre *</label>
                <input id="nombre" name="nombre" type="text"
                    value="{{ old('nombre', $equipo->nombre ?? '') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            </div>

            <div>
                <label for="torneo_id" class="block text-sm font-medium text-gray-700">Torneo *</label>
                <select id="torneo_id" name="torneo_id" class="mt-1 block w-full rounded-md border-gray-300" required>
                    <option value="">Selecciona un torneo</option>
                    @foreach($torneos as $torneo)
                        <option value="{{ $torneo->id }}"
                            {{ old('torneo_id', $equipo->torneo_id ?? '') == $torneo->id ? 'selected' : '' }}>
                            {{ $torneo->deporte ?? $torneo->nombre ?? 'Torneo #'.$torneo->id }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Jugadores *</label>
                <div id="jugadoresList" class="space-y-2 mt-2">
                    @php
                        $oldJugadores = old('jugadores', $equipo->jugadores ?? []);
                        if (!is_array($oldJugadores)) $oldJugadores = json_decode($oldJugadores, true) ?? [$oldJugadores];
                    @endphp

                    @if(count($oldJugadores) > 0)
                        @foreach($oldJugadores as $j)
                            <div class="flex gap-2">
                                <input type="text" name="jugadores[]" value="{{ $j }}" class="flex-1 rounded-md border-gray-300" required>
                                <button type="button" class="removeJugador px-3 py-1 bg-red-100 text-red-700 rounded">Eliminar</button>
                            </div>
                        @endforeach
                    @else
                        <div class="flex gap-2">
                            <input type="text" name="jugadores[]" placeholder="Nombre jugador" class="flex-1 rounded-md border-gray-300" required>
                            <button type="button" class="removeJugador px-3 py-1 bg-red-100 text-red-700 rounded">Eliminar</button>
                        </div>
                    @endif
                </div>

                <div class="mt-2">
                    <button type="button" id="addJugador" class="px-4 py-2 bg-emerald-600 text-white rounded">Añadir jugador</button>
                </div>
            </div>

            <div>
                <label for="categoria" class="block text-sm font-medium text-gray-700">Categoría</label>
                <input id="categoria" name="categoria" type="text" value="{{ old('categoria', $equipo->categoria ?? '') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded">
                    {{ isset($equipo) ? 'Actualizar equipo' : 'Registrar equipo' }}
                </button>
            </div>
        </form>
    </div>

    <script>
        (function(){
            const addBtn = document.getElementById('addJugador');
            const list = document.getElementById('jugadoresList');

            function makeRow(value = '') {
                const wrapper = document.createElement('div');
                wrapper.className = 'flex gap-2';
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'jugadores[]';
                input.placeholder = 'Nombre jugador';
                input.value = value;
                input.className = 'flex-1 rounded-md border-gray-300';
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'removeJugador px-3 py-1 bg-red-100 text-red-700 rounded';
                btn.textContent = 'Eliminar';
                btn.addEventListener('click', () => wrapper.remove());
                wrapper.appendChild(input);
                wrapper.appendChild(btn);
                return wrapper;
            }

            addBtn.addEventListener('click', () => {
                list.appendChild(makeRow());
            });

            document.querySelectorAll('.removeJugador').forEach(b => {
                b.addEventListener('click', (e) => {
                    e.target.closest('.flex').remove();
                });
            });
        })();
    </script>
</x-app-layout>