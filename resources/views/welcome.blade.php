<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tournamet</title>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,600,700,800" rel="stylesheet" />
    <style>
        :root{ --bg:#0b1c2a; --bg2:#0f2436; --card:#12283f; --glass:#173656ee; --line:#23415e; --accent:#00c896; --accent2:#0d6efd; --accent3:#14b8a6; --text:#eaf6ff; --muted:#a9b4c2; }
        *{ box-sizing:border-box; } body{ margin:0; background:linear-gradient(160deg,var(--bg) 0%, var(--bg2) 60%); color:var(--text); font-family:'Instrument Sans',ui-sans-serif,system-ui,sans-serif; }
        a{ color:inherit; text-decoration:none; }
        .wrap{ max-width:1180px; margin:0 auto; padding:24px 16px 56px; }
        .nav{ display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .brand{ display:flex; align-items:center; gap:12px; }
        .logo{ width:40px; height:40px; border-radius:12px; background:conic-gradient(from 160deg at 50% 50%, var(--accent), #16a085, var(--accent2)); box-shadow:0 6px 30px rgba(13,110,253,.25); }
        .title{ margin:0; font-size:1.8rem; font-weight:800; letter-spacing:.2px; }
        .actions{ display:flex; gap:10px; flex-wrap:wrap; }
        .btn{ --bgbtn:#1a3553; display:inline-flex; align-items:center; justify-content:center; padding:10px 16px; border-radius:12px; font-weight:800; letter-spacing:.2px; border:1.5px solid transparent; color:#fff; background:var(--bgbtn); box-shadow:0 6px 22px rgba(0,200,150,.15); transition:all .15s ease; }
        .btn-primary{ background:linear-gradient(135deg,var(--accent) 0%, var(--accent3) 100%); border-color:transparent; color:#06202d; }
        .btn-secondary{ background:#193a5e; border-color:#24d1a9; color:#eafff9; }
        .btn-ghost{ background:transparent; border-color:#2a4e73; color:#cfe7ff; }
        .hero{ margin-top:22px; background:radial-gradient(1200px 300px at 50% -10%, #185a8a33, transparent), var(--card); border:1px solid var(--line); border-radius:16px; padding:22px; display:grid; grid-template-columns:1.2fr .8fr; gap:14px; }
        @media (max-width:900px){ .hero{ grid-template-columns:1fr; } }
        .hero h1{ margin:0 0 8px; font-size:2rem; color:#fff; }
        .hero p{ margin:0 0 12px; color:var(--muted); }
        .hero-ctas{ display:flex; gap:10px; flex-wrap:wrap; margin-top:8px; }
        .hero-side{ background:var(--glass); border:1px solid var(--line); border-radius:14px; padding:14px; backdrop-filter: blur(6px); }
        .stats{ margin-top:12px; display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
        @media (max-width:700px){ .stats{ grid-template-columns:1fr; } }
        .stat{ background:#12314f; border:1px solid var(--line); border-radius:12px; padding:12px; text-align:center; }
        .stat .n{ font-size:1.4rem; font-weight:800; color:#fff; }
        .stat .k{ color:var(--muted); }
        .filters{ margin-top:14px; background:#132e49; border:1px solid var(--line); border-radius:12px; padding:12px; display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
        .filters label{ font-weight:700; color:#dfefff; }
        .select{ background:#173a5a; color:#fff; border:1.5px solid var(--accent); border-radius:10px; padding:9px 12px; min-width:220px; }
        .columns{ margin-top:16px; display:grid; grid-template-columns:1.2fr .8fr; gap:16px; }
        @media (max-width:900px){ .columns{ grid-template-columns:1fr; } }
        .card{ background:#12283f; border:1px solid var(--line); border-radius:14px; padding:16px; box-shadow:0 8px 32px rgba(13,110,253,.12); }
        .card h3{ margin:0 0 10px; font-size:1.05rem; color:var(--accent); }
        .list{ list-style:none; margin:0; padding:0; }
        .item{ padding:10px 0; border-bottom:1px solid var(--line); } .item:last-child{ border-bottom:none; }
        .row{ display:flex; justify-content:space-between; gap:8px; flex-wrap:wrap; }
        .muted{ color:var(--muted); font-size:.95rem; }
        .teams{ font-weight:800; color:#fff; }
        .score{ font-weight:800; }
        /* Mejora resultados recientes */
        .result-line{ display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .result-side{ display:flex; align-items:center; gap:8px; }
        .pill{ background:#173a5a; border:1px solid var(--accent); color:#fff; padding:2px 10px; border-radius:999px; min-width:36px; text-align:center; font-variant-numeric: tabular-nums; }
        .pill.win{ background:linear-gradient(135deg,var(--accent) 0%, var(--accent3) 100%); color:#06202d; border-color:transparent; }
        .team-name{ font-weight:800; color:#fff; }
        .team-name.win{ color:var(--accent); }
        .sep{ color:var(--muted); padding:0 4px; }
    </style>
</head>
<body>
@php
    use Carbon\Carbon;

    // Normalizador de deporte
    function normSport(?string $v): ?string {
        if ($v === null) return null;
        $m = [
            'futbol'=>'futboll', 'fútbol'=>'futboll', 'utboll'=>'futboll',
            'voley'=>'voley', 'baloncesto'=>'baloncesto'
        ];
        $vv = strtolower(trim($v));
        return $m[$vv] ?? $vv;
    }
    $validSports = ['futboll','voley','baloncesto'];
    $labels = ['futboll'=>'Fútbol','voley'=>'Vóley','baloncesto'=>'Baloncesto'];

    $selectedSport = normSport(request('deporte'));
    if ($selectedSport && !in_array($selectedSport, $validSports, true)) { $selectedSport = null; }
    $selectedTorneoId = request('torneo');

    // Deportes disponibles (distintos + normalizados + válidos)
    $sports = \App\Models\Torneo::select('deporte')->distinct()->pluck('deporte')
        ->map(fn($d)=>normSport($d))
        ->filter(fn($d)=>in_array($d,$validSports,true))
        ->unique()
        ->values()
        ->all();

    // Torneos (filtrados por deporte si aplica)
    $torneos = \App\Models\Torneo::when($selectedSport, fn($q)=>$q->where('deporte',$selectedSport))
        ->orderByDesc('created_at')->get();

    // Base de partidos con filtros
    $today = Carbon::today()->toDateString();
    $base = \App\Models\Partido::with([
         'equipo1:id,nombre',
         'equipo2:id,nombre',
         'torneo:id,deporte',
         // incluir resultado para leer marcador guardado
         'resultado:id,partido_id,marcador_equipo1,marcador_equipo2,updated_at',
     ])
        ->when($selectedTorneoId, fn($q)=>$q->where('torneo_id',$selectedTorneoId))
        ->when(!$selectedTorneoId && $selectedSport, fn($q)=>$q->whereHas('torneo', fn($t)=>$t->where('deporte',$selectedSport)));

    $proximos  = (clone $base)->whereDate('fecha','>=',$today)->orderBy('fecha')->orderBy('hora')->take(8)->get();
    // Tomar los últimos 6 resultados guardados, independientemente de la fecha del partido
    $recientes = (clone $base)
        ->whereHas('resultado')
        ->get()
        ->sortByDesc(fn($p)=>optional($p->resultado)->updated_at ?? $p->updated_at)
        ->take(6);

    // Equipos filtrados por torneo o por deporte
    $equipos = \App\Models\Equipo::when($selectedTorneoId, fn($q)=>$q->where('torneo_id',$selectedTorneoId))
        ->when(!$selectedTorneoId && $selectedSport, fn($q)=>$q->whereHas('torneo', fn($t)=>$t->where('deporte',$selectedSport)))
        ->select('id','nombre')->latest()->take(18)->get();

    // Métricas dinámicas según filtros
    $countTorneos = \App\Models\Torneo::when($selectedSport, fn($q)=>$q->where('deporte',$selectedSport))->count();
    $countEquipos = \App\Models\Equipo::when($selectedTorneoId, fn($q)=>$q->where('torneo_id',$selectedTorneoId))
        ->when(!$selectedTorneoId && $selectedSport, fn($q)=>$q->whereHas('torneo', fn($t)=>$t->where('deporte',$selectedSport)))->count();
    $countPartidos = \App\Models\Partido::when($selectedTorneoId, fn($q)=>$q->where('torneo_id',$selectedTorneoId))
        ->when(!$selectedTorneoId && $selectedSport, fn($q)=>$q->whereHas('torneo', fn($t)=>$t->where('deporte',$selectedSport)))->count();

    // Devuelve [local, visitante] usando primero resultados.marcador_* y luego columnas de partidos
    function matchScorePair($p): array {
        // 1) resultado (si existe)
        if (isset($p->resultado) && $p->resultado) {
            $ra = $p->resultado->marcador_equipo1 ?? null;
            $rb = $p->resultado->marcador_equipo2 ?? null;
            if ($ra !== null || $rb !== null) {
                return [$ra, $rb];
            }
        }
        // 2) columnas posibles en partidos
        $candidates = [
            ['local_score','visitante_score'],
            ['goles_local','goles_visitante'],
            ['marcador_local','marcador_visitante'],
            ['marcador_equipo1','marcador_equipo2'],
            ['resultado_equipo1','resultado_equipo2'],
            ['puntos_local','puntos_visitante'],
            ['sets_local','sets_visitante'],
        ];
        static $colsExist = null;
        if ($colsExist === null) {
            $colsExist = [];
            foreach ($candidates as [$a,$b]) {
                $colsExist[$a] = \Illuminate\Support\Facades\Schema::hasColumn('partidos',$a);
                $colsExist[$b] = \Illuminate\Support\Facades\Schema::hasColumn('partidos',$b);
            }
        }
        foreach ($candidates as [$a,$b]) {
            if (($colsExist[$a] ?? false) && ($colsExist[$b] ?? false)) {
                $la = $p->{$a} ?? null;
                $vb = $p->{$b} ?? null;
                if ($la !== null || $vb !== null) {
                    return [$la, $vb];
                }
            }
        }
        return [null, null];
    }

    function fdate($d){ try{ return $d? \Carbon\Carbon::parse($d)->format('d/m/Y') : '—'; }catch(\Throwable $e){ return '—'; } }
@endphp

<div class="wrap">
    <!-- Nav -->
    <div class="nav">
        <div class="brand">
            <div class="logo" aria-hidden="true"></div>
            <h1 class="title">Tournamet</h1>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="/partidos">Partidos</a>
            <a class="btn btn-secondary" href="/equipos">Equipos</a>
            <a class="btn btn-secondary" href="/torneos">Torneos</a>
            @auth
                <a class="btn btn-primary" href="{{ url('/dashboard') }}">Panel</a>
            @else
                <a class="btn btn-primary" href="{{ route('login') }}">Iniciar sesión</a>
                <a class="btn btn-ghost" href="{{ Route::has('register') ? route('register') : '/register' }}">Crear cuenta</a>
            @endauth
        </div>
    </div>

    <!-- Hero -->
    <section class="hero" aria-label="Introducción Tournamet">
        <div>
            <h1>Resultados y calendarios deportivos</h1>
            <p>Filtra por deporte o torneo para explorar próximos partidos, resultados y equipos.</p>
            <div class="hero-ctas">
                <a class="btn btn-primary" href="/partidos">Ver partidos</a>
                <a class="btn btn-secondary" href="/torneos">Explorar torneos</a>
                @guest <a class="btn btn-ghost" href="{{ Route::has('register') ? route('register') : '/register' }}">Crear cuenta</a> @endguest
            </div>
        </div>
        <div class="hero-side">
            <strong>¿Qué verás?</strong>
            <ul style="margin:8px 0 0 18px; padding:0; line-height:1.6">
                <li>Próximos partidos y resultados recientes del deporte elegido.</li>
                <li>Equipos relacionados con el filtro.</li>
                <li>Métricas dinámicas según filtros.</li>
            </ul>
        </div>
    </section>

    <!-- Métricas -->
    <div class="stats" aria-label="Métricas">
        <div class="stat"><div class="n">{{ $countTorneos }}</div><div class="k">Torneos</div></div>
        <div class="stat"><div class="n">{{ $countEquipos }}</div><div class="k">Equipos</div></div>
        <div class="stat"><div class="n">{{ $countPartidos }}</div><div class="k">Partidos</div></div>
    </div>

    <!-- Filtros: deporte + torneo -->
    <form class="filters" method="GET" action="/">
        <label for="deporte">Deporte</label>
        <select id="deporte" name="deporte" class="select" onchange="this.form.submit()">
            <option value="">Todos</option>
            @foreach($sports as $s)
                <option value="{{ $s }}" {{ $selectedSport===$s ? 'selected' : '' }}>
                    {{ $labels[$s] ?? ucfirst($s) }}
                </option>
            @endforeach
        </select>

        <label for="torneo">Torneo</label>
        <select id="torneo" name="torneo" class="select" onchange="this.form.submit()">
            <option value="">Todos</option>
            @foreach($torneos as $t)
                <option value="{{ $t->id }}" {{ (string)$t->id===(string)$selectedTorneoId?'selected':'' }}>
                    {{ $t->nombre ?? ($labels[normSport($t->deporte)] ?? $t->deporte) }}
                </option>
            @endforeach
        </select>

        @if($selectedSport || $selectedTorneoId)
            <a class="btn btn-ghost" href="/">Quitar filtros</a>
        @endif
    </form>

    <!-- Contenido principal -->
    <div class="columns">
        <!-- Próximos -->
        <div class="card" aria-label="Próximos partidos">
            <h3>Próximos partidos</h3>
            @if($proximos->isEmpty())
                <div class="empty">No hay partidos próximos.</div>
            @else
                <ul class="list">
                    @foreach($proximos as $p)
                        <li class="item">
                            <div class="row">
                                <span class="muted">{{ fdate($p->fecha) }} {{ !empty($p->hora) ? '· '.$p->hora : '' }}</span>
                                @php $sd = normSport(optional($p->torneo)->deporte); @endphp
                                <span class="muted">{{ $labels[$sd] ?? $sd ?? '' }}</span>
                            </div>
                            <div class="row">
                                <span class="teams">{{ optional($p->equipo1)->nombre ?? '—' }} vs {{ optional($p->equipo2)->nombre ?? '—' }}</span>
                                <span class="muted">{{ !empty($p->cancha) ? 'Cancha '.$p->cancha : '' }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div style="margin-top:10px">
                    <a class="btn btn-secondary" href="/partidos{{ $selectedTorneoId ? ('?torneo='.$selectedTorneoId) : ($selectedSport ? ('?deporte='.$selectedSport) : '') }}">Ver todos</a>
                </div>
            @endif
        </div>

        <!-- Resultados + Equipos -->
        <div style="display:grid; gap:16px;">
            <div class="card" aria-label="Resultados recientes">
                <h3>Resultados recientes</h3>
                @if($recientes->isEmpty())
                    <div class="empty">Aún no hay resultados.</div>
                @else
                    <ul class="list">
                        @foreach($recientes as $p)
                            <li class="item">
                                <div class="row">
                                    <span class="muted">{{ fdate($p->fecha) }}</span>
                                    @php $sd = normSport(optional($p->torneo)->deporte); @endphp
                                    <span class="muted">{{ $labels[$sd] ?? $sd ?? '' }}</span>
                                </div>
                                @php
                                    [$a,$b] = matchScorePair($p);
                                    $has = $a !== null && $b !== null;
                                    $win = $has ? ($a > $b ? 'L' : ($b > $a ? 'V' : 'E')) : null;
                                    $lName = optional($p->equipo1)->nombre ?? '—';
                                    $vName = optional($p->equipo2)->nombre ?? '—';
                                @endphp
                                <div class="result-line">
                                    <div class="result-side">
                                        <span class="team-name {{ $win==='L' ? 'win' : '' }}">{{ $lName }}</span>
                                    </div>
                                    <div class="result-side">
                                        <span class="pill score {{ $win==='L' ? 'win' : '' }}">{{ $a ?? '—' }}</span>
                                        <span class="sep">-</span>
                                        <span class="pill score {{ $win==='V' ? 'win' : '' }}">{{ $b ?? '—' }}</span>
                                    </div>
                                    <div class="result-side">
                                        <span class="team-name {{ $win==='V' ? 'win' : '' }}">{{ $vName }}</span>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card" aria-label="Equipos">
                <h3>Equipos</h3>
                @if($equipos->isEmpty())
                    <div class="empty">No hay equipos cargados.</div>
                @else
                    <div class="chips">
                        @foreach($equipos as $e)
                            <span class="chip">{{ $e->nombre }}</span>
                        @endforeach
                    </div>
                @endif
                <div style="margin-top:10px">
                    <a class="btn btn-secondary" href="/equipos{{ $selectedTorneoId ? ('?torneo='.$selectedTorneoId) : ($selectedSport ? ('?deporte='.$selectedSport) : '') }}">Ver equipos</a>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        Tournamet — Vista pública en solo lectura. Filtrando por: 
        {{ $selectedSport ? ($labels[$selectedSport] ?? ucfirst($selectedSport)) : 'Todos los deportes' }}
        {{ $selectedTorneoId ? ' · Torneo seleccionado' : '' }}
    </div>
</div>
</body>
</html>
