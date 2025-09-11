<x-app-layout>
    <style>
        /* estilos simplificados / reutilizables */
        body { background: #0f2436 !important; color: #fff; }
        .container { max-width: 1100px; margin: 32px auto; padding: 0 16px; }
        .card { background: #162938; border-radius: 12px; padding: 18px; margin-bottom: 18px; box-shadow: 0 6px 24px rgba(0,0,0,0.35); }
        h1 { color:#19d6a6; font-size:1.8rem; margin:0 0 10px 0; }
        .row { display:flex; gap:16px; flex-wrap:wrap; }
        .col { flex:1; min-width:220px; }
        .btn { background:#19d6a6; color:#021521; padding:10px 14px; border-radius:8px; font-weight:700; border:none; cursor:pointer; }
        .btn.secondary { background:#234; color:#fff; }
        .match { border-bottom:1px solid #0b2430; padding:12px 0; display:flex; gap:12px; align-items:center; }
        .match .info { flex:1; }
        .small { font-size:0.86rem; color:#9fb6c3; }
        input[type="number"], select { background:#0e2632; border:1px solid #1f8e6e; color:#fff; padding:6px 8px; border-radius:6px; }
        table { width:100%; border-collapse:collapse; margin-top:8px; }
        th, td { padding:8px 6px; text-align:left; border-bottom:1px solid #0b2430; font-size:0.95rem; }
        .badge { background:#0b5560; color:#bff3e6; padding:6px 8px; border-radius:8px; font-weight:700; }
        .center { text-align:center; }
        .muted { color:#7eaab2; font-size:0.9rem; }
        .hidden { display:none; }
    </style>

    <div class="container">
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                <div>
                    <h1>Partidos — Administrar resultados</h1>
                    <div class="small">Ver y editar resultados; ver clasificación (liga) o avance (eliminatoria).</div>
                </div>
                <div style="display:flex; gap:8px;">
                    <a class="btn secondary" href="{{ route('grupos.generate') }}">Generar desde grupos</a>
                    <a class="btn" href="{{ route('torneos.index') }}">Torneos</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="row" style="align-items:center;">
                <div class="col">
                    <label class="small">Torneo</label>
                    <select id="filterTorneo" class="form-select">
                        <option value="">— Selecciona —</option>
                        @foreach(\App\Models\Torneo::all() as $t)
                            <option value="{{ $t->id }}">{{ $t->deporte }} @if($t->numero_participantes) ({{ $t->numero_participantes }}) @endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label class="small">Mostrar</label>
                    <select id="filterType" class="form-select">
                        <option value="all">Todos los partidos</option>
                        <option value="pendientes">Pendientes</option>
                        <option value="finalizados">Finalizados</option>
                    </select>
                </div>
                <div style="display:flex; gap:8px; align-items:end;">
                    <button id="loadBtn" class="btn">Cargar partidos</button>
                </div>
            </div>
        </div>

        <div id="matchesContainer" class="card">
            <div id="matchesHeader" style="display:flex; justify-content:space-between; align-items:center;">
                <div><strong>Partidos</strong> <span class="muted" id="matchesCount">(0)</span></div>
                <div class="small muted">Introduce marcadores y pulsa Guardar</div>
            </div>

            <div id="matchesByDate" style="margin-top:12px;"></div>
        </div>

        <div id="rightPanel" style="display:flex; gap:16px; flex-wrap:wrap;">
            <div id="standingsCard" class="card" style="flex:1; min-width:360px;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div><strong>Tabla de clasificación</strong><div class="small muted">(se calcula localmente a partir de los partidos cargados)</div></div>
                    <div id="standingsMeta" class="small muted"></div>
                </div>
                <div id="standingsBody" style="margin-top:10px;">
                    <div class="muted">Carga partidos para generar la tabla.</div>
                </div>
            </div>

            <div id="bracketCard" class="card" style="flex:1; min-width:360px;">
                <div><strong>Avance (Eliminatoria)</strong><div class="small muted">Se mostrará cuando el torneo sea eliminatorio</div></div>
                <div id="bracketBody" style="margin-top:10px;">
                    <div class="muted">Sin datos.</div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function(){
        // token robusto: meta, input oculto o window.Laravel
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('input[name=_token]')?.value
            || (window.Laravel && window.Laravel.csrfToken)
            || '';
        const loadBtn = document.getElementById('loadBtn');
        const filterTorneo = document.getElementById('filterTorneo');
        const filterType = document.getElementById('filterType');
        const matchesByDate = document.getElementById('matchesByDate');
        const matchesCount = document.getElementById('matchesCount');
        const standingsBody = document.getElementById('standingsBody');
        const standingsMeta = document.getElementById('standingsMeta');
        const bracketBody = document.getElementById('bracketBody');

        let currentMatches = []; // array de partidos cargados
        let currentType = 'liga'; // 'liga' o 'eliminatoria' (lo devuelve el servidor)

        // helper
        function escapeHtml(s){ return String(s||'').replace(/[&<>"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
        function groupByDate(matches){
            const by = {};
            matches.forEach(m=>{
                const d = (m.fecha || m.fecha_iso || m.date || '').slice(0,10) || 'Sin fecha';
                if(!by[d]) by[d]=[];
                by[d].push(m);
            });
            return by;
        }
        function formatDateBadge(dateStr){
            if(!dateStr || dateStr==='Sin fecha') return 'Sin fecha';
            const parts = dateStr.split('-');
            if(parts.length===3){ return `${parts[2]}/${parts[1]}/${parts[0]}`; }
            try { const d = new Date(dateStr); return d.toLocaleDateString('es-ES'); } catch { return dateStr; }
        }

        // render partidos agrupados por fecha
        function renderMatches(matches){
            currentMatches = matches || [];
            matchesCount.textContent = `(${currentMatches.length})`;
            if(!currentMatches.length){
                matchesByDate.innerHTML = '<div class="muted">No hay partidos.</div>';
                standingsBody.innerHTML = '<div class="muted">Carga partidos para generar la tabla.</div>';
                bracketBody.innerHTML = '<div class="muted">Sin datos.</div>';
                return;
            }

            const byDate = groupByDate(currentMatches);
            let html = '';
            Object.keys(byDate).sort().forEach(date=>{
                html += `<div style="margin-bottom:12px;"><div style="margin-bottom:8px;"><span class="badge">${escapeHtml(formatDateBadge(date))}</span></div>`;
                byDate[date].forEach(m=>{
                    const localName = (m.local && (m.local.nombre||m.local.name)) || (m.local_nombre || 'Local');
                    const visitName = (m.visitante && (m.visitante.nombre||m.visitante.name)) || (m.visitante_nombre || 'Visitante');
                    const localScore = (m.local_score !== undefined && m.local_score !== null) ? m.local_score : '';
                    const visitScore = (m.visitante_score !== undefined && m.visitante_score !== null) ? m.visitante_score : '';
                    const estado = m.estado || '';
                    const hora = m.hora ? ` — ${escapeHtml(String(m.hora).slice(0,5))}` : '';
                    const cancha = m.cancha ? ` · Cancha: ${escapeHtml(m.cancha)}` : '';
                    html += `<div class="match" data-match-id="${m.id}">
                        <div class="info">
                            <div><strong>${escapeHtml(localName)}</strong> <span class="small">vs</span> <strong>${escapeHtml(visitName)}</strong> ${m.group_index !== undefined && m.group_index !== null ? `<span class="small"> (grupo ${m.group_index+1})</span>` : ''}</div>
                            <div class="small muted">${escapeHtml(estado)}${hora}${cancha}</div>
                        </div>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <input type="number" min="0" class="local-score" style="width:72px" value="${escapeHtml(localScore)}" placeholder="0">
                            <span class="muted">-</span>
                            <input type="number" min="0" class="visit-score" style="width:72px" value="${escapeHtml(visitScore)}" placeholder="0">
                            <select class="decide-mode" style="margin-left:8px;">
                                <option value="puntos">Por marcador</option>
                                <option value="manual">Manual</option>
                            </select>
                            <select class="manual-winner hidden" style="margin-left:6px;">
                                <option value="">Seleccionar ganador</option>
                                <option value="local">Local</option>
                                <option value="visitante">Visitante</option>
                            </select>
                            <button class="btn save-match" style="margin-left:8px;">Guardar</button>
                        </div>
                    </div>`;
                });
                html += `</div>`;
            });
            matchesByDate.innerHTML = html;

            // attach listeners (delegación simple)
            document.querySelectorAll('.save-match').forEach(btn=>{
                btn.onclick = async (ev)=>{
                    const wrapper = ev.target.closest('[data-match-id]');
                    await saveMatchResult(wrapper);
                };
            });

            document.querySelectorAll('.decide-mode').forEach(sel=>{
                sel.onchange = (e)=>{
                    const w = e.target.closest('[data-match-id]').querySelector('.manual-winner');
                    if(e.target.value === 'manual') w.classList.remove('hidden'); else w.classList.add('hidden');
                };
            });

            // actualizar tabla/avances
            computeAndRenderStandings();
            renderBracket();
        }

        // calcular tabla de clasificación desde currentMatches (para tipo liga)
        function computeAndRenderStandings(){
            if(currentType !== 'liga'){
                standingsBody.innerHTML = '<div class="muted">Tabla no disponible — torneo eliminatorio.</div>';
                standingsMeta.textContent = '';
                return;
            }

            const table = {}; // teamId/name -> stats
            currentMatches.forEach(m=>{
                // sólo considerar partidos con marcador o finalizados
                const local = (m.local && (m.local.nombre||m.local.name)) || (m.local_nombre || 'Local');
                const visit = (m.visitante && (m.visitante.nombre||m.visitante.name)) || (m.visitante_nombre || 'Visitante');
                const lid = m.local && (m.local.id || m.local_id) || `L:${local}`;
                const vid = m.visitante && (m.visitante.id || m.visitante_id) || `V:${visit}`;
                const a = Number(m.local_score);
                const b = Number(m.visitante_score);
                const hasScore = !isNaN(a) && !isNaN(b);

                if(!table[lid]) table[lid] = { name: local, played:0, won:0, draw:0, lost:0, gf:0, ga:0, points:0 };
                if(!table[vid]) table[vid] = { name: visit, played:0, won:0, draw:0, lost:0, gf:0, ga:0, points:0 };

                if(hasScore){
                    table[lid].played++; table[vid].played++;
                    table[lid].gf += a; table[lid].ga += b;
                    table[vid].gf += b; table[vid].ga += a;

                    if(a > b){ table[lid].won++; table[lid].points += 3; table[vid].lost++; }
                    else if(a < b){ table[vid].won++; table[vid].points +=3; table[lid].lost++; }
                    else { table[lid].draw++; table[vid].draw++; table[lid].points++; table[vid].points++; }
                }
            });

            // convertir y ordenar
            const rows = Object.keys(table).map(k => ({ id:k, ...table[k], gd: table[k].gf - table[k].ga }));
            rows.sort((a,b) => b.points - a.points || b.gd - a.gd || b.gf - a.gf || a.name.localeCompare(b.name));

            // render tabla
            let html = `<table><thead><tr><th>#</th><th>Equipo</th><th>PJ</th><th>G</th><th>E</th><th>P</th><th>GF</th><th>GA</th><th>DG</th><th>Pts</th></tr></thead><tbody>`;
            rows.forEach((r,i)=>{
                html += `<tr><td class="center">${i+1}</td><td>${escapeHtml(r.name)}</td><td class="center">${r.played}</td><td class="center">${r.won}</td><td class="center">${r.draw}</td><td class="center">${r.lost}</td><td class="center">${r.gf}</td><td class="center">${r.ga}</td><td class="center">${r.gd}</td><td class="center">${r.points}</td></tr>`;
            });
            html += `</tbody></table>`;
            standingsBody.innerHTML = html;
            standingsMeta.textContent = `Equipos: ${rows.length}`;
        }

        // render simple bracket (si matches incluyen property round o server retorna rounds)
        function renderBracket(){
            if(currentType !== 'eliminatoria'){
                bracketBody.innerHTML = '<div class="muted">No es eliminatoria.</div>';
                return;
            }
            // intentar agrupar por ronda si existe campo round en matches
            const rounds = {};
            currentMatches.forEach(m=>{
                const r = m.round || m.ronda || '1';
                if(!rounds[r]) rounds[r]=[];
                rounds[r].push(m);
            });
            const keys = Object.keys(rounds).sort((a,b)=>Number(a)-Number(b));
            if(!keys.length){ bracketBody.innerHTML = '<div class="muted">No hay información de rondas.</div>'; return; }

            let html = '';
            keys.forEach(k=>{
                html += `<div style="margin-bottom:10px;"><strong>Ronda ${escapeHtml(String(k))}</strong><div class="small muted">Partidos: ${rounds[k].length}</div>`;
                rounds[k].forEach(m=>{
                    const localName = (m.local && (m.local.nombre||m.local.name)) || (m.local_nombre || 'Local');
                    const visitName = (m.visitante && (m.visitante.nombre||m.visitante.name)) || (m.visitante_nombre || 'Visitante');
                    const score = (m.local_score !== undefined && m.visitante_score !== undefined) ? ` — ${m.local_score} : ${m.visitante_score}` : '';
                    html += `<div class="small" style="margin-top:6px;">${escapeHtml(localName)} vs ${escapeHtml(visitName)} ${escapeHtml(score)}</div>`;
                });
                html += `</div>`;
            });
            bracketBody.innerHTML = html;
        }

        // POST para guardar resultado de un match
        async function saveMatchResult(wrapper){
            const matchId = wrapper.getAttribute('data-match-id');
            const localScore = wrapper.querySelector('.local-score').value;
            const visitScore = wrapper.querySelector('.visit-score').value;
            const decideMode = wrapper.querySelector('.decide-mode').value;
            const manualWinnerEl = wrapper.querySelector('.manual-winner');
            let winner = null;
            if(decideMode === 'manual' && manualWinnerEl){
                const sel = manualWinnerEl.value;
                if(sel === 'local') winner = 'local';
                else if(sel === 'visitante') winner = 'visitante';
            } else {
                const a = Number(localScore);
                const b = Number(visitScore);
                if(!isNaN(a) && !isNaN(b)){
                    if(a > b) winner = 'local';
                    else if(a < b) winner = 'visitante';
                    else winner = null;
                }
            }

            const payload = { local_score: localScore !== '' ? Number(localScore) : null, visitante_score: visitScore !== '' ? Number(visitScore) : null, winner_mode: decideMode, winner };
            const url = `/admin/partidos/${matchId}/set-result`;

            // feedback mínimo
            const saveBtn = wrapper.querySelector('.save-match');
            const prevText = saveBtn.textContent;
            saveBtn.textContent = 'Guardando...';
            saveBtn.disabled = true;

            try{
                const res = await fetch(url, {
                    method:'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type':'application/json',
                        'Accept':'application/json',
                        'X-Requested-With':'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });
                const json = await res.json().catch(()=>null);
                if(!res.ok){
                    alert('Error al guardar: ' + (json?.error || res.status));
                    saveBtn.textContent = prevText;
                    saveBtn.disabled = false;
                    return;
                }
                // actualizar UI local con lo que devuelve el servidor (si devuelve match actualizado)
                if(json && json.match){
                    // actualizar currentMatches entry
                    const idx = currentMatches.findIndex(x => String(x.id) === String(json.match.id));
                    if(idx !== -1) currentMatches[idx] = json.match;
                } else {
                    // fallback: actualizar los inputs mostrados
                    const inputs = wrapper.querySelectorAll('input[type="number"]');
                    // no hacemos nada más
                }
                saveBtn.textContent = 'Guardado ✓';
                setTimeout(()=>{ saveBtn.textContent = prevText; saveBtn.disabled = false; }, 1200);
                // recalcular tabla y bracket
                computeAndRenderStandings();
                renderBracket();
            }catch(err){
                alert('Error red: ' + err.message);
                saveBtn.textContent = prevText;
                saveBtn.disabled = false;
            }
        }

        // request al servidor para obtener partidos del torneo seleccionado (robustecida)
        async function loadMatches(){
            const torneo = filterTorneo.value;
            if(!torneo){ alert('Selecciona un torneo.'); return; }
            const typeFilter = filterType.value;
            matchesByDate.innerHTML = '<div class="muted">Cargando…</div>';
            const prevText = loadBtn.textContent; loadBtn.textContent = 'Cargando…'; loadBtn.disabled = true;
            try{
                const endpoint = "{{ route('partidos.index.json') }}";
                const url = endpoint + '?torneo=' + encodeURIComponent(torneo);
                const res = await fetch(url, { headers:{ Accept:'application/json', 'X-Requested-With':'XMLHttpRequest' } });
                const contentType = res.headers.get('content-type') || '';
                if(!contentType.includes('application/json')){
                    const txt = await res.text();
                    matchesByDate.innerHTML = `<div class=\"muted\">Respuesta no JSON (status ${res.status}) desde ${escapeHtml(url)}<pre style=\"white-space:pre-wrap;max-height:240px;overflow:auto;margin-top:8px;\">${escapeHtml(txt)}</pre></div>`;
                    return;
                }
                if(!res.ok){
                    const txt = await res.text();
                    matchesByDate.innerHTML = `<div class=\"muted\">Error servidor (${res.status}): ${escapeHtml(txt)}</div>`;
                    return;
                }
                const json = await res.json();
                currentType = json.type || 'liga';
                let matches = Array.isArray(json.matches) ? json.matches : [];
                if(typeFilter === 'pendientes') matches = matches.filter(m => !m.estado || m.estado === 'pendiente' || m.estado === 'pending');
                else if(typeFilter === 'finalizados') matches = matches.filter(m => m.estado === 'finalizado' || m.estado === 'done' || (m.local_score !== undefined && m.visitante_score !== undefined));
                renderMatches(matches);
            }catch(err){
                matchesByDate.innerHTML = `<div class=\"muted\">Error de red: ${escapeHtml(err.message)}</div>`;
            } finally {
                loadBtn.textContent = prevText; loadBtn.disabled = false;
            }
        }

        loadBtn.addEventListener('click', loadMatches);
        [filterTorneo, filterType].forEach(el => el && el.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); loadMatches(); }}));
        if(filterTorneo.options.length===2){ filterTorneo.selectedIndex=1; loadMatches(); }
    })();
    </script>
</x-app-layout>