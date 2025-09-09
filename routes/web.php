<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TorneoController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\PartidoController;
use App\Http\Controllers\ResultadoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// usar la clase del middleware directamente (no requiere alias en Kernel)
Route::middleware(['auth', \App\Http\Middleware\CheckRole::class . ':admin'])->group(function () {
    Route::get('/admin/torneos/crear', [TorneoController::class, 'create'])->name('admin.torneos.create');
    Route::post('/admin/torneos', [TorneoController::class, 'store'])->name('admin.torneos.store');
    Route::resource('torneos', TorneoController::class);
    Route::resource('equipos', EquipoController::class);
    Route::resource('grupos', GrupoController::class);
    Route::resource('partidos', PartidoController::class);
    Route::resource('resultados', ResultadoController::class);
    Route::get('/admin/grupos/generate', [GrupoController::class, 'showGenerate'])->name('grupos.generate');
    Route::post('/admin/grupos/generate', [GrupoController::class, 'generate'])->name('grupos.generate.run');
    Route::get('/admin/partidos/generate', [PartidoController::class, 'showGenerate'])->name('partidos.generate');
    Route::post('/admin/partidos/generate-calendar', [PartidoController::class, 'generateCalendar'])->name('partidos.generateCalendar');
    Route::post('/admin/partidos/save-schedule', [\App\Http\Controllers\PartidoController::class, 'saveSchedule'])->name('partidos.saveSchedule');
    // ruta para ver partidos (asegúrate que exista PartidoController@index)
    Route::get('/admin/partidos', [PartidoController::class, 'index'])->name('partidos.index');
});

// Solo para participantes autenticados
Route::middleware(['auth'])->group(function () {
    Route::get('/participante/dashboard', function () {
        return view('participante.dashboard');
    })->name('participante.dashboard');
});

require __DIR__.'/auth.php';
