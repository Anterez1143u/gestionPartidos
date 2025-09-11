<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Equipo;
use App\Models\Partido;

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
        $request->validate([
            'torneo_id' => 'required|integer|exists:torneos,id',
            'matches'   => 'array'
        ]);

        $torneoId = (int) $request->input('torneo_id');
        $matches  = collect($request->input('matches', []));

        $today = Carbon::today();

        // Normalizar y filtrar duplicados (equipoA-equipoB en misma fecha)
        $normalized = $matches->map(function ($m) use ($today) {
            $fecha = Carbon::parse($m['fecha_iso'] ?? $m['fecha'] ?? $today)->startOfDay();
            if ($fecha->lt($today)) $fecha = (clone $today);

            $a = $m['local']; $b = $m['visitante'];
            // Intentar resolver a IDs si vienen nombres
            $aId = is_numeric($a) ? (int)$a : optional(Equipo::where('nombre', $a)->first())->id;
            $bId = is_numeric($b) ? (int)$b : optional(Equipo::where('nombre', $b)->first())->id;

            return [
                'fecha' => $fecha->toDateString(),
                'equipo1_id' => $aId,
                'equipo2_id' => $bId,
            ];
        })->filter(fn($m) => $m['equipo1_id'] && $m['equipo2_id']);

        // quitar duplicados por (fecha, minId, maxId)
        $unique = $normalized->unique(function ($m) {
            $a = min($m['equipo1_id'], $m['equipo2_id']);
            $b = max($m['equipo1_id'], $m['equipo2_id']);
            return $m['fecha'] . "|$a|$b";
        })->values();

        $saved = 0;
        DB::beginTransaction();
        try {
            foreach ($unique as $m) {
                // evitar insertar si ya existe en DB el mismo cruce en la misma fecha (en cualquier orden)
                $exists = Partido::where('torneo_id', $torneoId)
                    ->whereDate('fecha', $m['fecha'])
                    ->where(function ($q) use ($m) {
                        $q->where(function ($w) use ($m) {
                            $w->where('equipo1_id', $m['equipo1_id'])->where('equipo2_id', $m['equipo2_id']);
                        })->orWhere(function ($w) use ($m) {
                            $w->where('equipo1_id', $m['equipo2_id'])->where('equipo2_id', $m['equipo1_id']);
                        });
                    })->exists();

                if ($exists) continue;

                Partido::create([
                    'torneo_id'   => $torneoId,
                    'equipo1_id'  => $m['equipo1_id'],
                    'equipo2_id'  => $m['equipo2_id'],
                    'fecha'       => $m['fecha'],
                    'estado'      => 'programado',
                ]);
                $saved++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['saved' => $saved]);
    }

    // Vista pública (solo lectura)
    public function publicIndex(Request $request)
    {
        $torneos = Torneo::orderByDesc('id')->get();
        return view('public.partidos.index', compact('torneos'));
    }

    // Endpoint JSON: adapta los campos reales de la DB al formato usado por las vistas
    public function indexJson(Request $request)
    {
        $torneoId = $request->input('torneo');
        $type = 'liga'; // por defecto (no hay columna tipo en torneos en tus migraciones)

        $matches = [];
        if ($torneoId) {
            // join a equipos y resultados para obtener nombres y marcadores
            $rows = DB::table('partidos as p')
                ->leftJoin('equipos as e1', 'p.equipo1_id', '=', 'e1.id')
                ->leftJoin('equipos as e2', 'p.equipo2_id', '=', 'e2.id')
                ->leftJoin('resultados as r', 'r.partido_id', '=', 'p.id')
                ->where('p.torneo_id', $torneoId)
                ->orderBy(DB::raw('COALESCE(p.fecha, p.created_at)'))
                ->get([
                    'p.id', 'p.fecha', 'p.hora', 'p.cancha', 'p.grupo_id',
                    'p.equipo1_id', 'p.equipo2_id',
                    DB::raw('e1.nombre as equipo1_nombre'),
                    DB::raw('e2.nombre as equipo2_nombre'),
                    'r.marcador_equipo1', 'r.marcador_equipo2'
                ]);

            foreach ($rows as $row) {
                $fechaIso = $row->fecha ? substr((string)$row->fecha, 0, 10) : null;
                $hasScore = $row->marcador_equipo1 !== null && $row->marcador_equipo2 !== null;
                $matches[] = [
                    'id' => $row->id,
                    'fecha_iso' => $fechaIso,
                    'hora' => $row->hora,
                    'cancha' => $row->cancha,
                    'local' => [ 'id' => $row->equipo1_id, 'nombre' => $row->equipo1_nombre ],
                    'visitante' => [ 'id' => $row->equipo2_id, 'nombre' => $row->equipo2_nombre ],
                    'local_score' => $row->marcador_equipo1,
                    'visitante_score' => $row->marcador_equipo2,
                    'estado' => $hasScore ? 'finalizado' : 'pendiente',
                    'group_index' => null,
                    'round' => null,
                ];
            }
        }

        return response()->json([
            'matches' => $matches,
            'type' => $type,
        ]);
    }

    // Guardar/actualizar resultado en la tabla resultados (no altera la tabla partidos)
    public function setResult(Partido $partido, Request $request)
    {
        $data = $request->validate([
            'local_score' => ['nullable','integer','min:0'],
            'visitante_score' => ['nullable','integer','min:0'],
            'winner_mode' => ['nullable','string'], // no se persiste en DB; solo para UI
            'winner' => ['nullable','in:local,visitante'], // idem
        ]);

        // upsert en resultados
        $resultado = Resultado::firstOrNew(['partido_id' => $partido->id]);
        $resultado->marcador_equipo1 = $data['local_score'] ?? null;
        $resultado->marcador_equipo2 = $data['visitante_score'] ?? null;
        $resultado->save();

        // Normalizar respuesta como indexJson
        $e1 = Equipo::find($partido->equipo1_id);
        $e2 = Equipo::find($partido->equipo2_id);
        $fechaIso = $partido->fecha ? substr((string)$partido->fecha, 0, 10) : null;

        return response()->json([
            'match' => [
                'id' => $partido->id,
                'fecha_iso' => $fechaIso,
                'local' => [ 'id' => $partido->equipo1_id, 'nombre' => optional($e1)->nombre ],
                'visitante' => [ 'id' => $partido->equipo2_id, 'nombre' => optional($e2)->nombre ],
                'local_score' => $resultado->marcador_equipo1,
                'visitante_score' => $resultado->marcador_equipo2,
                'estado' => ($resultado->marcador_equipo1 !== null && $resultado->marcador_equipo2 !== null) ? 'finalizado' : 'pendiente',
                'group_index' => null,
                'round' => null,
            ]
        ]);
    }
}
