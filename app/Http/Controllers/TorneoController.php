<?php

namespace App\Http\Controllers;

use App\Models\Torneo;
use Illuminate\Http\Request;

class TorneoController extends Controller
{
    public function index()
    {
        $torneos = Torneo::all();
        return view('admin.torneos.index', compact('torneos'));
    }

    public function create()
    {
        return view('admin.torneos.create');
    }

    public function edit($id)
    {
        $torneo = Torneo::findOrFail($id);
        return view('admin.torneos.edit', compact('torneo'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'deporte' => 'required|string',
            'num_participantes' => 'required|integer|min:2',
            'fase_tipo' => 'required|in:unica,multifase',
            'formato_unica' => 'required_if:fase_tipo,unica',
            'formato_primera' => 'required_if:fase_tipo,multifase',
            'formato_segunda' => 'required_if:fase_tipo,multifase',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'descripcion' => 'nullable|string',
        ]);

        $payload = [
            'deporte' => $data['deporte'],
            'numero_participantes' => $data['num_participantes'],
            'fase' => $data['fase_tipo'],
            'formato_fase_unica' => $data['formato_unica'] ?? null,
            'formato_fase_1' => $data['formato_primera'] ?? null,
            'formato_fase_2' => $data['formato_segunda'] ?? null,
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'descripcion' => $data['descripcion'] ?? null,
        ];

        Torneo::create($payload);

        // redirige a la vista de equipos
        return redirect()->route('equipos.index')->with('success', 'Torneo creado correctamente');
    }
}
