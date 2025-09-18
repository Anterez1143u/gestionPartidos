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
                        @php $validos = ['futboll','voley','baloncesto']; @endphp
                        @foreach(\App\Models\Torneo::all() as $t)
                            @php $dep = strtolower(trim($t->deporte)); @endphp
                            @continue(!in_array($dep, $validos, true))
                            <option value="{{ $t->id }}" data-deporte="{{ $dep }}">
                                {{ $dep }} @if($t->numero_participantes) ({{ $t->numero_participantes }}) @endif
                            </option>
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
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div><strong>Avance (Eliminatoria)</strong><div class="small muted">Se mostrará cuando el torneo sea eliminatorio</div></div>
                    <button id="btnNextRound" class="btn hidden">Generar siguiente ronda</button>
                </div>
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
        let currentSport = 'generico'; // 'futbol' | 'baloncesto' | 'voley' | ...
        let ignoredCount = 0; // <- duplicados ignorados

        // helper
        function escapeHtml(s){ return String(s||'').replace(/[&<>"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
        // Añadir: deduplicar por id o por combinación fallback
        function dedupeMatches(list){
            const seen = new Set();
            const out = [];
            let dups = 0;
            for(const m of (list||[])){
                const key = (m.id != null) ? `id:${m.id}` :
                    `k:${m.local_id||m.local?.id||m.local_nombre}-${m.visitante_id||m.visitante?.id||m.visitante_nombre}-${(m.fecha||'').slice(0,10)}-${m.hora||''}`;
                if(seen.has(key)) { dups++; continue; }
                seen.add(key);
                out.push(m);
            }
            ignoredCount = dups; // actualizar contador global
            return out;
        }
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
            // Configuración por deporte (un solo origen de verdad)
            const meta = (window.sportMeta ? window.sportMeta(currentSport) : { placeholder:'Marcador', min:0, max:999, step:1 });
            const isVoley = /voley/.test(currentSport);

            let html = '';
            Object.keys(byDate).sort().forEach(date=>{
                html += `<div style="margin-bottom:12px;"><div style="margin-bottom:8px;"><span class="badge">${escapeHtml(formatDateBadge(date))}</span> <span class="small muted">· ${escapeHtml(currentSport)}</span></div>`;
                byDate[date].forEach(m=>{
                    const localId = (m.local && (m.local.id ?? m.local_id)) ?? m.local_id ?? m.equipo1_id ?? null;
                    const visitId = (m.visitante && (m.visitante.id ?? m.visitante_id)) ?? m.visitante_id ?? m.equipo2_id ?? null;
                    const localNameRaw = (m.local && (m.local.nombre||m.local.name)) || (m.local_nombre || null);
                    const visitNameRaw = (m.visitante && (m.visitante.nombre||m.visitante.name)) || (m.visitante_nombre || null);
                    const hasBothTeams = !!(localId && visitId);
                    const localName = localNameRaw || '—';
                    const visitName = visitNameRaw || '—';
                    const localScore = (m.local_score !== undefined && m.local_score !== null) ? m.local_score : '';
                    const visitScore = (m.visitante_score !== undefined && m.visitante_score !== null) ? m.visitante_score : '';
                    const estado = m.estado || '';
                    const hora = m.hora ? ` — ${escapeHtml(String(m.hora).slice(0,5))}` : '';
                    const cancha = m.cancha ? ` · Cancha: ${escapeHtml(m.cancha)}` : '';
                    const disabledAttr = hasBothTeams ? '' : 'disabled';
                    const incompletoTag = hasBothTeams ? '' : ' <span class="small muted">· incompleto (asignar equipos)</span>';
                    html += `<div class="match" data-match-id="${m.id}">
                        <div class="info">
                            <div><strong>${escapeHtml(localName)}</strong> <span class="small">vs</span> <strong>${escapeHtml(visitName)}</strong> ${m.group_index !== undefined && m.group_index !== null ? `<span class="small"> (grupo ${m.group_index+1})</span>` : ''}</div>
                            <div class="small muted">${escapeHtml(estado)}${hora}${cancha}${incompletoTag}</div>
                        </div>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <input type="number" min="${meta.min}" ${meta.max!=null?`max="${meta.max}"`:''} step="${meta.step}" class="local-score" style="width:72px" value="${escapeHtml(localScore)}" placeholder="${escapeHtml(meta.placeholder)}" ${disabledAttr} title="${hasBothTeams?'':'Asigna equipos para habilitar'}">
                            <span class="muted">-</span>
                            <input type="number" min="${meta.min}" ${meta.max!=null?`max="${meta.max}"`:''} step="${meta.step}" class="visit-score" style="width:72px" value="${escapeHtml(visitScore)}" placeholder="${escapeHtml(meta.placeholder)}" ${disabledAttr} title="${hasBothTeams?'':'Asigna equipos para habilitar'}">
                            <select class="decide-mode" style="margin-left:8px;" ${disabledAttr}>
                                <option value="puntos">${isVoley ? 'Por sets' : 'Por marcador'}</option>
                                <option value="manual">Manual</option>
                            </select>
                            <select class="manual-winner hidden" style="margin-left:6px;" ${disabledAttr}>
                                <option value="">Seleccionar ganador</option>
                                <option value="local">Local</option>
                                <option value="visitante">Visitante</option>
                            </select>
                            <button class="btn save-match" style="margin-left:8px;" ${disabledAttr} title="${hasBothTeams?'':'Asigna equipos para habilitar'}">Guardar</button>
                        </div>
                    </div>`;
                });
                html += `</div>`;
            });
            matchesByDate.innerHTML = html;

            // Asegurar atributos por si cambia el deporte luego
            window.applyScoreInputsConstraints?.(currentSport);

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

            const isVoley = /voley/.test(currentSport);
            const isBasket = /baloncesto/.test(currentSport);
            const isFutbol = /(futboll|futbol|fútbol)/.test(currentSport);

            const table = {};
            let skippedNoTeams = 0;
            currentMatches.forEach(m=>{
                const localNameRaw = (m.local && (m.local.nombre||m.local.name)) || (m.local_nombre || null);
                const visitNameRaw = (m.visitante && (m.visitante.nombre||m.visitante.name)) || (m.visitante_nombre || null);
                const lid = (m.local && (m.local.id ?? m.local_id)) ?? m.local_id ?? m.equipo1_id ?? null;
                const vid = (m.visitante && (m.visitante.id ?? m.visitante_id)) ?? m.visitante_id ?? m.equipo2_id ?? null;
                // Saltar partidos sin IDs o sin nombres reales para evitar 'Local/Visitante'
                if(!lid || !vid || !localNameRaw || !visitNameRaw){ skippedNoTeams++; return; }

                if(!table[lid]) table[lid] = { name: localNameRaw, played:0, won:0, draw:0, lost:0, gf:0, ga:0, points:0 };
                if(!table[vid]) table[vid] = { name: visitNameRaw, played:0, won:0, draw:0, lost:0, gf:0, ga:0, points:0 };

                if(m.local_score != null && m.visitante_score != null){
                    const a = Number(m.local_score);
                    const b = Number(m.visitante_score);
                    if(isNaN(a) || isNaN(b)) return;

                    // Empates no válidos para basket/vóley
                    if((isBasket || isVoley) && a === b) return;

                    table[lid].played++; table[vid].played++;
                    table[lid].gf += a; table[lid].ga += b;
                    table[vid].gf += b; table[vid].ga += a;

                    if(isFutbol){
                        if(a > b){ table[lid].won++; table[lid].points += 3; table[vid].lost++; }
                        else if(a < b){ table[vid].won++; table[vid].points += 3; table[lid].lost++; }
                        else { table[lid].draw++; table[vid].draw++; table[lid].points += 1; table[vid].points += 1; }
                    } else if(isBasket){
                        if(a > b){ table[lid].won++; table[vid].lost++; table[lid].points += 2; table[vid].points += 1; }
                        else if(a < b){ table[vid].won++; table[lid].lost++; table[vid].points += 2; table[lid].points += 1; }
                    } else if(isVoley){
                        // Puntuación por sets:
                        // 3:0 o 3:1 -> ganador 3 pts, perdedor 0
                        // 3:2 -> ganador 2 pts, perdedor 1
                        if(a > b){
                            table[lid].won++; table[vid].lost++;
                            if(a === 3 && (b === 0 || b === 1)){ table[lid].points += 3; /* perdedor 0 */ }
                            else if(a === 3 && b === 2){ table[lid].points += 2; table[vid].points += 1; }
                            else {
                                // Fallback si no es a 3 sets: ganador 2, perdedor 1 si diferencia 1; si no, 3-0
                                if(Math.abs(a-b) === 1){ table[lid].points += 2; table[vid].points += 1; }
                                else { table[lid].points += 3; }
                            }
                        } else if(a < b){
                            table[vid].won++; table[lid].lost++;
                            if(b === 3 && (a === 0 || a === 1)){ table[vid].points += 3; /* perdedor 0 */ }
                            else if(b === 3 && a === 2){ table[vid].points += 2; table[lid].points += 1; }
                            else {
                                if(Math.abs(a-b) === 1){ table[vid].points += 2; table[lid].points += 1; }
                                else { table[vid].points += 3; }
                            }
                        }
                    } else {
                        // Genérico por si aparece otro deporte: usar 3-1-0
                        if(a > b){ table[lid].won++; table[lid].points += 3; table[vid].lost++; }
                        else if(a < b){ table[vid].won++; table[vid].points += 3; table[lid].lost++; }
                        else { table[lid].draw++; table[vid].draw++; table[lid].points += 1; table[vid].points += 1; }
                    }
                }
            });

            const rows = Object.keys(table).map(k => ({ id:k, ...table[k], gd: table[k].gf - table[k].ga }));
            // Orden por deporte
            rows.sort((a,b) => {
                // Puntos
                if(b.points !== a.points) return b.points - a.points;
                // Diferencial (puntos o sets o goles)
                if((b.gd - a.gd) !== 0) return (b.gd - a.gd);
                // A favor
                if((b.gf - a.gf) !== 0) return (b.gf - a.gf);
                // Nombre
                return a.name.localeCompare(b.name);
            });

            // Encabezados por deporte
            const gfLabel = isVoley ? 'GS' : (isBasket ? 'PF' : 'GF');
            const gaLabel = isVoley ? 'RC' : (isBasket ? 'PC' : 'GA');
            const gdLabel = isVoley ? 'DS' : (isBasket ? 'DP' : 'DG');
            const showDraw = !!isFutbol;

            let html = `<table><thead><tr>
                <th>#</th><th>Equipo</th><th>PJ</th><th>G</th>${showDraw?'<th>E</th>':''}<th>P</th>
                <th>${gfLabel}</th><th>${gaLabel}</th><th>${gdLabel}</th><th>Pts</th>
            </tr></thead><tbody>`;

            rows.forEach((r,i)=>{
                html += `<tr>
                    <td class="center">${i+1}</td>
                    <td>${escapeHtml(r.name)}</td>
                    <td class="center">${r.played}</td>
                    <td class="center">${r.won}</td>
                    ${showDraw?`<td class="center">${r.draw}</td>`:''}
                    <td class="center">${r.lost}</td>
                    <td class="center">${r.gf}</td>
                    <td class="center">${r.ga}</td>
                    <td class="center">${r.gd}</td>
                    <td class="center">${r.points}</td>
                </tr>`;
            });
            html += `</tbody></table>`;

            standingsBody.innerHTML = html;
            standingsMeta.textContent =
                `Equipos: ${rows.length} · Deporte: ${isFutbol?'Fútbol':(isBasket?'Baloncesto':(isVoley?'Vóley':currentSport))}`
                + (ignoredCount ? ` · Duplicados ignorados: ${ignoredCount}` : '')
                + (skippedNoTeams ? ` · Partidos incompletos: ${skippedNoTeams}` : '');
        }

        // render simple bracket (si matches incluyen property round o server retorna rounds)
        function renderBracket(){
            if(currentType !== 'eliminatoria'){
                bracketBody.innerHTML = '<div class="muted">No es eliminatoria.</div>';
                document.getElementById('btnNextRound').classList.add('hidden');
                return;
            }
            const rounds = {};
            currentMatches.forEach(m=>{
                const r = Number(m.round || 1);
                if(!rounds[r]) rounds[r]=[];
                rounds[r].push(m);
            });
            const keys = Object.keys(rounds).map(n=>Number(n)).sort((a,b)=>a-b);
            if(!keys.length){ bracketBody.innerHTML = '<div class="muted">No hay información de rondas.</div>'; return; }

            let html = '';
            keys.forEach(k=>{
                html += `<div style="margin-bottom:10px;"><strong>Ronda ${k}</strong><div class="small muted">Partidos: ${rounds[k].length}</div>`;
                rounds[k].forEach(m=>{
                    const localName = (m.local && (m.local.nombre||m.local.name)) || (m.local_nombre || '—');
                    const visitName = (m.visitante && (m.visitante.nombre||m.visitante.name)) || (m.visitante_nombre || '—');
                    const score = (m.local_score != null && m.visitante_score != null) ? ` — ${m.local_score} : ${m.visitante_score}` : '';
                    html += `<div class="small" style="margin-top:6px;">${escapeHtml(localName)} vs ${escapeHtml(visitName)} ${escapeHtml(score)}</div>`;
                });
                html += `</div>`;
            });
            bracketBody.innerHTML = html;

            // botón de “Generar siguiente ronda”
            const state = canGenerateNextRound();
            const btn = document.getElementById('btnNextRound');
            if(state.ok){ btn.classList.remove('hidden'); btn.onclick = generateNextRound; }
            else { btn.classList.add('hidden'); btn.onclick = null; }
        }

        function canGenerateNextRound(){
            if(currentType !== 'eliminatoria') return {ok:false};
            const byRound = {};
            currentMatches.forEach(m=>{
                const r = Number(m.round || 1);
                if(!byRound[r]) byRound[r]=[];
                byRound[r].push(m);
            });
            const rounds = Object.keys(byRound).map(n=>Number(n)).sort((a,b)=>a-b);
            if(!rounds.length) return {ok:false};
            const last = rounds[rounds.length-1];
            const allClosed = byRound[last].every(m => m.local_score != null && m.visitante_score != null);
            if(!allClosed) return {ok:false};
            // que no exista ya la siguiente
            const existsNext = currentMatches.some(m => Number(m.round||1) === last+1);
            if(existsNext) return {ok:false};
            // pares de ganadores
            const winnersCount = byRound[last].length;
            if(winnersCount < 2 || winnersCount % 2 !== 0) return {ok:false};
            return {ok:true, from:last};
        }

        async function generateNextRound(){
            const torneo = filterTorneo.value;
            const cg = canGenerateNextRound();
            if(!cg.ok){ alert('No se puede generar la siguiente ronda aún.'); return; }
            const btn = document.getElementById('btnNextRound');
            const prev = btn.textContent; btn.textContent='Generando...'; btn.disabled=true;
            try{
                const res = await fetch(`{{ route('partidos.advance') }}`, {
                    method:'POST',
                    headers:{
                        'X-CSRF-TOKEN': token,
                        'Accept':'application/json',
                        'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: new URLSearchParams({ torneo_id: torneo, from_round: String(cg.from) }).toString()
                });
                const json = await res.json().catch(()=>null);
                if(!res.ok){ alert(json?.error || `Error ${res.status}`); return; }
                // recargar partidos
                await loadMatches();
            } finally {
                btn.textContent=prev; btn.disabled=false;
            }
        }

        // POST para guardar resultado de un match
        async function saveMatchResult(wrapper){
            const matchId = wrapper.getAttribute('data-match-id');
            const aStr = wrapper.querySelector('.local-score').value;
            const bStr = wrapper.querySelector('.visit-score').value;
            const decideMode = wrapper.querySelector('.decide-mode').value;
            const manualWinnerEl = wrapper.querySelector('.manual-winner');

            const isVoley = /voley/.test(currentSport);
            const isBasket = /baloncesto/.test(currentSport);
            const isFutbol = /(futboll|futbol|fútbol)/.test(currentSport);
            const meta = (window.sportMeta ? window.sportMeta(currentSport) : { min:0, max:999 });

            const a = aStr === '' ? null : Number(aStr);
            const b = bStr === '' ? null : Number(bStr);

            // Validaciones por deporte
            if(a !== null && b !== null){
                if((isBasket || isVoley) && a === b){
                    alert('No se permiten empates en ' + (isBasket ? 'baloncesto' : 'vóley') + '.');
                    return;
                }
                if(isVoley){
                    // ganador llega a 3; total entre 3 y 5; valores >=0
                    if(a < 0 || b < 0 || a > 3 || b > 3){
                        alert('En vóley, los sets deben estar entre 0 y 3.');
                        return;
                    }
                    if(a !== 3 && b !== 3){
                        alert('En vóley, el ganador debe llegar a 3 sets.');
                        return;
                    }
                    const total = a + b;
                    if(total < 3 || total > 5){
                        alert('En vóley, el total de sets debe estar entre 3 y 5 (p. ej. 3–0, 3–1, 3–2).');
                        return;
                    }
                } else {
                    // Aplicar límites genéricos por deporte (fútbol, baloncesto, etc.)
                    if(a < meta.min || b < meta.min || (meta.max != null && (a > meta.max || b > meta.max))){
                        alert(`Los marcadores deben estar entre ${meta.min} y ${meta.max ?? '∞'} para ${currentSport}.`);
                        return;
                    }
                 }
            }

            let winner = null;
            if(decideMode === 'manual' && manualWinnerEl){
                winner = manualWinnerEl.value || null;
            } else if(a !== null && b !== null){
                if(a > b) winner = 'local';
                else if(a < b) winner = 'visitante';
            }

            const payload = new URLSearchParams();
            if(a !== null) payload.append('local_score', String(a));
            if(b !== null) payload.append('visitante_score', String(b));
            if(decideMode) payload.append('winner_mode', decideMode);
            if(winner) payload.append('winner', winner);

            const url = `/admin/partidos/${matchId}/set-result`;

            const saveBtn = wrapper.querySelector('.save-match');
            const prevText = saveBtn.textContent;
            saveBtn.textContent = 'Guardando...';
            saveBtn.disabled = true;

            try{
                const res = await fetch(url, {
                    method:'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8',
                        'Accept':'application/json',
                        'X-Requested-With':'XMLHttpRequest'
                    },
                    body: payload.toString()
                });

                let json = null, text = null;
                const ct = res.headers.get('content-type') || '';
                if(ct.includes('application/json')) json = await res.json().catch(()=>null);
                else text = await res.text().catch(()=>'');

                if(!res.ok){
                    alert('Error al guardar: ' + (json?.error || text || res.status));
                    saveBtn.textContent = prevText;
                    saveBtn.disabled = false;
                    return;
                }

                if(json && json.match){
                    const idx = currentMatches.findIndex(x => String(x.id) === String(json.match.id));
                    if(idx !== -1) currentMatches[idx] = json.match;
                }
                saveBtn.textContent = 'Guardado ✓';
                setTimeout(()=>{ saveBtn.textContent = prevText; saveBtn.disabled = false; }, 1200);

                currentMatches = dedupeMatches(currentMatches);
                computeAndRenderStandings();
                renderBracket();
            }catch(err){
                alert('Error red: ' + err.message);
                saveBtn.textContent = prevText;
                saveBtn.disabled = false;
            }
        }

        // request al servidor para obtener partidos del torneo seleccionado
        async function loadMatches(){
            const torneo = filterTorneo.value;
            if(!torneo){ alert('Selecciona un torneo.'); return; }
            // detectar deporte seleccionado
            const selOpt = filterTorneo.options[filterTorneo.selectedIndex];
            currentSport = (selOpt?.dataset?.deporte || selOpt?.textContent || 'generico').toLowerCase().trim();

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
                // dedupe para evitar "mismos partidos"
                matches = dedupeMatches(matches);
                if(typeFilter === 'pendientes') matches = matches.filter(m => !m.estado || m.estado === 'pendiente' || m.estado === 'pending' || (m.local_score == null || m.visitante_score == null));
                else if(typeFilter === 'finalizados') matches = matches.filter(m => m.estado === 'finalizado' || m.estado === 'done' || (m.local_score != null && m.visitante_score != null));
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

    <script>
    (function(){
        // Ajusta aquí los topes por deporte (cámbialos si ya los modificaste en tu proyecto)
        const SPORT_LIMITS = {
            voley:       { placeholder:'Sets',            min:0, max:3,   step:1 },
            baloncesto:  { placeholder:'Puntos (0–199)',  min:0, max:199, step:1 },
            futbol:      { placeholder:'Goles (0–99)',    min:0, max:99,  step:1 },
            futboll:     { placeholder:'Goles (0–99)',    min:0, max:99,  step:1 }, // variante escrita en BD
            default:     { placeholder:'Marcador',        min:0, max:999, step:1 },
        };

        function sportMeta(sport){
            const d = (sport||'').toLowerCase();
            if(d.includes('voley')) return SPORT_LIMITS.voley;
            if(d.includes('baloncesto')) return SPORT_LIMITS.baloncesto;
            if(d.includes('futboll') || d.includes('futbol') || d.includes('fútbol')) return SPORT_LIMITS.futboll;
            return SPORT_LIMITS.default;
        }
        // Exponer para otros scripts
        window.sportMeta = sportMeta;
        window.applyScoreInputsConstraints = function(currentSport){
            const meta = sportMeta(currentSport);
            document.querySelectorAll('input.local-score, input.visit-score').forEach(inp=>{
                inp.placeholder = meta.placeholder; inp.min = meta.min; inp.max = meta.max; inp.step = meta.step;
            });
        };
        // Si la vista ya tiene inputs, aplica al cargar
        window.addEventListener('DOMContentLoaded', ()=> window.applyScoreInputsConstraints?.(document.querySelector('#filterTorneo option:checked')?.dataset?.deporte || ''));
    })();
    </script>
</x-app-layout>