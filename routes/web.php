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

// Rutas públicas (no requieren login) — solo lectura
Route::get('/torneos', function () {
    $torneos = \App\Models\Torneo::orderByDesc('created_at')->paginate(20);
    return view('public.torneos.index', compact('torneos'));
})->name('public.torneos');

Route::get('/equipos', function () {
    $equipos = \App\Models\Equipo::with('torneo:id,deporte,nombre')
        ->orderByDesc('created_at')->paginate(24);
    return view('public.equipos.index', compact('equipos'));
})->name('public.equipos');

Route::get('/partidos', function () {
    $torneoId = request('torneo');
    $estado = request('estado'); // pendiente|en_curso|finalizado|programado|todos
    $q = \App\Models\Partido::with(['equipo1:id,nombre', 'equipo2:id,nombre', 'torneo:id,deporte,nombre'])
        ->when($torneoId, fn($qq)=>$qq->where('torneo_id', $torneoId))
        ->when($estado && $estado !== 'todos', fn($qq)=>$qq->where('estado', $estado))
        ->orderBy('fecha')->orderBy('hora');

    $partidos = $q->paginate(20)->withQueryString();
    $torneos = \App\Models\Torneo::orderByDesc('created_at')->get(['id','deporte','nombre']);
    return view('public.partidos.index', compact('partidos','torneos','torneoId','estado'));
})->name('public.partidos');

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
    Route::get('/admin/torneos/edit', [TorneoController::class, 'edit'])->name('admin.torneos.edit');
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
    // Endpoint JSON dedicado para la vista de partidos
    Route::get('/admin/partidos-json', [PartidoController::class, 'indexJson'])->name('partidos.index.json');
    // Guardar resultado de un partido (usado por la vista admin)
    Route::post('/admin/partidos/{partido}/set-result', [PartidoController::class, 'setResult'])->name('partidos.setResult');
});

// Rutas públicas: ver partidos y JSON sin login (solo lectura)
Route::get('/ver-partidos', [PartidoController::class, 'publicIndex'])->name('public.partidos');
Route::get('/partidos-json', [PartidoController::class, 'indexJson'])->name('public.partidos.json');

// Solo para participantes autenticados
Route::middleware(['auth'])->group(function () {
    Route::get('/participante/dashboard', function () {
        return view('participante.dashboard');
    })->name('participante.dashboard');
});

Route::get('/resultados', [ResultadoController::class, 'index'])->name('resultados.index');

require __DIR__.'/auth.php';
