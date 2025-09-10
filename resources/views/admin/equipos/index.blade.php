<x-app-layout>
    <style>
        body {
            background: #1b2e47 !important;
        }
        .equipos-bg {
            background: #1b2e47;
            min-height: 100vh;
            padding-top: 40px;
        }
        .equipos-header {
            margin-bottom: 32px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }
        .equipos-header h1 {
            font-size: 2.1rem;
            font-weight: 700;
            color: #00c896;
            margin-bottom: 0;
        }
        .equipos-header .btn {
            font-weight: 600;
            border-radius: 8px;
            margin-right: 8px;
            padding: 10px 22px;
            box-shadow: 0 2px 8px rgba(0,200,150,0.08);
            border: none;
            transition: background .2s;
            font-size: 1rem;
        }
        .equipos-header .btn:last-child { margin-right: 0; }
        .equipos-header .btn-primary {
            background: #00c896;
            color: #fff;
        }
        .equipos-header .btn-primary:hover {
            background: #0d6efd;
        }
        .equipos-header .btn-accent {
            background: #232946;
            color: #fff;
        }
        .equipos-header .btn-accent:hover {
            background: #00c896;
        }
        .equipos-header .btn-groups {
            background: #0d6efd;
            color: #fff;
        }
        .equipos-header .btn-groups:hover {
            background: #00c896;
        }
        .equipos-table-card {
            background: #232946;
            border-radius: 14px;
            box-shadow: 0 8px 32px rgba(13,110,253,0.10);
            padding: 0;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        thead {
            background: #1b2e47;
        }
        th, td {
            padding: 14px 12px;
            font-size: 1rem;
        }
        th {
            color: #00c896;
            font-weight: 700;
            border-bottom: 2px solid #00c896;
            background: #232946;
        }
        td {
            color: #fff;
            background: #232946;
            border-bottom: 1px solid #1b2e47;
        }
        .equipos-actions a, .equipos-actions button {
            font-weight: 600;
            border-radius: 8px;
            padding: 7px 16px;
            font-size: 0.98rem;
            border: none;
            margin-right: 6px;
            transition: background .2s;
            display: inline-block;
        }
        .equipos-actions a:last-child, .equipos-actions button:last-child { margin-right: 0; }
        .equipos-actions .btn-edit {
            background: #fffbe6;
            color: #b8860b;
        }
        .equipos-actions .btn-edit:hover {
            background: #ffe58f;
        }
        .equipos-actions .btn-delete {
            background: #ffe6e6;
            color: #c00;
        }
        .equipos-actions .btn-delete:hover {
            background: #ffb3b3;
        }
        .equipos-empty {
            background: #232946;
            color: #fff;
            text-align: center;
            padding: 32px;
            border-radius: 14px;
            margin-bottom: 24px;
            box-shadow: 0 8px 32px rgba(13,110,253,0.10);
            font-size: 1.15rem;
        }
        .equipos-success {
            background: #00c896;
            color: #fff;
            text-align: center;
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 1.08rem;
            font-weight: 600;
        }
    </style>
    <div class="equipos-bg">
        <div class="max-w-6xl mx-auto px-3">
            <div class="equipos-header">
                <h1>Equipos</h1>
                <div>
                    @if($equipos->count() >= 4)
                        <a href="{{ route('grupos.generate') }}" class="btn btn-groups">
                            Generar grupos
                        </a>
                    @endif
                    <a href="{{ route('torneos.index') }}" class="btn btn-accent">Torneos</a>
                    <a href="{{ route('equipos.create') }}" class="btn btn-primary">Crear equipo</a>
                </div>
            </div>

            @if(session('success'))
                <div class="equipos-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($equipos->isEmpty())
                <div class="equipos-empty">
                    No hay equipos aún. Crea uno nuevo.
                </div>
            @else
                <div class="equipos-table-card">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Torneo</th>
                                <th>Categoría</th>
                                <th>Jugadores</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($equipos as $equipo)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $equipo->nombre }}</td>
                                    <td>
                                        {{ optional($equipo->torneo)->deporte ?? optional($equipo->torneo)->nombre ?? '—' }}
                                    </td>
                                    <td>{{ $equipo->categoria ?? '—' }}</td>
                                    <td>
                                        {{ is_array($equipo->jugadores) ? count($equipo->jugadores) : ($equipo->jugadores ? 1 : 0) }}
                                    </td>
                                    <td class="text-right equipos-actions">
                                        <a href="{{ route('equipos.edit', $equipo) }}" class="btn-edit">Editar</a>
                                        <form action="{{ route('equipos.destroy', $equipo) }}" method="POST" class="inline-block" style="display:inline;" onsubmit="return confirm('Eliminar equipo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>