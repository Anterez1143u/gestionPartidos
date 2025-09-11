<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Gestión de Partidos') }}</title>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,600,700,800" rel="stylesheet" />
    <style>
        :root{ --bg:#0f2436; --card:#162a40; --accent:#00c896; --accent2:#0d6efd; --muted:#a9b4c2; --line:#1c2f46; }
        *{ box-sizing:border-box; }
        body{ margin:0; background:var(--bg); color:#fff; font-family:'Instrument Sans',ui-sans-serif,system-ui,sans-serif; }
        a{ color:var(--accent); text-decoration:none; }
        a:hover{ color:var(--accent2); }

        .wrap{ max-width:1140px; margin:0 auto; padding:28px 16px 48px; }
        .header{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:18px; }
        .brand{ display:flex; align-items:center; gap:12px; }
        .logo{ width:36px; height:36px; border-radius:10px; background:linear-gradient(130deg,var(--accent),#16a085); }
        .title{ margin:0; font-size:1.9rem; font-weight:800; color:var(--accent); letter-spacing:.3px; }
        .nav a{ display:inline-block; background:var(--accent); color:#fff; padding:10px 16px; border-radius:10px; font-weight:700; margin-left:8px; box-shadow:0 6px 22px rgba(0,200,150,.15); }
        .nav a:hover{ background:var(--accent2); }

        .filters{ background:var(--card); border-radius:14px; padding:14px; display:flex; gap:10px; flex-wrap:wrap; align-items:center; box-shadow:0 8px 28px rgba(13,110,253,.10); margin-bottom:16px; }
        .filters label{ font-weight:700; color:#eaf6ff; }
        .input, .select{ background:#20344e; color:#fff; border:1.5px solid var(--accent); border-radius:10px; padding:10px 12px; min-width:220px; }

        .grid{ display:grid; gap:16px; grid-template-columns:1fr; }
        @media (min-width:900px){ .grid{ grid-template-columns:1.15fr .85fr; } }

        .card{ background:var(--card); border-radius:14px; padding:18px; box-shadow:0 10px 40px rgba(13,110,253,.12); }
        .card h3{ margin:0 0 10px; font-size:1.1rem; color:var(--accent); }
        .list{ list-style:none; margin:0; padding:0; }
        .item{ padding:10px 0; border-bottom:1px solid var(--line); }
        .item:last-child{ border-bottom:none; }

        .row{ display:flex; justify-content:space-between; gap:8px; flex-wrap:wrap; }
        .teams{ font-weight:700; }
        .muted{ color:var(--muted); font-size:.95rem; }
        .badge{ background:#20344e; color:#e6f9f4; border:1px solid var(--accent); padding:3px 8px; border-radius:999px; font-size:.78rem; }
        .score{ font-weight:800; }
        .empty{ color:#cdd6e0; font-style:italic; }

        .chips{ display:flex; flex-wrap:wrap; gap:10px; }
        .chip{ background:#20344e; border:1px solid var(--accent); color:#fff; border-radius:999px; padding:8px 12px; font-weight:600; }

        .links{ display:flex; gap:10px; flex-wrap:wrap; margin-top:10px; }
        .btn-link{ background:#20344e; border:1.5px solid var(--accent); color:#fff; border-radius:10px; padding:8px 12px; font-weight:700; }
        .btn-link:hover{ background:var(--accent2); border-color:var(--accent2); }
    </style>
</head>
<body>
@php
    // Carga de datos reales, con tolerancia si algún modelo no existe
    $torneos = collect(); $proximos = collect(); $recientes = collect(); $equipos = collect();
    $selectedTorneoId = request('torneo'); $q = trim((string)request('q',''));
    try {
        $torneos = \App\Models\Torneo::when($q, fn($qq)=>$qq->where(function($w) use ($q){
                $w->where('deporte','like',"%$q%")->orWhere('nombre','like',"%$q%");
            }))
            ->orderByDesc('created_at')->take(12)->get();

        $base = \App\Models\Partido::with(['equipo1:id,nombre','equipo2:id,nombre','torneo:id,deporte,nombre'])
            ->when($selectedTorneoId, fn($qq)=>$qq->where('torneo_id',$selectedTorneoId));

        $proximos = (clone $base)->whereIn('estado',['pendiente','programado','en_curso'])
            ->orderBy('fecha')->orderBy('hora')->take(8)->get();

        $recientes = (clone $base)->where('estado','finalizado')
            ->orderByDesc('fecha')->orderByDesc('hora')->take(8)->get();

        $equipos = \App\Models\Equipo::select('id','nombre','torneo_id')
            ->when($selectedTorneoId, fn($qq)=>$qq->where('torneo_id',$selectedTorneoId))
            ->latest()->take(20)->get();
    } catch (\Throwable $e) {}
    function fdate($d){ try{ return $d?\Carbon\Carbon::parse($d)->format('d/m/Y'):'—'; }catch(\Throwable $e){ return '—'; } }
@endphp

<div class="wrap">
    <!-- Cabecera -->
    <div class="header">
        <div class="brand">
            <div class="logo"></div>
            <h1 class="title">Explora torneos y partidos</h1>
        </div>
        <!-- Enlaces públicos SIN route() para evitar error de rutas y evitar redirecciones a login -->
        <div class="nav">
            <a href="/torneos">Torneos</a>
            <a href="/equipos">Equipos</a>
            <a href="/partidos">Partidos</a>
        </div>
    </div>

    <!-- Filtros -->
    <form class="filters" method="GET" action="/">
        <label for="q">Buscar</label>
        <input id="q" name="q" class="input" value="{{ $q }}" placeholder="Deporte o nombre del torneo">
        <label for="torneo">Torneo</label>
        <select id="torneo" name="torneo" class="select" onchange="this.form.submit()">
            <option value="">Todos</option>
            @php
                // No seleccionar columnas para evitar errores si faltan
                $torneosSelect = \App\Models\Torneo::orderByDesc('created_at')->get();
            @endphp
            @foreach($torneosSelect as $t)
                <option value="{{ $t->id }}" {{ (string)$t->id===(string)$selectedTorneoId?'selected':'' }}>
                    {{ $t->nombre ?? $t->deporte ?? 'Torneo #'.$t->id }}
                    @if(!empty($t->numero_participantes)) ({{ $t->numero_participantes }}) @endif
                </option>
            @endforeach
        </select>
        @if($selectedTorneoId || $q)
            <a class="btn-link" href="/">Quitar filtros</a>
        @endif
        <button class="btn-link" type="submit">Buscar</button>
    </form>

    <div class="grid">
        <!-- Columna izquierda: Próximos y resultados -->
        <div>
            <div class="card">
                <h3>Próximos partidos</h3>
                @if($proximos->isEmpty())
                    <div class="empty">No hay partidos próximos.</div>
                @else
                    <ul class="list">
                        @foreach($proximos as $p)
                            <li class="item">
                                <div class="row">
                                    <span class="muted">{{ fdate($p->fecha) }} {{ $p->hora ? '· '.$p->hora : '' }}</span>
                                    <span class="badge">{{ optional($p->torneo)->deporte ?? optional($p->torneo)->nombre ?? 'Torneo' }}</span>
                                </div>
                                <div class="row">
                                    <span class="teams">{{ optional($p->equipo1)->nombre ?? '—' }} vs {{ optional($p->equipo2)->nombre ?? '—' }}</span>
                                    <span class="muted">{{ $p->cancha ? 'Cancha '.$p->cancha : '' }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <div class="links">
                    <a class="btn-link" href="/partidos">Ver todos los partidos</a>
                </div>
            </div>

            <div class="card" style="margin-top:14px">
                <h3>Resultados recientes</h3>
                @if($recientes->isEmpty())
                    <div class="empty">Aún no hay resultados cargados.</div>
                @else
                    <ul class="list">
                        @foreach($recientes as $p)
                            <li class="item">
                                <div class="row">
                                    <span class="muted">{{ fdate($p->fecha) }}</span>
                                    <span class="badge">Final</span>
                                </div>
                                <div class="row">
                                    <span class="teams">
                                        {{ optional($p->equipo1)->nombre ?? '—' }}
                                        <span class="score">{{ $p->marcador_equipo1 ?? $p->resultado_equipo1 ?? $p->goles_local ?? '—' }}</span>
                                        —
                                        <span class="score">{{ $p->marcador_equipo2 ?? $p->resultado_equipo2 ?? $p->goles_visitante ?? '—' }}</span>
                                        {{ optional($p->equipo2)->nombre ?? '—' }}
                                    </span>
                                    <span class="muted">{{ optional($p->torneo)->deporte ?? optional($p->torneo)->nombre ?? '' }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <!-- Columna derecha: Torneos y Equipos -->
        <div>
            <div class="card">
                <h3>Torneos destacados</h3>
                @if($torneos->isEmpty())
                    <div class="empty">No hay torneos.</div>
                @else
                    <div style="display:grid; gap:12px; grid-template-columns:repeat(auto-fill,minmax(250px,1fr))">
                        @foreach($torneos as $t)
                            <div style="background:#20344e; border:1px solid var(--line); border-radius:12px; padding:14px">
                                <div style="font-weight:800; margin-bottom:6px">{{ $t->nombre ?? $t->deporte ?? 'Torneo #'.$t->id }}</div>
                                <div class="muted">
                                    {{ $t->deporte ? 'Deporte: '.$t->deporte.' · ' : '' }}
                                    Participantes: {{ $t->numero_participantes ?? '—' }}
                                    @if(!empty($t->fecha_inicio)) · Inicio: {{ fdate($t->fecha_inicio) }} @endif
                                </div>
                                <div class="links" style="margin-top:8px">
                                    <a class="btn-link" href="/partidos?torneo={{ $t->id }}">Partidos</a>
                                    <a class="btn-link" href="/equipos?torneo={{ $t->id }}">Equipos</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card" style="margin-top:14px">
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
                <div class="links" style="margin-top:10px">
                    <a class="btn-link" href="/equipos">Ver todos los equipos</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
