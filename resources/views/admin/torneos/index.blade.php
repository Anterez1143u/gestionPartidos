<x-app-layout>
    <style>
        body {
            background: #1b2e47 !important;
        }
        .torneos-bg {
            background: #1b2e47;
            min-height: 100vh;
            padding-top: 40px;
        }
        .header {
            margin-bottom: 32px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }
        .header h1 {
            font-size: 2.3rem;
            font-weight: 700;
            color: #00c896;
            margin-bottom: 0;
        }
        .header .btn {
            font-weight: 600;
            border-radius: 8px;
            margin-right: 8px;
            padding: 10px 22px;
            box-shadow: 0 2px 8px rgba(0,200,150,0.08);
            border: none;
            transition: background .2s;
        }
        .header .btn:last-child { margin-right: 0; }
        .header .btn-primary {
            background: #00c896;
            color: #fff;
        }
        .header .btn-primary:hover {
            background: #0d6efd;
        }
        .header .btn-accent {
            background: #232946;
            color: #fff;
        }
        .tournament-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 28px;
            justify-items: center;
        }
        .tournament-card {
            background: #232946;
            color: #fff;
            max-width: 370px;
            width: 100%;
            padding: 28px 22px;
            box-shadow: 0 8px 32px rgba(13,110,253,0.10);
            border-left: 8px solid #00c896;
            border-radius: 14px;
            position: relative;
        }
        .tournament-card h2 {
            font-size: 1.35rem;
            color: #00c896;
            margin-bottom: 8px;
            text-align: center;
        }
        .tournament-card .meta {
            margin-bottom: 6px;
            text-align: center;
            font-size: 1rem;
        }
        .tournament-card .desc {
            margin-bottom: 12px;
            color: #e3e3e3;
            background: #1b2e47;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            font-size: 0.98rem;
        }
        .tournament-card .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .tournament-card .btn {
            font-weight: 600;
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 0.98rem;
            border: none;
            transition: background .2s;
        }
        .tournament-card .btn-view {
            background: #00c896;
            color: #fff;
        }
        .tournament-card .btn-view:hover {
            background: #0d6efd;
        }
        .tournament-card .btn-edit {
            background: #fffbe6;
            color: #b8860b;
        }
        .tournament-card .btn-edit:hover {
            background: #ffe58f;
        }
        .tournament-card .btn-delete {
            background: #ffe6e6;
            color: #c00;
        }
        .tournament-card .btn-delete:hover {
            background: #ffb3b3;
        }
        .card-empty {
            background: #232946;
            color: #fff;
            text-align: center;
            padding: 32px;
            border-radius: 14px;
            margin-bottom: 24px;
            box-shadow: 0 8px 32px rgba(13,110,253,0.10);
        }
        .card-empty span {
            font-size: 2.2rem;
            display: block;
            margin-bottom: 12px;
        }
    </style>
    <div class="torneos-bg">
        <div class="container">
            <div class="header">
                <h1>Torneos</h1>
                <div>
                    <a href="{{ route('equipos.index') }}" class="btn btn-accent">
                        ⚽ Equipos
                    </a>
                    <a href="{{ route('torneos.create') }}" class="btn btn-primary">
                        ➕ Crear torneo
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="card-empty" style="background:#00c896;color:#fff;">
                    {{ session('success') }}
                </div>
            @endif

            @if($torneos->isEmpty())
                <div class="card-empty">
                    <span>😕</span>
                    <div>No hay torneos creados aún.<br>Crea uno nuevo para comenzar.</div>
                </div>
            @else
                <div class="tournament-list">
                    @foreach($torneos as $torneo)
                        <div class="tournament-card">
                            <div style="position:absolute;top:18px;right:18px;font-size:1.3rem;color:#00c896;">
                                🏆
                            </div>
                            <h2>{{ ucfirst($torneo->deporte ?? '—') }}</h2>
                            <div class="meta">
                                👥 <strong>{{ $torneo->numero_participantes ?? '—' }}</strong> participantes
                            </div>
                            <div class="meta">
                                🏁 Fase: <strong>{{ $torneo->fase ?? '—' }}</strong>
                            </div>
                            <div class="meta">
                                🎯 Formato: 
                                @if(($torneo->fase ?? '') === 'unica')
                                    <span>{{ $torneo->formato_fase_unica ?? '—' }}</span>
                                @else
                                    <span>{{ $torneo->formato_fase_1 ? $torneo->formato_fase_1 . ' / ' : '' }}{{ $torneo->formato_fase_2 ?? '—' }}</span>
                                @endif
                            </div>
                            <div class="meta">
                                📅 Fechas: 
                                <span>{{ optional($torneo)->fecha_inicio ? \Carbon\Carbon::parse($torneo->fecha_inicio)->format('Y-m-d') : '—' }}</span>
                                @if(!empty($torneo->fecha_fin))
                                    — <span>{{ \Carbon\Carbon::parse($torneo->fecha_fin)->format('Y-m-d') }}</span>
                                @endif
                            </div>
                            <div class="meta">
                                🕒 Creado: <span>{{ optional($torneo->created_at)->format('Y-m-d') ?? '—' }}</span>
                            </div>
                            <div class="desc">
                                <strong>Descripción:</strong> {{ $torneo->descripcion ?? '—' }}
                            </div>
                            <div class="actions">
                                <a href="{{ route('torneos.show', $torneo) }}" class="btn btn-view">
                                    👁 Ver
                                </a>
                                <a href="{{ route('torneos.edit', $torneo) }}" class="btn btn-edit">
                                    ✏️ Editar
                                </a>
                                <form action="{{ route('torneos.destroy', $torneo) }}" method="POST" class="inline-block" style="display:inline;" onsubmit="return confirm('¿Eliminar torneo?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-delete">
                                        🗑 Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if(method_exists($torneos, 'links'))
                    <div class="card-empty" style="background:#232946;color:#fff;margin-top:18px;">
                        {{ $torneos->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>