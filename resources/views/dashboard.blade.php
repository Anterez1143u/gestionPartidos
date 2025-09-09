<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Panel de Administración') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <a href="{{ route('admin.torneos.create') }}" class="bg-green-600 text-white px-4 py-2 rounded font-bold">Crear Torneo</a>
                <a href="{{ route('torneos.index') }}" class="ml-4 text-blue-600 underline">Ver Torneos</a>
                <a href="{{ route('equipos.index') }}" class="ml-4 text-blue-600 underline">Ver Equipos</a>
                <a href="{{ route('partidos.index') }}" class="ml-4 text-blue-600 underline">Ver Partidos</a>
                <a href="{{ route('resultados.index') }}" class="ml-4 text-blue-600 underline">Ver Resultados</a>
            </div>
        </div>
    </div>
</x-app-layout>
