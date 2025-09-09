<x-app-layout>
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center p-4">
        <div class="w-full max-w-4xl bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-emerald-600 text-white px-6 py-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold">Crea tu torneo</h2>
                <button onclick="history.back()" class="text-white/90 hover:text-white">✕</button>
            </div>

            <!-- Body -->
            <div class="p-6">
                <form action="{{ route('admin.torneos.store') }}" method="POST" id="torneoForm" class="space-y-6">
                    @csrf

                    <!-- Top row: Deporte + Nº participantes -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                        <div>
                            <label for="deporte" class="block text-sm font-medium text-gray-700">Deporte *</label>
                            <select id="deporte" name="deporte"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Selecciona un deporte</option>
                                <option value="futbol" {{ old('deporte') == 'futbol' ? 'selected' : '' }}>Fútbol</option>
                                <option value="baloncesto" {{ old('deporte') == 'baloncesto' ? 'selected' : '' }}>Baloncesto</option>
                                <option value="voley" {{ old('deporte') == 'voley' ? 'selected' : '' }}>Vóley</option>
                                <option value="tenis" {{ old('deporte') == 'tenis' ? 'selected' : '' }}>Tenis</option>
                                <option value="padel" {{ old('deporte') == 'padel' ? 'selected' : '' }}>Pádel</option>
                                <option value="otro" {{ old('deporte') == 'otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                        </div>

                        <div>
                            <label for="num_participantes" class="block text-sm font-medium text-gray-700">Número de participantes *</label>
                            <div class="mt-1 flex items-center gap-2">
                                <button type="button" id="decBtn" class="px-3 py-1 rounded bg-gray-100 border hover:bg-gray-200">−</button>
                                <input id="num_participantes" name="num_participantes" type="number" min="2" value="{{ old('num_participantes', 8) }}"
                                    class="w-24 text-center rounded-md border-gray-300">
                                <button type="button" id="incBtn" class="px-3 py-1 rounded bg-gray-100 border hover:bg-gray-200">+</button>
                            </div>
                        </div>
                    </div>

                    <!-- Formato -->
                    <div class="border rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-3">Formato *</h3>

                        <div class="flex flex-col md:flex-row gap-6">
                            <!-- Radios -->
                            <div class="w-full md:w-1/3 space-y-4">
                                <label class="flex items-center gap-3">
                                    <input type="radio" name="fase_tipo" value="unica" checked class="fase-radio text-emerald-600">
                                    <span class="text-sm">Fase única</span>
                                </label>

                                <label class="flex items-center gap-3">
                                    <input type="radio" name="fase_tipo" value="multifase" class="fase-radio text-emerald-600">
                                    <span class="text-sm">Multifase</span>
                                </label>
                            </div>

                            <!-- Selects -->
                            <div class="w-full md:w-2/3 space-y-4">
                                <!-- Para Fase única -->
                                <div id="unicaGroup">
                                    <label class="block text-sm font-medium text-gray-700">Selecciona formato de fase</label>
                                    <select name="formato_unica" class="mt-1 block w-full rounded-md border-gray-300">
                                        <option value="cuadro_eliminatorio" {{ old('formato_unica')=='cuadro_eliminatorio' ? 'selected' : '' }}>Cuadro eliminatorio</option>
                                        <option value="todos_contra_todos" {{ old('formato_unica')=='todos_contra_todos' ? 'selected' : '' }}>Todos contra todos</option>
                                        <option value="liga" {{ old('formato_unica')=='liga' ? 'selected' : '' }}>Liga</option>
                                    </select>
                                </div>

                                <!-- Para Multifase -->
                                <div id="multiGroup" class="space-y-3 hidden">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Selecciona formato de 1ª fase</label>
                                        <select name="formato_primera" class="mt-1 block w-full rounded-md border-gray-200 bg-gray-50" disabled>
                                            <option value="grupos_todos_contra_todos">Grupos todos contra todos</option>
                                            <option value="puntos_por_grupo">Puntos por grupo</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Selecciona formato de 2ª fase</label>
                                        <select name="formato_segunda" class="mt-1 block w-full rounded-md border-gray-200 bg-gray-50" disabled>
                                            <option value="cuadro_eliminatorio">Cuadro eliminatorio</option>
                                            <option value="liguilla">Liguilla</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campos adicionales -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">Fecha inicio</label>
                            <input id="fecha_inicio" name="fecha_inicio" type="date" value="{{ old('fecha_inicio') }}"
                                class="mt-1 block w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label for="fecha_fin" class="block text-sm font-medium text-gray-700">Fecha fin</label>
                            <input id="fecha_fin" name="fecha_fin" type="date" value="{{ old('fecha_fin') }}"
                                class="mt-1 block w-full rounded-md border-gray-300">
                        </div>
                    </div>

                    <div>
                        <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300">{{ old('descripcion') }}</textarea>
                    </div>

                    <!-- Spacer para que el botón no tape el contenido -->
                    <div style="height:1px"></div>
                </form>
            </div>

            <!-- Footer / CTA -->
            <div class="px-6 py-4 bg-white border-t">
                <div class="max-w-4xl mx-auto">
                    <button type="submit" form="torneoForm"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-full text-lg uppercase tracking-wide">
                        Crea tu torneo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function(){
            const incBtn = document.getElementById('incBtn');
            const decBtn = document.getElementById('decBtn');
            const numInput = document.getElementById('num_participantes');
            const radios = document.querySelectorAll('.fase-radio');
            const unicaGroup = document.getElementById('unicaGroup');
            const multiGroup = document.getElementById('multiGroup');

            incBtn.addEventListener('click', () => {
                numInput.value = Math.max(2, parseInt(numInput.value || 0) + 1);
            });
            decBtn.addEventListener('click', () => {
                numInput.value = Math.max(2, parseInt(numInput.value || 0) - 1);
            });

            function updateFormato() {
                const val = document.querySelector('input[name="fase_tipo"]:checked').value;
                if (val === 'unica') {
                    unicaGroup.classList.remove('hidden');
                    multiGroup.classList.add('hidden');
                    // enable unica select, disable multifase selects
                    document.querySelector('select[name="formato_unica"]').disabled = false;
                    multiGroup.querySelectorAll('select').forEach(s => s.disabled = true);
                } else {
                    unicaGroup.classList.add('hidden');
                    multiGroup.classList.remove('hidden');
                    document.querySelector('select[name="formato_unica"]').disabled = true;
                    multiGroup.querySelectorAll('select').forEach(s => s.disabled = false);
                }
            }

            radios.forEach(r => r.addEventListener('change', updateFormato));
            updateFormato();
        })();
    </script>
</x-app-layout>