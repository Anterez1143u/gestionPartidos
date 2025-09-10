<x-app-layout>
    <style>
        body {
            background: #1b2e47 !important;
        }
        .partidos-bg {
            background: #1b2e47;
            min-height: 100vh;
            padding-top: 40px;
        }
        .partidos-header {
            margin-bottom: 32px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }
        .partidos-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #00c896;
            margin-bottom: 0;
        }
        .partidos-header .btn {
            font-weight: 600;
            border-radius: 8px;
            margin-right: 8px;
            padding: 10px 22px;
            box-shadow: 0 2px 8px rgba(0,200,150,0.08);
            border: none;
            transition: background .2s;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
        }
        .partidos-header .btn:last-child { margin-right: 0; }
        .partidos-header .btn-primary {
            background: #00c896;
            color: #fff;
        }
        .partidos-header .btn-primary:hover {
            background: #0d6efd;
        }
        .partidos-header .btn-accent {
            background: #232946;
            color: #fff;
        }
        .partidos-header .btn-accent:hover {
            background: #00c896;
        }
        .partidos-header .btn-groups {
            background: #0d6efd;
            color: #fff;
        }
        .partidos-header .btn-groups:hover {
            background: #00c896;
        }
        .partidos-card {
            background: #232946;
            border-radius: 14px;
            box-shadow: 0 8px 32px rgba(13,110,253,0.10);
            padding: 32px;
            margin-bottom: 32px;
        }
        .form-label {
            font-weight: 700;
            font-size: 1.08rem;
            color: #fff;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }
        .form-control, .form-select, input[type="date"], input[type="number"] {
            background: #2a3550 !important;
            color: #fff !important;
            border-radius: 8px !important;
            border: 1.5px solid #00c896 !important;
            margin-bottom: 18px;
            font-size: 1.05rem;
            box-shadow: 0 2px 8px rgba(0,200,150,0.05);
        }
        .form-control:focus, .form-select:focus, input[type="date"]:focus, input[type="number"]:focus {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 2px #0d6efd33;
        }
        .btn-generate {
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
        .btn-generate:hover {
            background: #0d6efd;
        }
        .calendar-preview, .matches-list, .matches-summary {
            background: #232946;
            color: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(13,110,253,0.08);
            padding: 24px;
            margin-bottom: 24px;
        }
        .calendar-preview .bg-yellow-50,
        .calendar-preview .bg-green-50 {
            background: #00c89622 !important;
            color: #00c896 !important;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
        }
        .matches-list .border-b {
            border-bottom: 1px solid #1b2e47;
        }
        .matches-list .btn-save {
            background: #0d6efd;
            color: #fff;
            border-radius: 8px;
            padding: 6px 16px;
            font-size: 0.95rem;
            border: none;
            transition: background .2s;
        }
        .matches-list .btn-save:hover {
            background: #00c896;
        }
        .matches-list input[type="number"] {
            background: #2a3550 !important;
            color: #fff !important;
            border: 1.5px solid #00c896 !important;
        }
        .matches-list select {
            background: #2a3550 !important;
            color: #fff !important;
            border: 1.5px solid #00c896 !important;
        }
        .matches-summary canvas {
            background: #232946;
            border-radius: 8px;
        }
    </style>
    <div class="partidos-bg">
        <div class="max-w-6xl mx-auto px-3">
            <div class="partidos-header">
                <h1>Generación de Calendario de Partidos</h1>
                <div>
                    <a href="{{ route('torneos.index') }}" class="btn btn-accent">Torneos</a>
                    <a href="{{ route('grupos.generate') }}" class="btn btn-groups">Grupos</a>
                    <a href="{{ route('partidos.index') }}" class="btn btn-primary">Partidos</a>
                </div>
            </div>

            <div class="partidos-card">
                @php
                    $sessionGroups = session('generated_groups', null);
                    $sessionGroupsTorneo = session('generated_groups_torneo', null);
                @endphp

                <form id="generateCalendarForm" onsubmit="return false;">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">Torneo</label>
                            <select id="torneo_id" name="torneo_id" class="form-select" required>
                                <option value="">Selecciona un torneo</option>
                                @foreach(\App\Models\Torneo::all() as $t)
                                    <option value="{{ $t->id }}"
                                        {{ (int)old('torneo_id', $sessionGroupsTorneo ?? '') === $t->id ? 'selected' : '' }}>
                                        {{ $t->deporte }} @if($t->numero_participantes) ({{ $t->numero_participantes }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Fecha inicio</label>
                            <input id="fecha_inicio" name="fecha_inicio" type="date"
                                value="{{ old('fecha_inicio', optional(\App\Models\Torneo::find($sessionGroupsTorneo))->fecha_inicio ?? '') }}"
                                class="form-control" required>
                        </div>

                        <div>
                            <label class="form-label">Días entre jornadas</label>
                            <input id="dias_entre" name="dias_entre" type="number" min="1" value="7" class="form-control">
                        </div>

                        <div>
                            <label class="form-label">Programar en</label>
                            <select id="tipo_horario" name="tipo_horario" class="form-select">
                                <option value="todo">Todos los días</option>
                                <option value="entre_semana">Entre semana (Lun-Vie)</option>
                                <option value="fines_semana">Fines de semana (Sáb-Dom)</option>
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <label class="inline-flex items-center">
                                <input id="use_groups" type="checkbox" class="form-checkbox" {{ $sessionGroups ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-200">Usar grupos guardados en sesión</span>
                            </label>
                        </div>

                        <div class="flex items-end md:col-span-3">
                            <button id="generateBtn" class="btn-generate">
                                🗓 Generar calendario
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div id="calendarPreview" class="calendar-preview"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="matches-list">
                    <h3 class="font-semibold mb-2" style="color:#00c896;">Partidos generados (detalle)</h3>
                    <div id="matchesList" class="max-h-96 overflow-auto text-sm"></div>
                </div>

                <div class="matches-summary">
                    <h3 class="font-semibold mb-2" style="color:#00c896;">Gráfico resumen / Tipo de torneo</h3>
                    <canvas id="matchesChart" height="220"></canvas>
                    <div id="tournamentGraphic" class="mt-4 text-sm"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    (function(){
        const generateBtn = document.getElementById('generateBtn');
        const matchesList = document.getElementById('matchesList');
        const calendarPreview = document.getElementById('calendarPreview');
        const ctx = document.getElementById('matchesChart').getContext('2d');
        let chart = null;

        const sessionGroups = @json($sessionGroups);
        const sessionGroupsTorneo = @json($sessionGroupsTorneo);

        function renderSessionGroups(selectedTorneoId) {
            if (!sessionGroups || sessionGroupsTorneo == null) {
                calendarPreview.innerHTML = '<div class="p-4 bg-yellow-50 rounded">No hay grupos guardados en sesión.</div>';
                return;
            }
            if (parseInt(selectedTorneoId) !== parseInt(sessionGroupsTorneo)) {
                calendarPreview.innerHTML = '<div class="p-4 bg-yellow-50 rounded">Los grupos guardados pertenecen a otro torneo.</div>';
                return;
            }
            let html = '<div class="space-y-3">';
            sessionGroups.forEach((g, i) => {
                html += `<div class="p-2 border rounded"><strong>Grupo ${i+1}</strong><ul class="pl-5 list-disc mt-1">`;
                g.forEach(member => html += `<li class="text-sm">${member}</li>`);
                html += '</ul></div>';
            });
            html += '</div>';
            calendarPreview.innerHTML = html;
        }

        // crea HTML interactivo para cada partido: inputs de marcador + opción de decidir ganador (por puntos o manual)
        function buildMatchItem(match, tipoTorneo = 'liga') {
            const id = match.id ?? '';
            const fecha = match.fecha || match.fecha_iso || match.fecha_iso2 || '';
            const local = match.local || 'Local';
            const visitante = match.visitante || 'Visitante';
            const estado = match.estado || 'pendiente';
            const groupText = match.group_index !== undefined ? ` (grupo ${match.group_index + 1})` : '';
            return `
                <div class="p-3 border-b flex flex-col gap-2" data-match-id="${id}">
                    <div class="flex justify-between items-center">
                        <div><strong>${fecha}</strong> — <span class="font-medium">${escapeHtml(local)}</span> <span class="text-xs text-gray-500">vs</span> <span class="font-medium">${escapeHtml(visitante)}</span> ${groupText}</div>
                        <div class="text-xs text-gray-400">${escapeHtml(estado)}</div>
                    </div>

                    <div class="flex gap-2 items-center">
                        <label class="text-xs">Marcador:</label>
                        <input type="number" min="0" class="px-2 py-1 border rounded w-16 local-score" value="${match.local_score ?? ''}" placeholder="0">
                        <span class="text-sm">-</span>
                        <input type="number" min="0" class="px-2 py-1 border rounded w-16 visitante-score" value="${match.visitante_score ?? ''}" placeholder="0">

                        <select class="ml-4 text-xs decide-mode border rounded decide-mode">
                            <option value="puntos">Decidir por puntos</option>
                            <option value="manual">Decidir manualmente</option>
                        </select>

                        <select class="ml-2 text-xs border rounded manual-winner hidden manual-winner">
                            <option value="">Seleccionar ganador</option>
                            <option value="local">Local (${escapeHtml(local)})</option>
                            <option value="visitante">Visitante (${escapeHtml(visitante)})</option>
                        </select>

                        <button class="ml-auto px-3 py-1 bg-indigo-600 text-white rounded text-xs btn-save">Guardar resultado</button>
                    </div>

                    <div class="flex gap-2 text-xs text-gray-600">
                        <div class="info-points">Puntos: —</div>
                        <div class="info-winner">Ganador: —</div>
                    </div>
                </div>
            `;
        }

        function escapeHtml(s){ return String(s||'').replace(/[&<>"]/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' })[c]); }

        // renderiza la lista completa con controles
        function renderMatchesInteractive(matches, tipoTorneo) {
            if (!matches || !matches.length) {
                matchesList.innerHTML = '<div class="p-3 text-sm text-gray-600">No se encontraron partidos.</div>';
                return;
            }
            let html = '';
            matches.forEach(m => html += buildMatchItem(m, tipoTorneo));
            matchesList.innerHTML = html;
            // inicializar info por cada match
            document.querySelectorAll('[data-match-id]').forEach(el => {
                updateMatchInfo(el, tipoTorneo);
            });
        }

        function updateMatchInfo(el, tipoTorneo) {
            const localScoreEl = el.querySelector('.local-score');
            const visitScoreEl = el.querySelector('.visitante-score');
            const infoPoints = el.querySelector('.info-points');
            const infoWinner = el.querySelector('.info-winner');
            const decideMode = el.querySelector('.decide-mode');
            const manualWinner = el.querySelector('.manual-winner');

            const local = el.querySelector('strong + span')?.textContent ?? '';
            function calc() {
                const a = parseInt(localScoreEl.value) || 0;
                const b = parseInt(visitScoreEl.value) || 0;
                // puntos simples: victoria 3, empate 1, derrota 0 (liga)
                let ptsLocal = 0, ptsVisit = 0;
                if (a > b) { ptsLocal = 3; ptsVisit = 0; }
                else if (a < b) { ptsLocal = 0; ptsVisit = 3; }
                else { ptsLocal = 1; ptsVisit = 1; }
                infoPoints.textContent = `Puntos: local ${ptsLocal} — visitante ${ptsVisit}`;
                let ganador = '—';
                if (decideMode.value === 'puntos') {
                    if (a > b) ganador = 'Local';
                    else if (a < b) ganador = 'Visitante';
                    else ganador = 'Empate';
                } else {
                    const sel = manualWinner.value;
                    if (sel === 'local') ganador = 'Local';
                    else if (sel === 'visitante') ganador = 'Visitante';
                    else ganador = '—';
                }
                infoWinner.textContent = `Ganador: ${ganador}`;
            }
            // listeners
            localScoreEl.addEventListener('input', calc);
            visitScoreEl.addEventListener('input', calc);
            decideMode.addEventListener('change', () => {
                manualWinner.classList.toggle('hidden', decideMode.value !== 'manual');
                calc();
            });
            manualWinner.addEventListener('change', calc);

            calc();
        }

        // guardar resultado (envía al servidor)
        async function saveResultForMatch(el) {
            const matchId = el.getAttribute('data-match-id');
            if (!matchId) return;
            const localScore = parseInt(el.querySelector('.local-score').value) || 0;
            const visitanteScore = parseInt(el.querySelector('.visitante-score').value) || 0;
            const decideMode = el.querySelector('.decide-mode').value;
            let winner = null;
            if (decideMode === 'manual') {
                const sel = el.querySelector('.manual-winner').value;
                if (sel === 'local') winner = 'local';
                else if (sel === 'visitante') winner = 'visitante';
            } else {
                if (localScore > visitanteScore) winner = 'local';
                else if (localScore < visitanteScore) winner = 'visitante';
                else winner = null; // empate
            }

            const payload = {
                local_score: localScore,
                visitante_score: visitanteScore,
                winner_mode: decideMode,
                winner: winner, // 'local'|'visitante'|null
            };

            const url = `/admin/partidos/${matchId}/set-result`; // Añadir ruta/controller para manejar this POST JSON
            try {
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
                let json = null;
                try { json = JSON.parse(text); } catch(e){ /* html/text error */ }

                if (!res.ok) {
                    const message = json?.error ?? text ?? `Error ${res.status}`;
                    el.querySelector('.info-winner').textContent = `Error: ${message}`;
                    return;
                }

                // actualizar UI con confirmación
                el.querySelector('.info-winner').textContent = `Guardado ✓`;
                // si el servidor devolvió partido actualizado, usarlo para actualizar info
                if (json && json.match) {
                    el.querySelector('.info-points').textContent = `Puntos actualizados`;
                    // actualizar estado visual si hay estado
                    if (json.match.estado) {
                        el.querySelector('.text-xs.text-gray-400').textContent = json.match.estado;
                    }
                }
            } catch (err) {
                el.querySelector('.info-winner').textContent = `Error: ${err.message}`;
            }
        }

        // delegación para botones Guardar
        matchesList.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-save');
            if (!btn) return;
            const el = btn.closest('[data-match-id]');
            if (!el) return;
            saveResultForMatch(el);
        });

        // función para dibujar gráfico simple
        function drawChart(counts) {
            const labels = Object.keys(counts || {});
            const data = Object.values(counts || {});
            if (chart) chart.destroy();
            chart = new Chart(ctx, {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Partidos por fecha', data, backgroundColor: 'rgba(16,185,129,0.7)' }] },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });
        }

        // acción principal: genera calendario y renderiza interactivo
        generateBtn.addEventListener('click', async () => {
            const torneo_id = document.getElementById('torneo_id').value;
            const fecha_inicio = document.getElementById('fecha_inicio').value;
            const dias_entre = document.getElementById('dias_entre').value || 7;
            const tipo_horario = document.getElementById('tipo_horario').value;
            const use_groups = document.getElementById('use_groups').checked;

            if (!torneo_id || !fecha_inicio) {
                alert('Selecciona torneo y fecha inicio.');
                return;
            }

            const payload = { torneo_id, fecha_inicio, dias_entre, tipo_horario };
            if (use_groups && sessionGroups && sessionGroupsTorneo == torneo_id) payload.groups = sessionGroups;

            matchesList.innerHTML = 'Generando…';
            try {
                const res = await fetch("{{ route('partidos.generateCalendar') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                if (!res.ok) {
                    const text = await res.text();
                    matchesList.innerHTML = `<div class="text-red-600 p-3">Error servidor: <pre style="white-space:pre-wrap">${escapeHtml(text)}</pre></div>`;
                    return;
                }
                const json = await res.json();

                // render matches interactivos
                renderMatchesInteractive(json.matches || [], json.type || 'liga');

                // dibujar chart y visual
                drawChart(json.counts || {});
                const tg = document.getElementById('tournamentGraphic');
                tg.innerHTML = json.type === 'eliminatoria'
                    ? '<div>Tipo: Eliminatoria — se mostrará cuadro simplificado</div>'
                    : '<div>Tipo: Liga/Grupos — calendario por jornadas</div>';

                calendarPreview.innerHTML = `<div class="p-3 bg-green-50 rounded text-sm">Generados ${json.matches.length} partidos — tipo: ${json.type}</div>`;
            } catch (err) {
                matchesList.innerHTML = `<div class="text-red-600 p-3">Error: ${err.message}</div>`;
            }
        });

    })();
    </script>
</x-app-layout>