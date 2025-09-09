{{-- resources/views/participante/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Mi Torneo') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-bold mb-2">Mis Partidos</h3>
                <a href="{{ route('partidos.index') }}" class="text-blue-600 underline">Ver mis partidos</a>
                <h3 class="font-bold mt-6 mb-2">Tabla de posiciones</h3>
                <a href="{{ route('torneos.index') }}" class="text-blue-600 underline">Ver tabla</a>
            </div>
        </div>
    </div>
</x-app-layout>