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

    private function jugadoresRecomendadosPorDeporte(?string $deporte): ?int
    {
        if (!$deporte) return null;
        $mapa = [
            'futboll' => 11,
            'voley' => 6,
            'baloncesto' => 5,
        ];
        return $mapa[$deporte] ?? null;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:255'],
            'torneo_id' => ['required','exists:torneos,id'],
            'jugadores' => ['required','array'],
            'jugadores.*' => ['required','string','max:255'],
        ]);

        $torneo = Torneo::find($data['torneo_id']);
        $recomendados = $this->jugadoresRecomendadosPorDeporte($torneo?->deporte);

        if ($torneo && $recomendados !== null && count($data['jugadores']) < $recomendados) {
            return back()
                ->withErrors(['jugadores' => "Debes registrar al menos {$recomendados} jugadores para {$torneo->deporte}."])
                ->withInput();
        }

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
            'nombre' => ['required','string','max:255'],
            'torneo_id' => ['required','exists:torneos,id'],
            'jugadores' => ['required','array'],
            'jugadores.*' => ['required','string','max:255'],
        ]);

        $torneo = Torneo::find($data['torneo_id']);
        $recomendados = $this->jugadoresRecomendadosPorDeporte($torneo?->deporte);

        if ($torneo && $recomendados !== null && count($data['jugadores']) < $recomendados) {
            return back()
                ->withErrors(['jugadores' => "Debes registrar al menos {$recomendados} jugadores para {$torneo->deporte}."])
                ->withInput();
        }

        $equipo->update([
            'nombre' => $data['nombre'],
            'jugadores' => $data['jugadores'],
            'torneo_id' => $data['torneo_id'],
            'categoria' => $data['categoria'] ?? null,
        ]);

        return redirect()->route('equipos.index')->with('success', 'Equipo actualizado correctamente');
    }

    public function destroy(Equipo $equipo)
    {
        try {
            // Si existen FK, esto puede fallar; captura y muestra el motivo
            $equipo->delete();

            return redirect()
                ->route('equipos.index')
                ->with('success', 'Equipo eliminado correctamente.');
        } catch (\Throwable $e) {
            return back()->withErrors('No se pudo eliminar el equipo: '.$e->getMessage());
        }
    }

    public function byTorneo(Request $request)
    {
        $torneoId = $request->integer('torneo_id');
        $equipos = Equipo::where('torneo_id', $torneoId)->select('id','nombre')->orderBy('nombre')->get();
        return response()->json(['equipos' => $equipos]);
    }
}
