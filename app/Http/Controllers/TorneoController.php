<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Torneo;
use App\Models\Equipo;
use App\Models\Partido;
use Illuminate\Validation\Rule;

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

    public function show($id)
    {
        $torneo = Torneo::findOrFail($id);
        return view('admin.torneos.show', compact('torneo'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'deporte' => ['required', Rule::in(['futboll','voley','baloncesto'])],
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

    public function update(Request $request, \App\Models\Torneo $torneo)
    {
        $data = $request->validate([
            'deporte' => ['required', Rule::in(['futboll','voley','baloncesto'])],
            'num_participantes' => 'required|integer|min:2|max:256',
            'fase_tipo' => 'required|in:unica,multifase',
            'formato_unica' => 'nullable|string|max:50',
            'formato_primera' => 'nullable|string|max:50',
            'formato_segunda' => 'nullable|string|max:50',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'descripcion' => 'nullable|string|max:1000',
        ], [
            'deporte.required' => 'El deporte es obligatorio.',
            'deporte.in'       => 'Solo se permite Fútbol, Baloncesto o Vóley.',
            'num_participantes.required' => 'Indica el número de participantes.',
            'num_participantes.min' => 'Debe haber al menos 2 participantes.',
            'fase_tipo.required' => 'Selecciona un formato.',
            'fecha_fin.after_or_equal' => 'La fecha fin no puede ser anterior a la fecha inicio.',
        ]);

        try {
            // Mapear a columnas reales
            $torneo->deporte = $data['deporte'];
            $torneo->numero_participantes = $data['num_participantes'];
            $torneo->fase = $data['fase_tipo'];
            $torneo->formato_fase_unica = $data['formato_unica'] ?? null;
            $torneo->formato_fase_1 = $data['formato_primera'] ?? null;
            $torneo->formato_fase_2 = $data['formato_segunda'] ?? null;
            $torneo->fecha_inicio = $data['fecha_inicio'] ?? null;
            $torneo->fecha_fin = $data['fecha_fin'] ?? null;
            $torneo->descripcion = $data['descripcion'] ?? null;

            $torneo->save();

            // Redirigir al índice con mensaje
            return redirect()->route('torneos.index')->with('success', 'Torneo actualizado correctamente.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'No se pudo actualizar: '.$e->getMessage());
        }
    }

    public function destroy(Torneo $torneo)
    {
        try {
            $equipos  = Equipo::where('torneo_id', $torneo->id)->count();
            $partidos = Partido::where('torneo_id', $torneo->id)->count();

            if ($equipos > 0 || $partidos > 0) {
                return back()->with('error', "No se puede eliminar. Tiene $equipos equipo(s) y $partidos partido(s) asociados.");
            }

            $torneo->delete();
            return redirect()->route('torneos.index')->with('success', 'Torneo eliminado.');
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo eliminar: '.$e->getMessage());
        }
    }
}
