<x-app-layout>
    <style>
        body {
            background: #1b2e47 !important;
        }
        .show-bg {
            background: #1b2e47;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .tournament-card {
            background: #232946;
            color: #fff;
            max-width: 420px;
            width: 100%;
            margin: auto;
            padding: 32px 28px;
            box-shadow: 0 8px 32px rgba(13,110,253,0.10);
            border-left: 8px solid #00c896;
            border-radius: 14px;
        }
        .tournament-card h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #00c896;
            margin-bottom: 18px;
            text-align: center;
        }
        .tournament-card .meta {
            margin-bottom: 10px;
            text-align: center;
            font-size: 1rem;
        }
        .tournament-card .desc {
            margin-top: 18px;
            background: #1b2e47;
            color: #e3e3e3;
            padding: 14px;
            border-radius: 8px;
            text-align: center;
            font-size: 0.98rem;
        }
        .tournament-card .actions {
            margin-top: 28px;
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .tournament-card .btn {
            font-weight: 600;
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 0.98rem;
            border: none;
            transition: background .2s;
            background: #00c896;
            color: #fff;
        }
        .tournament-card .btn:hover {
            background: #0d6efd;
            color: #fff;
        }
        .tournament-card .btn-edit {
            background: #fffbe6;
            color: #b8860b;
        }
        .tournament-card .btn-edit:hover {
            background: #ffe58f;
            color: #b8860b;
        }
    </style>
    <div class="show-bg">
        <div class="tournament-card">
            <h2>{{ ucfirst($torneo->deporte ?? '—') }}</h2>
            <div class="meta">👥 <strong>{{ $torneo->numero_participantes ?? '—' }}</strong> participantes</div>
            <div class="meta">🏁 Fase: <strong>{{ $torneo->fase ?? '—' }}</strong></div>
            <div class="meta">🎯 Formato única: <span>{{ $torneo->formato_fase_unica ?? '—' }}</span></div>
            <div class="meta">🔄 Formato 1ª fase: <span>{{ $torneo->formato_fase_1 ?? '—' }}</span></div>
            <div class="meta">🔄 Formato 2ª fase: <span>{{ $torneo->formato_fase_2 ?? '—' }}</span></div>
            <div class="meta">📅 Inicio: <span>{{ $torneo->fecha_inicio ? \Carbon\Carbon::parse($torneo->fecha_inicio)->format('Y-m-d') : '—' }}</span></div>
            <div class="meta">📅 Fin: <span>{{ $torneo->fecha_fin ? \Carbon\Carbon::parse($torneo->fecha_fin)->format('Y-m-d') : '—' }}</span></div>
            <div class="meta">🕒 Creado: <span>{{ $torneo->created_at ? $torneo->created_at->format('Y-m-d') : '—' }}</span></div>
            <div class="desc">
                <strong>Descripción:</strong> {{ $torneo->descripcion ?? '—' }}
            </div>
            <div class="actions">
                <a href="{{ route('torneos.index') }}" class="btn">Volver</a>
                <a href="{{ route('torneos.edit', $torneo) }}" class="btn btn-edit">Editar</a>
            </div>
        </div>
    </div>
</x-app-layout>