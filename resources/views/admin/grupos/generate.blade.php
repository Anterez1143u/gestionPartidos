<x-app-layout>
    <div class="max-w-4xl mx-auto p-6">
        <div class="flex items-center justify-between mb-6">
            @php
                $sessionGroups = session('generated_groups', null);
                $sessionGroupsTorneo = session('generated_groups_torneo', null);
                $totalTeams = 0;
                if (is_array($sessionGroups)) {
                    foreach ($sessionGroups as $g) {
                        $totalTeams += is_array($g) ? count($g) : 0;
                    }
                }
                $canViewMatches = false;
                if ($sessionGroups && $sessionGroupsTorneo) {
                    $torneoObj = \App\Models\Torneo::find($sessionGroupsTorneo);
                    $hasFechaInicio = $torneoObj && !empty($torneoObj->fecha_inicio);
                    if ($hasFechaInicio && $totalTeams >= 2) {
                        $canViewMatches = true;
                    }
                }
            @endphp

            <h1 class="text-2xl font-bold">Generación de Grupos / Llaves</h1>
            <div class="flex gap-2">
                <a href="{{ route('torneos.index') }}" class="px-3 py-2 bg-gray-100 rounded text-sm">Torneos</a>

                {{-- botón para ir a partidos: solo mostrar cuando estén completos los datos --}}
                @if($canViewMatches)
                    <a id="viewMatchesBtn" href="{{ route('partidos.index') }}" class="px-3 py-2 bg-indigo-600 text-white rounded text-sm">Ver partidos</a>
                @else
                    <a id="viewMatchesBtn" style="display:none" href="{{ route('partidos.index') }}" class="px-3 py-2 bg-indigo-600 text-white rounded text-sm">Ver partidos</a>
                @endif
            </div>
        </div>

        <form id="generateGroupsForm" class="bg-white p-6 rounded shadow space-y-4" method="POST" action="{{ route('grupos.generate.run') }}">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700">Torneo</label>
                <select id="torneo_id" name="torneo_id" class="mt-1 block w-full rounded-md border-gray-300" required>
                    <option value="">Selecciona un torneo</option>
                    @foreach($torneos as $t)
                        <option value="{{ $t->id }}"
                            {{ (int)old('torneo_id', $selected ?? '') === $t->id ? 'selected' : '' }}>
                            {{ $t->deporte ?? $t->nombre ?? 'Torneo #'.$t->id }} @if($t->numero_participantes) ({{ $t->numero_participantes }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo</label>
                    <select id="tipo" name="tipo" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="grupos" {{ old('tipo', $tipo ?? 'grupos') === 'grupos' ? 'selected' : '' }}>Grupos</option>
                        <option value="llaves" {{ old('tipo', $tipo ?? '') === 'llaves' ? 'selected' : '' }}>Llaves / Cuadro eliminatorio</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Equipos por grupo</label>
                    <input id="equipos_por_grupo" name="equipos_por_grupo" type="number" min="2"
                           value="{{ old('equipos_por_grupo', $equipos_por_grupo ?? 4) }}"
                           class="mt-1 block w-full rounded-md border-gray-300">
                </div>

                <div class="flex items-end md:col-span-3">
                    <div class="w-full grid grid-cols-2 gap-2">
                        <button id="generateBtn" class="w-full bg-emerald-600 text-white py-2 rounded">Generar calendario</button>
                        <button id="saveScheduleBtn" class="w-full bg-sky-600 text-white py-2 rounded" style="display:none">Guardar calendario</button>
                    </div>
                </div>
            </div>
        </form>

        <div id="preview" class="mt-6">
            @if(!empty($groups))
                <div class="bg-white rounded shadow p-4 space-y-3" id="serverGroups">
                    @foreach($groups as $i => $g)
                        <div data-group-index="{{ $i }}">
                            <strong>Grupo {{ $i + 1 }}</strong>
                            <ul class="list-disc pl-5 mt-1">
                                @foreach($g as $team)
                                    <li data-team="{{ e($team) }}">{{ e($team) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-4 bg-yellow-50 rounded">No hay grupos generados todavía.</div>
            @endif
        </div>

        {{-- Formulario de programación (usa los grupos visibles en preview) --}}
        <div class="mt-6 bg-white p-6 rounded shadow">
            <h2 class="text-lg font-semibold mb-3">Generar horario desde grupos</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha inicio</label>
                    <input id="fecha_inicio_sched" type="date"
                        value="{{ old('fecha_inicio', optional($torneos->firstWhere('id', $selected))->fecha_inicio ?? '') }}"
                        class="mt-1 block w-full rounded-md border-gray-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Días entre jornadas</label>
                    <input id="dias_entre_sched" type="number" min="1" value="7" class="mt-1 block w-full rounded-md border-gray-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Programar en</label>
                    <select id="tipo_horario_sched" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="todo">Todos los días</option>
                        <option value="entre_semana">Entre semana (Lun-Vie)</option>
                        <option value="fines_semana">Fines de semana (Sáb-Dom)</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <button id="generateScheduleBtn" class="w-full bg-indigo-600 text-white py-2 rounded">Generar horario usando estos grupos</button>
                </div>
            </div>

            <div id="scheduleResult" class="mt-4"></div>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div id="confirmModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
            <h3 class="text-lg font-semibold mb-2">Confirmar guardado del calendario</h3>
            <div id="confirmSummary" class="text-sm text-gray-700 mb-4">
                <!-- contenido llenado por JS -->
            </div>
            <div class="flex justify-end gap-2">
                <button id="cancelConfirmBtn" class="px-4 py-2 bg-gray-200 rounded">Cancelar</button>
                <button id="confirmSaveBtn" class="px-4 py-2 bg-emerald-600 text-white rounded">Confirmar y guardar</button>
            </div>
        </div>
    </div>

    <script>
    (function(){
        // helpers y funciones definidas primero
        function escapeHtml(s){ return String(s).replace(/[&<>"]/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' })[c]); }

        const normalizeDate = (d) => {
            if (!d) return null;
            // aceptar DD/MM/YYYY o YYYY-MM-DD
            if (d.includes('/')) {
                const parts = d.split('/');
                if (parts.length === 3) return `${parts[2]}-${parts[1].padStart(2,'0')}-${parts[0].padStart(2,'0')}`;
            }
            return d;
        };

        function parseDateToObj(d) {
            const iso = normalizeDate(d);
            if (!iso) return null;
            const parts = iso.split('-').map(Number);
            return new Date(parts[0], parts[1]-1, parts[2]);
        }

        function formatDateLocal(dateObj) {
            if (!dateObj) return '';
            return dateObj.toLocaleDateString('es-ES');
        }

        function nextAllowedDate(dateObj, tipoHorario) {
            const d = new Date(dateObj.getTime());
            while (true) {
                const wd = d.getDay(); // 0 domingo .. 6 sabado
                if (tipoHorario === 'fines_semana') {
                    if (wd === 0 || wd === 6) break;
                } else if (tipoHorario === 'entre_semana') {
                    if (wd >= 1 && wd <= 5) break;
                } else {
                    break;
                }
                d.setDate(d.getDate() + 1);
            }
            return d;
        }

        function generateRoundRobinForGroup(members, startDateStr, diasEntre, tipoHorario) {
            // members: array de strings o ids; startDateStr: value from input
            const startObj = parseDateToObj(startDateStr) || new Date();
            let currentDate = nextAllowedDate(startObj, tipoHorario);
            let ids = members.slice();
            const n0 = ids.length;
            if (n0 < 2) return [];
            // if odd add null bye
            if (ids.length % 2 === 1) ids.push(null);
            const n = ids.length;
            const rounds = n - 1;
            const half = n / 2;
            const matches = [];
            for (let r = 0; r < rounds; r++) {
                for (let i = 0; i < half; i++) {
                    const a = ids[i];
                    const b = ids[n-1-i];
                    if (a === null || b === null) continue;
                    matches.push({
                        fecha: formatDateLocal(currentDate),
                        fecha_iso: currentDate.toISOString().slice(0,10),
                        local: String(a),
                        visitante: String(b)
                    });
                }
                // rotate (circle method)
                const fixed = ids.shift();
                const last = ids.pop();
                ids.unshift(fixed);
                ids.push(last);
                // advance date
                const next = new Date(currentDate.getTime());
                next.setDate(next.getDate() + Number(diasEntre || 7));
                currentDate = nextAllowedDate(next, tipoHorario);
            }
            return matches;
        }

        // función para generar horario completo para todos los grupos (secuencial por grupo)
        function generateSchedulePreview(groups, startDate, diasEntre, tipoHorario) {
            const all = [];
            let cursorDate = startDate; // string
            for (let gi = 0; gi < groups.length; gi++) {
                const g = groups[gi];
                const groupMatches = generateRoundRobinForGroup(g, cursorDate, diasEntre, tipoHorario);
                // anexar group_index
                groupMatches.forEach(m => m.group_index = gi);
                all.push(...groupMatches);
                // avanzar cursorDate al último match + diasEntre para el siguiente grupo
                if (groupMatches.length) {
                    const lastIso = groupMatches[groupMatches.length-1].fecha_iso;
                    const lastObj = new Date(lastIso + 'T00:00:00');
                    const next = new Date(lastObj.getTime());
                    next.setDate(next.getDate() + Number(diasEntre || 7));
                    cursorDate = `${next.getFullYear()}-${String(next.getMonth()+1).padStart(2,'0')}-${String(next.getDate()).padStart(2,'0')}`;
                }
            }
            return all;
        }

        // decide visibilidad del botón Ver partidos
        function updateViewMatchesVisibility() {
            const viewMatchesBtn = document.getElementById('viewMatchesBtn');
            if (!viewMatchesBtn) return;
            const fecha = document.getElementById('fecha_inicio_sched').value;
            const hasFecha = fecha && fecha.trim() !== '';
            const serverGroupsEl = document.getElementById('serverGroups');
            const serverHasGroups = serverGroupsEl && serverGroupsEl.querySelectorAll('[data-group-index]').length > 0;
            const hasLastGroups = Array.isArray(lastGroups) && lastGroups.length > 0;
            if ((hasLastGroups || serverHasGroups) && hasFecha) {
                viewMatchesBtn.style.display = 'inline-block';
            } else {
                viewMatchesBtn.style.display = 'none';
            }
        }

        // postJson con diagnóstico
        async function postJson(url, payload) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const text = await res.text();
            try { return { ok: res.ok, status: res.status, json: JSON.parse(text) }; }
            catch(e) { return { ok: res.ok, status: res.status, text }; }
        }

        // variables DOM
        const form = document.getElementById('generateGroupsForm');
        const preview = document.getElementById('preview');
        const scheduleBtn = document.getElementById('generateScheduleBtn');
        const resultado = document.getElementById('scheduleResult');
        let lastGroups = null;

        // cargar grupos server-side (si existen)
        const serverGroupsEl = document.getElementById('serverGroups');
        if (serverGroupsEl) {
            const groups = [];
            serverGroupsEl.querySelectorAll('[data-group-index]').forEach(div => {
                const items = [];
                div.querySelectorAll('li').forEach(li => items.push(li.getAttribute('data-team') || li.textContent.trim()));
                groups.push(items);
            });
            if (groups.length) lastGroups = groups;
        }

        // si hay grupos precargados y fecha, generar preview automáticamente
        function tryAutoPreview() {
            const fecha = document.getElementById('fecha_inicio_sched').value;
            if (!fecha) return;
            if (!lastGroups || !lastGroups.length) return;
            const dias = document.getElementById('dias_entre_sched').value || 7;
            const tipo = document.getElementById('tipo_horario_sched').value || 'todo';
            const matches = generateSchedulePreview(lastGroups, fecha, dias, tipo);
            renderScheduleResult(matches, true);
            updateViewMatchesVisibility();
        }

        function renderScheduleResult(matches, fromPreview = false) {
            if (!matches || !matches.length) {
                resultado.innerHTML = '<div class="p-3 text-sm text-gray-600">No hay partidos generados.</div>';
                return;
            }
            let html = '<div class="bg-white rounded shadow p-4">';
            const counts = {};
            matches.forEach(m => {
                html += `<div class="border-b py-2"><strong>${escapeHtml(m.fecha)}</strong> — ${escapeHtml(m.local)} <span class="text-xs text-gray-400">vs</span> ${escapeHtml(m.visitante)} <span class="text-sm text-gray-500"> (grupo ${Number(m.group_index)+1})</span></div>`;
                counts[m.fecha] = (counts[m.fecha] || 0) + 1;
            });
            html += '</div>';
            // gráfico simple de barras inline
            html += '<div class="mt-4">';
            Object.keys(counts).forEach(date => {
                const n = counts[date];
                html += `<div class="mb-2 text-xs"><strong>${escapeHtml(date)}</strong> — ${n} partidos<div class="h-2 bg-gray-200 rounded mt-1"><div style="width:${Math.min(100,n*10)}%" class="h-2 bg-green-500 rounded"></div></div></div>`;
            });
            html += '</div>';
            resultado.innerHTML = html;
            if (!fromPreview) {
                // si fue generación real, permitimos ver partidos
                const viewBtn = document.getElementById('viewMatchesBtn');
                if (viewBtn) viewBtn.style.display = 'inline-block';
            }
        }

        // manejar formulario generación de grupos (POST)
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            preview.innerHTML = '<div class="p-4 bg-gray-50 rounded">Generando…</div>';
            const data = new FormData(form);
            try {
                const res = await fetch("{{ route('grupos.generate.run') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value },
                    body: data
                });
                const json = await res.json();
                if (json.groups) {
                    lastGroups = json.groups;
                    // render preview
                    let html = '<div class="bg-white rounded shadow p-4 space-y-3">';
                    json.groups.forEach((g, i) => {
                        html += `<div data-group-index="${i}"><strong>Grupo ${i+1}</strong><ul class="list-disc pl-5 mt-1">${g.map(t=>`<li data-team="${escapeHtml(t)}">${escapeHtml(t)}</li>`).join('')}</ul></div>`;
                    });
                    html += '</div>';
                    preview.innerHTML = html;
                    tryAutoPreview();
                    updateViewMatchesVisibility();
                } else {
                    preview.innerHTML = '<div class="p-4 bg-yellow-50">Respuesta inesperada</div>';
                }
            } catch (err) {
                preview.innerHTML = '<div class="p-4 bg-red-50 text-red-700 rounded">Error: ' + err.message + '</div>';
            }
        });

        // manejar click generar horario (envío al controlador)
        scheduleBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            resultado.innerHTML = '<div class="p-3 bg-gray-50 rounded">Generando horario…</div>';
            if (!lastGroups || !lastGroups.length) {
                const serverHas = serverGroupsEl && serverGroupsEl.querySelectorAll('[data-group_index]').length > 0;
                if (!serverHas) {
                    resultado.innerHTML = '<div class="p-3 bg-yellow-50 text-yellow-700 rounded">No hay grupos generados. Genera los grupos antes.</div>';
                    return;
                }
            }
            const rawDate = document.getElementById('fecha_inicio_sched').value;
            const fecha_inicio = normalizeDate(rawDate);
            const payload = {
                torneo_id: document.getElementById('torneo_id').value,
                fecha_inicio,
                dias_entre: document.getElementById('dias_entre_sched').value || 7,
                tipo_horario: document.getElementById('tipo_horario_sched').value || 'todo',
                groups: lastGroups
            };
            try {
                const res = await postJson("{{ route('partidos.generateCalendar') }}", payload);
                if (!res.ok) {
                    if (res.text) {
                        resultado.innerHTML = `<div class="p-3 bg-red-50 text-red-700 rounded">Error servidor (HTML):<pre style="white-space:pre-wrap;max-height:200px;overflow:auto">${escapeHtml(res.text)}</pre></div>`;
                        console.error('Respuesta HTML del servidor:', res.text);
                    } else if (res.json && res.json.error) {
                        resultado.innerHTML = `<div class="p-3 bg-red-50 text-red-700 rounded">${escapeHtml(JSON.stringify(res.json.error))}</div>`;
                    } else {
                        resultado.innerHTML = `<div class="p-3 bg-red-50 text-red-700 rounded">Error status ${res.status}</div>`;
                    }
                    return;
                }
                const json = res.json || {};
                renderScheduleResult(json.matches || []);
                // guardar matches en variable global y mostrar botón Guardar
                window.__lastGeneratedMatches = json.matches || [];
                toggleSaveButton(window.__lastGeneratedMatches.length > 0);
            } catch (err) {
                resultado.innerHTML = '<div class="p-3 bg-red-50 text-red-700 rounded">Error: ' + err.message + '</div>';
            }
        });

        // mostrar botón Guardar cuando haya matches generados en UI
        function toggleSaveButton(visible) {
            const btn = document.getElementById('saveScheduleBtn');
            if (!btn) return;
            btn.style.display = visible ? 'block' : 'none';
        }

        // guardar calendario en servidor
        async function saveSchedule(matches) {
            const torneo_id = document.getElementById('torneo_id').value;
            if (!torneo_id) return alert('Selecciona un torneo antes de guardar.');
            try {
                const res = await fetch("{{ route('partidos.saveSchedule') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ torneo_id, matches })
                });
                if (!res.ok) {
                    const text = await res.text();
                    alert('Error al guardar: ' + text);
                    return;
                }
                const json = await res.json();
                alert('Guardados ' + (json.saved ?? 0) + ' partidos.');
                // opcional: recargar lista desde servidor
                // fetchSavedMatches(torneo_id);
                hideConfirmModal();
            } catch (err) {
                alert('Error al guardar: ' + err.message);
                hideConfirmModal();
            }
        }

        // recuperar partidos ya guardados para un torneo
        async function fetchSavedMatches(torneoId) {
            if (!torneoId) return;
            try {
                const res = await fetch(`/admin/partidos?torneo=${encodeURIComponent(torneoId)}`, {
                    headers: { Accept: 'application/json' }
                });
                if (!res.ok) return;
                const json = await res.json();
                // esperamos json.matches en la respuesta (implementar en controller index)
                // renderMatchesInteractive(json.matches || [], json.type || 'liga');
                // drawChart(json.counts || {});
                toggleSaveButton(false);
            } catch (err) {
                console.error('fetchSavedMatches', err);
            }
        }

        // enganchar botón Guardar: ahora abre modal de confirmación
        document.getElementById('saveScheduleBtn').addEventListener('click', async () => {
            const matches = window.__lastGeneratedMatches || [];
            // mostrar resumen en modal
            const summaryEl = document.getElementById('confirmSummary');
            const torneoEl = document.getElementById('torneo_id');
            const torneoText = torneoEl ? torneoEl.options[torneoEl.selectedIndex]?.text || '' : '';
            summaryEl.innerHTML = `<div class="mb-2"><strong>Torneo:</strong> ${escapeHtml(torneoText)}</div>
                                   <div class="mb-2"><strong>Partidos a guardar:</strong> ${matches.length}</div>
                                   <div class="text-xs text-gray-600">Confirma que deseas persistir estos partidos en la base de datos.</div>`;
            showConfirmModal();
        });

        // modal helpers
        function showConfirmModal() {
            const m = document.getElementById('confirmModal');
            if (!m) return;
            m.classList.remove('hidden');
            m.classList.add('flex');
        }
        function hideConfirmModal() {
            const m = document.getElementById('confirmModal');
            if (!m) return;
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        document.getElementById('cancelConfirmBtn').addEventListener('click', () => hideConfirmModal());
        document.getElementById('confirmSaveBtn').addEventListener('click', async () => {
            // deshabilitar botón para evitar doble submit
            const btn = document.getElementById('confirmSaveBtn');
            btn.disabled = true;
            btn.textContent = 'Guardando...';
            try {
                await saveSchedule(window.__lastGeneratedMatches || []);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Confirmar y guardar';
            }
        });

        // cuando se genera el calendario (respuesta del POST), guardamos las matches en variable global y mostramos Guardar
        // además, cuando se cambia el select de torneo, intentar cargar calendario guardado:
        document.getElementById('torneo_id').addEventListener('change', (e) => {
            const val = e.target.value;
            if (val) fetchSavedMatches(val);
            else { /* limpiar previews */ }
        });

        // mostrar preview automático si hay datos al cargar
        tryAutoPreview();
        updateViewMatchesVisibility();
    })();
    </script>
</x-app-layout>