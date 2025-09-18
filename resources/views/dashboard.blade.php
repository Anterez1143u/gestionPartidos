<x-app-layout>

    <style>
        :root{
            --bg:#0f2436; --bg2:#142c44; --card:#192f4b; --line:#244465;
            --accent:#00c896; --accent2:#0d6efd; --text:#eaf6ff; --muted:#a9b4c2;
        }
        body { background: linear-gradient(160deg,var(--bg) 0%, var(--bg2) 60%) !important; color: var(--text); }
        nav, .navbar, .bg-white { background: #1c2e4b !important; color:#fff !important; }

        .wrap{ max-width:1200px; margin:0 auto; padding:20px 16px 48px; }

        .hero{
            background: radial-gradient(900px 250px at 30% -10%, #1a6bb933, transparent), var(--card);
            border:1px solid var(--line); border-radius:16px; padding:20px; display:grid; grid-template-columns:1.2fr .8fr; gap:14px;
            box-shadow:0 10px 32px rgba(13,110,253,.15);
        }
        @media (max-width:900px){ .hero{ grid-template-columns:1fr; } }
        .hero h1{ margin:0 0 6px; font-size:1.6rem; font-weight:800; }
        .hero p{ margin:0; color:var(--muted); }

        .actions{ display:flex; flex-wrap:wrap; gap:10px; margin-top:10px; }
        .btn{
            display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:12px;
            font-weight:800; border:1.5px solid transparent; color:#fff; background:#1b3a5d;
            box-shadow:0 6px 22px rgba(0,200,150,.12); transition:all .15s ease;
        }
        .btn:hover{ transform:translateY(-1px); }
        .btn-primary{ background:linear-gradient(135deg,var(--accent), #14b8a6); color:#06202d; }
        .btn-primary:hover{ filter:brightness(1.05); }
        .btn-outline{ background:transparent; border-color:#2a4e73; }
        .btn-outline:hover{ background:#1b3a5d; border-color:#3f79b1; }

        .grid{ display:grid; grid-template-columns:repeat(12,1fr); gap:14px; margin-top:14px; }
        .col-12{ grid-column:span 12; }
        .col-4{ grid-column:span 4; } .col-8{ grid-column:span 8; }
        @media (max-width:900px){ .col-4,.col-8{ grid-column:span 12; } }

        .card{ background:var(--card); border:1px solid var(--line); border-radius:14px; padding:16px; box-shadow:0 8px 28px rgba(13,110,253,.12); }
        .card h3{ margin:0 0 10px; font-size:1.05rem; color:var(--accent); }

        .stats{ display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
        @media (max-width:700px){ .stats{ grid-template-columns:1fr; } }
        .stat{ background:#163252; border:1px solid var(--line); border-radius:12px; padding:14px; text-align:center; }
        .stat .n{ font-size:1.6rem; font-weight:800; color:#fff; }
        .stat .k{ color:var(--muted); }

        .links{ display:flex; flex-wrap:wrap; gap:10px; }
        .link{ color:var(--accent); font-weight:700; text-decoration:none; }
        .link:hover{ color:var(--accent2); text-decoration:underline; }
    </style>

    @php
        $counts = ['torneos'=>0,'equipos'=>0,'partidos'=>0];
        try {
            $counts['torneos'] = \App\Models\Torneo::count();
            $counts['equipos'] = \App\Models\Equipo::count();
            $counts['partidos'] = \App\Models\Partido::count();
        } catch (\Throwable $e) { /* silencioso */ }
    @endphp

    <div class="wrap">
        <!-- Hero -->
        <section class="hero">
            <div>
                <h1>Bienvenido a Tournamet</h1>
                <p>Administra torneos, equipos y calendarios desde un solo lugar. Usa las acciones rápidas o navega por las secciones.</p>
                <div class="actions">
                    <a href="{{ route('admin.torneos.create') }}" class="btn btn-primary">🏆 Crear torneo</a>
                    <a href="{{ route('torneos.index') }}" class="btn btn-outline">📑 Ver torneos</a>
                    <a href="{{ route('equipos.index') }}" class="btn btn-outline">👥 Ver equipos</a>
                    <a href="{{ route('partidos.index') }}" class="btn btn-outline">📅 Ver partidos</a>
                </div>
            </div>
            <div>
                <div class="stats">
                    <div class="stat">
                        <div class="n">{{ $counts['torneos'] }}</div>
                        <div class="k">Torneos</div>
                    </div>
                    <div class="stat">
                        <div class="n">{{ $counts['equipos'] }}</div>
                        <div class="k">Equipos</div>
                    </div>
                    <div class="stat">
                        <div class="n">{{ $counts['partidos'] }}</div>
                        <div class="k">Partidos</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Accesos y enlaces -->
        <div class="grid">
            <div class="col-8">
                <div class="card">
                    <h3>Acciones rápidas</h3>
                    <div class="actions">
                        <a href="{{ route('admin.torneos.create') }}" class="btn btn-primary">➕ Nuevo torneo</a>
                        <a href="{{ route('torneos.index') }}" class="btn">⚙️ Gestionar torneos</a>
                        <a href="{{ route('equipos.index') }}" class="btn">👥 Gestionar equipos</a>
                        <a href="{{ route('partidos.index') }}" class="btn">📆 Gestionar partidos</a>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card">
                    <h3>Navegación</h3>
                    <div class="links">
                        <a class="link" href="{{ route('torneos.index') }}">• Listado de torneos</a>
                        <a class="link" href="{{ route('equipos.index') }}">• Listado de equipos</a>
                        <a class="link" href="{{ route('partidos.index') }}">• Listado de partidos</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
