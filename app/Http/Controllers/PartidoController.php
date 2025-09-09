<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partido;
use App\Models\Equipo;
use App\Models\Grupo;
use App\Models\Torneo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PartidoController extends Controller
{
    public function index()
    {
        // Si es admin, ve todos; si no, solo los partidos del equipo del usuario (si existe)
        if (Auth::check() && Auth::user()->rol === 'admin') {
            $partidos = Partido::with(['local', 'visitante', 'torneo'])->get();
        } else {
            $partidos = collect();
            if (Auth::check() && isset(Auth::user()->equipo_id)) {
                $userEquipoId = Auth::user()->equipo_id;
                $partidos = Partido::with(['local', 'visitante', 'torneo'])
                    ->where('local_id', $userEquipoId)
                    ->orWhere('visitante_id', $userEquipoId)
                    ->get();
            }
        }

        return view('admin.partidos.index', compact('partidos'));
    }

    public function show($id)
    {
        $partido = Partido::findOrFail($id);
        return view('partidos.show', compact('partido'));
    }

    public function showGenerate()
    {
        return view('admin.partidos.generate_calendar');
    }

    /**
     * Genera partidos automáticamente y los guarda en BD.
     * - soporta "liga" (round-robin) y "eliminatoria" (bracket) según torneo.
     * - permite programar fechas según opción: entre_semana / fines_semana / todo
     */
    public function generateCalendar(Request $request)
    {
        $data = $request->validate([
            'torneo_id' => 'required|exists:torneos,id',
            'fecha_inicio' => 'nullable|date',
            'dias_entre' => 'nullable|integer|min:1',
            'tipo_horario' => 'nullable|in:entre_semana,fines_semana,todo',
            'groups' => 'nullable|array',
        ]);

        $torneo = Torneo::findOrFail($data['torneo_id']);

        // Elegir fecha de inicio: prioridad -> input del formulario -> fecha_inicio del torneo -> hoy
        if (!empty($data['fecha_inicio'])) {
            $start = Carbon::parse($data['fecha_inicio']);
        } elseif (!empty($torneo->fecha_inicio)) {
            $start = Carbon::parse($torneo->fecha_inicio);
        } else {
            $start = Carbon::now();
        }

        $diasEntre = $data['dias_entre'] ?? 7;
        $tipoHorario = $data['tipo_horario'] ?? 'todo';

        $nextAllowedDate = function (Carbon $date) use ($tipoHorario) {
            $d = $date->copy();
            while (true) {
                $weekday = $d->dayOfWeek; // 0 Sun .. 6 Sat
                if ($tipoHorario === 'fines_semana') {
                    if (in_array($weekday, [0,6])) break;
                } elseif ($tipoHorario === 'entre_semana') {
                    if (!in_array($weekday, [0,6])) break;
                } else {
                    break;
                }
                $d->addDay();
            }
            return $d;
        };

        $matchesOutput = [];
        $createdIds = [];

        // Si se enviaron grupos, programamos partidos dentro de cada grupo (round-robin)
        if (!empty($data['groups']) && is_array($data['groups'])) {
            $groups = $data['groups'];
            $currentDate = $nextAllowedDate($start);

            foreach ($groups as $gIndex => $group) {
                // normalizar miembros a IDs si vienen por nombre/int
                $members = [];
                foreach ($group as $member) {
                    if (is_numeric($member)) {
                        $members[] = (int)$member;
                    } else {
                        // buscar por nombre
                        $eq = Equipo::where('nombre', $member)->where('torneo_id', $torneo->id)->first();
                        if ($eq) $members[] = $eq->id;
                    }
                }

                // round-robin para este grupo
                $ids = array_values($members);
                $n = count($ids);
                if ($n < 2) continue;

                // si impar, añadir bye (null)
                $hasBye = false;
                if ($n % 2 === 1) {
                    $ids[] = null;
                    $n++;
                    $hasBye = true;
                }
                $rounds = $n - 1;
                $half = $n / 2;

                // por cada ronda
                for ($r = 0; $r < $rounds; $r++) {
                    for ($i = 0; $i < $half; $i++) {
                        $a = $ids[$i];
                        $b = $ids[$n - 1 - $i];
                        if ($a === null || $b === null) continue; // bye
                        $matchDate = $currentDate->copy();

                        // crear partido y guardarlo
                        $partido = Partido::create([
                            'torneo_id' => $torneo->id,
                            'fecha' => $matchDate,
                            'local_id' => $a,
                            'visitante_id' => $b,
                            'estado' => 'pendiente',
                        ]);
                        $createdIds[] = $partido->id;

                        $matchesOutput[] = [
                            'fecha' => $matchDate->toDateString(),
                            'local' => Equipo::find($a)->nombre ?? ('Equipo #' . $a),
                            'visitante' => Equipo::find($b)->nombre ?? ('Equipo #' . $b),
                            'group_index' => $gIndex,
                        ];
                    }

                    // rotación para next round (circle method)
                    $fixed = array_shift($ids);
                    $last = array_pop($ids);
                    array_unshift($ids, $fixed);
                    array_push($ids, $last);

                    // avanzar fecha para próxima ronda (se respeta tipoHorario)
                    $currentDate = $nextAllowedDate($currentDate->copy()->addDays($diasEntre));
                }

                // al terminar un grupo, opcionalmente se puede desplazar la fecha de inicio del siguiente grupo
                // aquí mantenemos la continuidad (no resetearemos la fecha)
            }

            // agrupar conteos
            $counts = [];
            foreach ($matchesOutput as $m) {
                $counts[$m['fecha']] = ($counts[$m['fecha']] ?? 0) + 1;
            }
            ksort($counts);

            return response()->json([
                'status' => 'ok',
                'type' => 'grupos',
                'matches' => $matchesOutput,
                'counts' => $counts,
                'created_ids' => $createdIds,
            ]);
        }

        // Si no hay grupos, fallback: generar como antes usando todos los equipos del torneo
        $equipos = Equipo::where('torneo_id', $torneo->id)->pluck('nombre', 'id')->toArray();
        $teamIds = array_keys($equipos);
        if (count($teamIds) < 2) {
            return response()->json(['error' => 'Se requieren al menos 2 equipos para generar partidos.'], 422);
        }

        // implementación previa (liga simple) - conservar o adaptar según sea necesario
        $n = count($teamIds);
        $ids = $teamIds;
        if ($n % 2 === 1) {
            $ids[] = null; $n++; // bye
        }
        $rounds = $n - 1;
        $half = $n / 2;
        $roundDate = $nextAllowedDate($start);

        for ($r = 0; $r < $rounds; $r++) {
            for ($i = 0; $i < $half; $i++) {
                $a = $ids[$i];
                $b = $ids[$n - 1 - $i];
                if ($a === null || $b === null) continue;
                $matchDate = $roundDate->copy();
                $partido = Partido::create([
                    'torneo_id' => $torneo->id,
                    'fecha' => $matchDate,
                    'local_id' => $a,
                    'visitante_id' => $b,
                    'estado' => 'pendiente',
                ]);
                $createdIds[] = $partido->id;
                $matchesOutput[] = [
                    'fecha' => $matchDate->toDateString(),
                    'local' => $equipos[$a] ?? 'Equipo #' . $a,
                    'visitante' => $equipos[$b] ?? 'Equipo #' . $b,
                ];
            }
            // rota
            $fixed = array_shift($ids);
            $last = array_pop($ids);
            array_unshift($ids, $fixed);
            array_push($ids, $last);
            $roundDate = $nextAllowedDate($roundDate->copy()->addDays($diasEntre));
        }

        $counts = [];
        foreach ($matchesOutput as $m) {
            $counts[$m['fecha']] = ($counts[$m['fecha']] ?? 0) + 1;
        }
        ksort($counts);

        return response()->json([
            'status' => 'ok',
            'type' => 'liga',
            'matches' => $matchesOutput,
            'counts' => $counts,
            'created_ids' => $createdIds,
        ]);
    }

    /**
     * Guardar un calendario generado (recibe JSON de matches).
     */
    public function saveSchedule(Request $request)
    {
        $payload = $request->validate([
            'torneo_id' => 'required|integer|exists:torneos,id',
            'matches' => 'required|array',
            'matches.*.local' => 'required',
            'matches.*.visitante' => 'required',
            'matches.*.fecha_iso' => 'nullable|date',
            'matches.*.group_index' => 'nullable|integer',
        ]);

        $torneoId = $payload['torneo_id'];
        $matches = $payload['matches'];

        DB::beginTransaction();
        try {
            $saved = [];
            foreach ($matches as $m) {
                // resolver o crear equipos (por id o por nombre)
                $resolveEquipo = function ($raw) use ($torneoId) {
                    if (is_numeric($raw)) {
                        return Equipo::find($raw);
                    }
                    return Equipo::firstOrCreate(
                        ['nombre' => (string)$raw, 'torneo_id' => $torneoId],
                        ['nombre' => (string)$raw, 'torneo_id' => $torneoId]
                    );
                };

                $equipo1 = $resolveEquipo($m['local']);
                $equipo2 = $resolveEquipo($m['visitante']);

                // grupo si aplica
                $grupoId = null;
                if (isset($m['group_index']) && $m['group_index'] !== null) {
                    // intentar encontrar grupo por nombre/índice en el torneo
                    $groupIndex = (int)$m['group_index'];
                    $grupo = Grupo::where('torneo_id', $torneoId)->skip($groupIndex)->first();
                    if ($grupo) $grupoId = $grupo->id;
                }

                // crear partido (si ya existe podemos actualizar por torneo+equipos+fecha)
                $partido = new Partido();
                $partido->equipo1_id = $equipo1 ? $equipo1->id : null;
                $partido->equipo2_id = $equipo2 ? $equipo2->id : null;
                $partido->torneo_id = $torneoId;
                $partido->grupo_id = $grupoId;
                $partido->fecha = $m['fecha_iso'] ?? null;
                $partido->hora = $m['hora'] ?? null;
                $partido->cancha = $m['cancha'] ?? null;
                $partido->save();

                $saved[] = $partido->load(['equipo1', 'equipo2', 'grupo']);
            }

            DB::commit();
            return response()->json(['saved' => count($saved), 'partidos' => $saved], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
