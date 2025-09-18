<x-app-layout>
    @php
        $canViewMatches = $canViewMatches ?? false;
        $tipo = $tipo ?? 'grupos';
        $equipos_por_grupo = (int)($equipos_por_grupo ?? 4);
        $minReq = $tipo === 'grupos' ? max(2, $equipos_por_grupo) : 2;
    @endphp
    <style>
        body {
            background: #1b2e47 !important;
        }
        .grupos-bg {
            background: #1b2e47;
            min-height: 100vh;
            padding-top: 40px;
        }
        .grupos-card {
            background: #232946;
            color: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(13,110,253,0.10);
            padding: 36px 32px;
            margin-bottom: 32px;
        }
        .grupos-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }
        .grupos-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #00c896;
            margin-bottom: 0;
        }
        .grupos-header .btn {
            font-weight: 600;
            border-radius: 8px;
            padding: 10px 22px;
            margin-right: 8px;
            background: #00c896;
            color: #fff;
            text-decoration: none;
            transition: background .2s;
        }
        .grupos-header .btn:last-child { margin-right: 0; }
        .grupos-header .btn:hover {
            background: #0d6efd;
        }
        .form-label {
            font-weight: 700;
            font-size: 1.08rem;
            color: #fff;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }
        .form-control, .form-select, input[type="number"], input[type="date"] {
            background: #2a3550 !important;
            color: #fff !important;
            border-radius: 8px !important;
            border: 1.5px solid #00c896 !important;
            margin-bottom: 18px;
            font-size: 1.05rem;
            box-shadow: 0 2px 8px rgba(0,200,150,0.05);
        }
        .form-control:focus, .form-select:focus, input[type="number"]:focus, input[type="date"]:focus {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 2px #0d6efd33;
        }
        .btn-generate, .btn-save {
            background: #00c896;
            color: #fff;
            font-weight: 700;
            border-radius: 8px;
            padding: 14px 28px;
            font-size: 1.15rem;
            border: none;
            transition: background .2s;
            width: 100%;
            box-shadow: 0 2px 8px rgba(0,200,150,0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-generate:hover, .btn-save:hover {
            background: #0d6efd;
        }
        .preview-card, .schedule-card {
            background: #232946;
            color: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(13,110,253,0.08);
            padding: 24px;
            margin-bottom: 24px;
        }
        .bg-yellow-50 {
            background: #ffe066 !important;
            color: #232946 !important;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
        }
        .bg-white {
            background: #232946 !important;
            color: #fff !important;
        }
        .shadow {
            box-shadow: 0 8px 32px rgba(13,110,253,0.10) !important;
        }
        .text-emerald-600 {
            color: #00c896 !important;
        }
        .text-indigo-600 {
            color: #0d6efd !important;
        }
        .rounded {
            border-radius: 14px !important;
        }
        .btn-indigo {
            background: #0d6efd;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            padding: 10px 22px;
            border: none;
            transition: background .2s;
        }
        .btn-indigo:hover {
            background: #00c896;
        }
    </style>
    <div class="grupos-bg">
        <div class="max-w-4xl mx-auto p-6">
            <div class="grupos-header">
                <h1>Generación de Grupos / Llaves</h1>
                <div>
                    <a href="{{ route('torneos.index') }}" class="btn">Torneos</a>
                    @if($canViewMatches)
                        <a id="viewMatchesBtn" href="{{ route('partidos.index') }}" class="btn btn-indigo">Ver partidos</a>
                    @else
                        <a id="viewMatchesBtn" style="display:none" href="{{ route('partidos.index') }}" class="btn btn-indigo">Ver partidos</a>
                    @endif
                </div>
            </div>

            <form id="generateGroupsForm" class="grupos-card shadow space-y-4" method="POST" action="{{ route('grupos.generate.run') }}">
                @csrf
                <div>
                    <label class="form-label">Torneo</label>
                    <select id="torneo_id" name="torneo_id" class="form-select" required>
                        <option value="">Selecciona un torneo</option>
                        @foreach($torneos as $t)
                            <option value="{{ $t->id }}"

                                {{ (int)old('torneo_id', $selected ?? '') === $t->id ? 'selected' : '' }}>
                                {{ ucfirst($t->deporte) }} @if($t->numero_participantes) ({{ $t->numero_participantes }}) @endif
                            </option>
                        @endforeach
                    </select>
                    <div id="equiposResumen" class="small" style="margin-top:-8px;margin-bottom:12px;color:#a7c6d8">
                        Cargando equipos…
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Tipo</label>
                        <select id="tipo" name="tipo" class="form-select">
                            <option value="grupos" {{ old('tipo', $tipo ?? 'grupos') === 'grupos' ? 'selected' : '' }}>Grupos</option>
                            <option value="llaves" {{ old('tipo', $tipo ?? '') === 'llaves' ? 'selected' : '' }}>Llaves / Cuadro eliminatorio</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Equipos por grupo</label>
                        <input id="equipos_por_grupo" name="equipos_por_grupo" type="number" min="2"
                               value="{{ old('equipos_por_grupo', $equipos_por_grupo ?? 4) }}"
                               class="form-control">
                    </div>
                    <div class="flex items-end md:col-span-3">
                        <div class="w-full grid grid-cols-2 gap-2">
                            <button id="generateBtn" class="btn-generate">Generar grupos</button>
                            <button id="saveScheduleBtn" class="btn-save" style="display:none">Guardar grupos</button>
                        </div>
                    </div>
                </div>
            </form>

            <div id="preview" class="preview-card mt-6">
                @if(!empty($groups))
                    <div class="bg-white rounded shadow p-4" id="serverGroups">
                        @foreach($groups as $i => $g)
                            <div data-group-index="{{ $i }}">
                                <strong>Grupo {{ $i + 1 }}</strong>
                                <ul class="list-disc pl-5 mt-1">
                                    @foreach($g as $team)
                                        @if(is_array($team) || is_object($team))
                                            <li data-team="{{ e($team['id'] ?? $team->id ?? '') }}">{{ e($team['nombre'] ?? $team->nombre ?? '') }}</li>
                                        @else
                                            <li data-team="{{ e($team) }}">{{ e($team) }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-yellow-50 rounded">No hay grupos generados todavía.</div>
                @endif
            </div>

            <div class="schedule-card mt-6">
                <h2 class="font-semibold mb-3 text-emerald-600">Generar horario desde grupos</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Fecha inicio</label>
                        <input id="fecha_inicio_sched" type="date"
                            value="{{ old('fecha_inicio', optional($torneos->firstWhere('id', $selected))->fecha_inicio ?? '') }}"
                            class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Días entre jornadas</label>
                        <input id="dias_entre_sched" type="number" min="1" value="7" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Programar en</label>
                        <select id="tipo_horario_sched" class="form-select">
                            <option value="todo">Todos los días</option>
                            <option value="entre_semana">Entre semana (Lun-Vie)</option>
                            <option value="fines_semana">Fines de semana (Sáb-Dom)</option>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <!-- Eliminado el botón de generar horario -->
                        <button id="saveCalendarNowBtn" type="button" class="btn-save w-full mt-2" style="margin-top:8px">Guardar calendario</button>
                    </div>
                </div>
                <div id="scheduleResult" class="mt-4"></div>
            </div>
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
        // Índice de nombres por ID para mostrar Equipo vs Equipo aunque vengan IDs
        const nameById = new Map();
        function rebuildNameIndex(){
            nameById.clear();
            if (Array.isArray(lastGroups)) {
                lastGroups.forEach(g => g.forEach(t => {
                    const id = (t && typeof t === 'object') ? t.id : t;
                    const nombre = (t && typeof t === 'object') ? (t.nombre ?? t.name) : null;
                    if (id != null && nombre) nameById.set(Number(id), String(nombre));
                }));
            }
        }
        function getTeamName(val){
            // val puede ser objeto, número o string
            if (val && typeof val === 'object') {
                const id = val.id ?? null;
                const nom = val.nombre ?? val.name ?? null;
                if (nom) return String(nom);
                if (id != null && nameById.has(Number(id))) return nameById.get(Number(id));
                return String(id ?? '');
            }
            const num = Number(val);
            if (!Number.isNaN(num) && nameById.has(num)) return nameById.get(num);
            return String(val ?? '');
        }

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
                div.querySelectorAll('li').forEach(li => {
                    items.push({ id: li.getAttribute('data-team') || null, nombre: li.textContent.trim() });
                });
                groups.push(items);
            });
            if (groups.length) { lastGroups = groups; rebuildNameIndex(); }
        }

        // cuando el formulario devuelve json.groups (ahora objetos {id,nombre}), renderizar preview acorde
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
                   // Asegurar que todos los items son {id,nombre}
                   json.groups = json.groups.map(g => g.map(t => (t && typeof t==='object') ? { id:Number(t.id), nombre:String(t.nombre||'') } : { id:Number(t), nombre:String(t) }));
                    lastGroups = json.groups;
                    rebuildNameIndex();
                    // render preview (cada item puede ser {id,nombre})
                    let html = '<div class="bg-white rounded shadow p-4 space-y-3">';
                    json.groups.forEach((g, i) => {
                        html += `<div data-group-index="${i}"><strong>Grupo ${i+1}</strong><ul class="list-disc pl-5 mt-1">` +
                                g.map(t => {
                                    const id = (t && (t.id !== undefined)) ? t.id : t;
                                    const name = (t && (t.nombre !== undefined)) ? t.nombre : t;
                                    return `<li data-team="${escapeHtml(String(id))}">${escapeHtml(String(name))}</li>`;
                                }).join('') +
                                `</ul></div>`;
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

        // generar horario: cuando se envía al servidor, enviar solo arrays de ids en payload.groups
        if (scheduleBtn) {
            scheduleBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                resultado.innerHTML = '<div class="p-3 bg-gray-50 rounded">Generando horario…</div>';
                if (!lastGroups || !lastGroups.length) {
                    const serverHas = serverGroupsEl && serverGroupsEl.querySelectorAll('[data-group-index]').length > 0;
                    if (!serverHas) {
                        resultado.innerHTML = '<div class="p-3 bg-yellow-50 text-yellow-700 rounded">No hay grupos generados. Genera los grupos antes.</div>';
                        return;
                    }
                }
                const rawDate = document.getElementById('fecha_inicio_sched').value;
                const fecha_inicio = normalizeDate(rawDate);

                // transformar lastGroups (array de objetos) en arrays de ids
                const groupsIds = (Array.isArray(lastGroups) ? lastGroups : []).map(g => {
                    return g.map(t => (t && t.id) ? Number(t.id) : (Number(t) || null));
                });

                const payload = {
                    torneo_id: document.getElementById('torneo_id').value,
                    fecha_inicio,
                    dias_entre: document.getElementById('dias_entre_sched').value || 7,
                    tipo_horario: document.getElementById('tipo_horario_sched').value || 'todo',
                    groups: groupsIds
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
        }

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

        // ---------- FUNCIONES AÑADIDAS: renderScheduleResult y tryAutoPreview ----------
        // renderScheduleResult: muestra lista simple de partidos y agrupa por fecha
        function renderScheduleResult(matches) {
            window.__lastGeneratedMatches = Array.isArray(matches) ? matches : [];
            toggleSaveButton(window.__lastGeneratedMatches.length > 0);
            const container = document.getElementById('scheduleResult');
            if (!container) return;
            if (!window.__lastGeneratedMatches.length) {
                container.innerHTML = '<div class="p-3 bg-yellow-50 text-yellow-900 rounded">No hay partidos generados.</div>';
                return;
            }
            const byDate = {};
            window.__lastGeneratedMatches.forEach(m => {
                const key = m.fecha_iso || (m.fecha && m.fecha_iso === undefined ? m.fecha : (m.date || 'sin fecha'));
                if (!byDate[key]) byDate[key] = [];
                byDate[key].push(m);
            });
            let html = '<div class="space-y-4">';
            Object.keys(byDate).sort().forEach(d => {
                const ms = byDate[d];
                html += `<div class="text-sm text-indigo-100"><strong>${escapeHtml(d)}</strong> — ${ms.length} partido(s)</div>`;
                html += '<ul class="pl-5 text-sm">';
                ms.forEach(it => {
                    const localName = getTeamName(it.local);
                    const visitanteName = getTeamName(it.visitante);
                    const grp = (it.group_index !== undefined) ? ` <span class="text-xs text-gray-300">(grupo ${Number(it.group_index)+1})</span>` : '';
                    html += `<li>${escapeHtml(localName)} vs ${escapeHtml(visitanteName)}${grp}</li>`;
                });
                html += '</ul>';
            });
            html += '</div>';
            container.innerHTML = html;
        }

        // tryAutoPreview: si hay grupos en lastGroups genera una preview local y la muestra
        function tryAutoPreview() {
            try {
                if (!Array.isArray(lastGroups) || lastGroups.length === 0) return;
                const rawDate = document.getElementById('fecha_inicio_sched').value || null;
                const fecha_inicio = normalizeDate(rawDate) || new Date().toISOString().slice(0,10);
                const dias = document.getElementById('dias_entre_sched') ? document.getElementById('dias_entre_sched').value || 7 : 7;
                const tipo = document.getElementById('tipo_horario_sched') ? document.getElementById('tipo_horario_sched').value || 'todo' : 'todo';

                // transformar lastGroups ({id,nombre}) en arrays de ids para el generador cliente
                const groupsIds = lastGroups.map(g => g.map(t => (t && t.id) ? t.id : t));

                const previewMatches = generateSchedulePreview(groupsIds, fecha_inicio, dias, tipo);
                renderScheduleResult(previewMatches);
            } catch (e) {
                console.error('tryAutoPreview error', e);
            }
        }
        // ------------------------------------------------------------------------------

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

        // Nuevo handler: botón "Guardar calendario" visible en la vista.
        document.getElementById('saveCalendarNowBtn').addEventListener('click', async (e) => {
            e.preventDefault();
            // si ya existen matches generados en memoria, los usamos
            let matches = window.__lastGeneratedMatches || [];
            if (!matches || matches.length === 0) {
                // intentar generar cliente-side usando grupos disponibles y la fecha seleccionada
                const fecha = document.getElementById('fecha_inicio_sched').value;
                if (!fecha) return alert('Selecciona una fecha de inicio o genera el horario antes.');
                const dias = document.getElementById('dias_entre_sched').value || 7;
                const tipo = document.getElementById('tipo_horario_sched').value || 'todo';

                const groupsSource = lastGroups && lastGroups.length ? lastGroups : (function(){
                    if (!serverGroupsEl) return [];
                    const g = [];
                    serverGroupsEl.querySelectorAll('[data-group-index]').forEach(div => {
                        const items = [];
                        div.querySelectorAll('li').forEach(li => items.push(li.getAttribute('data-team') || li.textContent.trim()));
                        g.push(items);
                    });
                    return g;
                })();

                if (!groupsSource || !groupsSource.length) return alert('No hay grupos disponibles para generar partidos.');
                matches = generateSchedulePreview(groupsSource, fecha, dias, tipo);
                if (!matches || !matches.length) return alert('No se pudieron generar partidos con los datos actuales.');
                // almacenar para posible uso posterior
                window.__lastGeneratedMatches = matches;
            }

            // mostrar resumen en modal (reutiliza modal de confirmación ya existente)
            const summaryEl = document.getElementById('confirmSummary');
            const torneoEl = document.getElementById('torneo_id');
            const torneoText = torneoEl ? torneoEl.options[torneoEl.selectedIndex]?.text || '' : '';
            summaryEl.innerHTML = `<div class="mb-2"><strong>Torneo:</strong> ${escapeHtml(torneoText)}</div>
                                   <div class="mb-2"><strong>Partidos a guardar:</strong> ${matches.length}</div>
                                   <div class="text-xs text-gray-600">Confirma que deseas persistir estos partidos en la base de datos.</div>`;
            showConfirmModal();
        });

        // mostrar preview automático si hay datos al cargar
        tryAutoPreview();
        updateViewMatchesVisibility();
    })();
    </script>
</x-app-layout>