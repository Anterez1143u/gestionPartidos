<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipo;
use App\Models\Torneo;
use Illuminate\Support\Facades\Session;

class GrupoController extends Controller
{
    public function showGenerate(Request $request)
    {
        $torneos = Torneo::all();

        // valores precargados: first -> query param -> primer torneo disponible -> null
        $selected = $request->old('torneo_id') ?? $request->get('torneo_id') ?? ($torneos->first()->id ?? null);
        $tipo = $request->old('tipo') ?? $request->get('tipo') ?? 'grupos';
        $equipos_por_grupo = $request->old('equipos_por_grupo') ?? $request->get('equipos_por_grupo') ?? 4;

        // Si en sesión hay grupos y pertenecen al torneo seleccionado, úsalos
        $sessionTorneo = Session::get('generated_groups_torneo');
        $groups = null;
        if ($sessionTorneo && $selected && (int)$sessionTorneo === (int)$selected) {
            $groups = Session::get('generated_groups', []);
        } else {
            // intentar precargar grupos automáticamente si hay equipos
            if ($selected) {
                $equipos = Equipo::where('torneo_id', $selected)->pluck('nombre')->toArray();
                if (!empty($equipos)) {
                    shuffle($equipos);
                    $size = max(2, (int)$equipos_por_grupo);
                    $groups = array_chunk($equipos, $size);
                }
            }
        }

        return view('admin.grupos.generate', compact('torneos', 'selected', 'tipo', 'equipos_por_grupo', 'groups'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'torneo_id' => 'required|exists:torneos,id',
            'equipos_por_grupo' => 'nullable|integer|min:2',
            'tipo' => 'nullable|in:grupos,llaves',
        ]);

        // obtener id y nombre para poder enviar ambos al cliente
        $equipos = Equipo::where('torneo_id', $request->torneo_id)->get(['id', 'nombre'])->toArray();
        shuffle($equipos);
        $size = max(2, (int)($request->equipos_por_grupo ?: 4));

        // groups será array de arrays con items {id,nombre}
        $groups = array_chunk($equipos, $size);

        // Guardar en sesión para persistir tras recarga
        Session::put('generated_groups', $groups);
        Session::put('generated_groups_torneo', (int)$request->torneo_id);

        return response()->json(['groups' => $groups]);
    }
}
