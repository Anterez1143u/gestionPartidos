<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Torneo;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    public function index()
    {
        $equipos = Equipo::all();
        return view('admin.equipos.index', compact('equipos'));
    }

    public function create()
    {
        $torneos = Torneo::all();
        return view('admin.equipos.create', compact('torneos'));
    }

    public function edit(Equipo $equipo)
    {
        $torneos = Torneo::all();
        return view('admin.equipos.create', compact('torneos', 'equipo'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'jugadores' => 'required|array|min:1',
            'jugadores.*' => 'nullable|string|max:255',
            'torneo_id' => 'required|exists:torneos,id',
            'categoria' => 'nullable|string|max:100',
        ]);

        Equipo::create([
            'nombre' => $data['nombre'],
            'jugadores' => $data['jugadores'], // se casteará a JSON automáticamente
            'torneo_id' => $data['torneo_id'],
            'categoria' => $data['categoria'] ?? null,
        ]);

        return redirect()->route('equipos.index')->with('success', 'Equipo registrado correctamente');
    }

    public function update(Request $request, Equipo $equipo)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'jugadores' => 'required|array|min:1',
            'jugadores.*' => 'nullable|string|max:255',
            'torneo_id' => 'required|exists:torneos,id',
            'categoria' => 'nullable|string|max:100',
        ]);

        $equipo->update([
            'nombre' => $data['nombre'],
            'jugadores' => $data['jugadores'],
            'torneo_id' => $data['torneo_id'],
            'categoria' => $data['categoria'] ?? null,
        ]);

        return redirect()->route('equipos.index')->with('success', 'Equipo actualizado correctamente');
    }
}
