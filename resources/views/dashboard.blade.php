<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight" style="letter-spacing:1px;">
            {{ __('Panel de Administración') }}
        </h2>
    </x-slot>

    <style>
        body {
            background: #1b2e47 !important;
        }
        nav, .navbar, .bg-white {
            background: #232946 !important;
            color: #fff !important;
        }
        .dashboard-card {
            background: #232946;
            color: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(13,110,253,0.10);
            padding: 32px 28px;
        }
        .dashboard-card a {
            color: #00c896;
            font-weight: 600;
            text-decoration: none;
            margin-right: 18px;
            transition: color .2s;
        }
        .dashboard-card a:hover {
            color: #0d6efd;
            text-decoration: underline;
        }
        .dashboard-btn {
            background: #00c896;
            color: #fff;
            font-weight: 700;
            border-radius: 8px;
            padding: 12px 28px;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px rgba(0,200,150,0.08);
            border: none;
            margin-bottom: 18px;
            transition: background .2s;
        }
        .dashboard-btn:hover {
            background: #0d6efd;
        }
    </style>

    <div class="py-12" style="background:#f3f6fa;min-height:70vh;">
        <div class="max-w-2xl mx-auto px-3">
            <div class="dashboard-card text-center">
                <button onclick="location.href='{{ route('admin.torneos.create') }}'" class="dashboard-btn mb-3">
                    🏆 Crear Torneo
                </button>
                <div>
                    <a href="{{ route('torneos.index') }}">Ver Torneos</a>
                    <a href="{{ route('equipos.index') }}">Ver Equipos</a>
                    <a href="{{ route('partidos.index') }}">Ver Partidos</a>
                    <a href="{{ route('resultados.index') }}">Ver Resultados</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
