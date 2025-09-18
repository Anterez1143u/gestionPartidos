<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // <-- import correcto
use App\Models\Equipo; // <-- import correcto
use App\Models\Partido;
use App\Models\Torneo;
use App\Models\Resultado;

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
                    ->where('equipo1_id', $userEquipoId) // <-- reemplaza local_id
                    ->orWhere('equipo2_id', $userEquipoId) // <-- reemplaza visitante_id
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

                        // crear partido y guardarlo (usa equipo1_id / equipo2_id)
                        $partido = Partido::create([
                            'torneo_id' => $torneo->id,
                            'fecha' => $matchDate,
                            'equipo1_id' => $a,   // <-- reemplaza local_id
                            'equipo2_id' => $b,   // <-- reemplaza visitante_id
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
                    'equipo1_id' => $a,   // <-- reemplaza local_id
                    'equipo2_id' => $b,   // <-- reemplaza visitante_id
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
        $today    = Carbon::today();

        // Columnas reales en la tabla (sin modificar DB)
        $cols = Schema::getColumnListing('partidos');
        $eq1Col  = in_array('equipo1_id',$cols,true) ? 'equipo1_id' :
                   (in_array('local_id',$cols,true) ? 'local_id' :
                   (in_array('id_equipo1',$cols,true) ? 'id_equipo1' : null));
        $eq2Col  = in_array('equipo2_id',$cols,true) ? 'equipo2_id' :
                   (in_array('visitante_id',$cols,true) ? 'visitante_id' :
                   (in_array('id_equipo2',$cols,true) ? 'id_equipo2' : null));
        $fechaCol = in_array('fecha',$cols,true) ? 'fecha' :
                    (in_array('fecha_partido',$cols,true) ? 'fecha_partido' :
                    (in_array('date',$cols,true) ? 'date' : null));

        if (!$eq1Col || !$eq2Col || !$fechaCol) {
            return response()->json([
                'error' => "La tabla partidos no tiene las columnas esperadas para equipos/fecha. Detectado: ".json_encode($cols)
            ], 422);
        }

        // Normalizar entradas (fechas no pasadas y resolución de nombres->IDs)
        $normalized = $matches->map(function ($m) use ($today) {
            $fecha = Carbon::parse($m['fecha_iso'] ?? $m['fecha'] ?? $today)->startOfDay();
            if ($fecha->lt($today)) $fecha = (clone $today);

            $a = $m['local']; $b = $m['visitante'];
            $aId = is_numeric($a) ? (int)$a : optional(Equipo::where('nombre', $a)->first())->id;
            $bId = is_numeric($b) ? (int)$b : optional(Equipo::where('nombre', $b)->first())->id;

            return [
                'fecha' => $fecha->toDateString(),
                'aId'   => $aId,
                'bId'   => $bId,
            ];
        })->filter(fn($m) => $m['aId'] && $m['bId']);

        // Quitar duplicados por (fecha, pareja sin orden)
        $unique = $normalized->unique(function ($m) {
            $a = min($m['aId'], $m['bId']);
            $b = max($m['aId'], $m['bId']);
            return $m['fecha']."|$a|$b";
        })->values();

        $saved = 0;
        DB::beginTransaction();
        try {
            foreach ($unique as $m) {
                // Existe en la misma fecha (sin importar localía)
                $exists = DB::table('partidos')
                    ->where('torneo_id', $torneoId)
                    ->whereDate($fechaCol, $m['fecha'])
                    ->where(function ($q) use ($eq1Col,$eq2Col,$m) {
                        $q->where(function ($w) use ($eq1Col,$eq2Col,$m) {
                            $w->where($eq1Col, $m['aId'])->where($eq2Col, $m['bId']);
                        })->orWhere(function ($w) use ($eq1Col,$eq2Col,$m) {
                            $w->where($eq1Col, $m['bId'])->where($eq2Col, $m['aId']);
                        });
                    })
                    ->exists();
                if ($exists) continue;

                // Insertar solo columnas existentes
                DB::table('partidos')->insert([
                    'torneo_id' => $torneoId,
                    $eq1Col     => $m['aId'],
                    $eq2Col     => $m['bId'],
                    $fechaCol   => $m['fecha'],
                    'created_at'=> now(),
                    'updated_at'=> now(),
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

    // Endpoint JSON: usa columnas reales (equipo1_id/equipo2_id, fecha, etc.)
    public function indexJson(Request $request)
    {
        $torneoId = (int) $request->input('torneo');
        if (!$torneoId) {
            return response()->json(['matches'=>[], 'type'=>'liga']);
        }

        $torneo = Torneo::find($torneoId);
        $type = ($torneo && ($torneo->fase_tipo ?? 'unica') === 'unica'
                 && strtolower((string)($torneo->formato_unica ?? 'liga')) === 'cuadro_eliminatorio')
                ? 'eliminatoria' : 'liga';

        $rows = DB::table('partidos as p')
            ->leftJoin('equipos as e1', 'p.equipo1_id', '=', 'e1.id')
            ->leftJoin('equipos as e2', 'p.equipo2_id', '=', 'e2.id')
            ->leftJoin('resultados as r', 'r.partido_id', '=', 'p.id')
            ->where('p.torneo_id', $torneoId)
            ->orderBy('p.fecha')->orderBy('p.hora')
            ->get([
                'p.id',
                'p.fecha',
                'p.hora',
                'p.cancha',
                'p.estado',
                'p.equipo1_id',
                'p.equipo2_id',
                DB::raw('e1.nombre as equipo1_nombre'),
                DB::raw('e2.nombre as equipo2_nombre'),
                'r.marcador_equipo1',
                'r.marcador_equipo2',
            ]);

        $matches = [];
        foreach ($rows as $row) {
            $hasScore = $row->marcador_equipo1 !== null && $row->marcador_equipo2 !== null;
            $estado = $row->estado ?: ($hasScore ? 'finalizado' : 'pendiente');

            $matches[] = [
                'id' => $row->id,
                'fecha_iso' => $row->fecha ? substr((string)$row->fecha, 0, 10) : null,
                'hora' => $row->hora,
                'cancha' => $row->cancha,
                'estado' => $estado,
                'local' => $row->equipo1_id ? ['id'=>$row->equipo1_id, 'nombre'=>$row->equipo1_nombre] : null,
                'visitante' => $row->equipo2_id ? ['id'=>$row->equipo2_id, 'nombre'=>$row->equipo2_nombre] : null,
                'local_nombre' => $row->equipo1_nombre,
                'visitante_nombre' => $row->equipo2_nombre,
                'local_score' => $row->marcador_equipo1,
                'visitante_score' => $row->marcador_equipo2,
                'group_index' => null,
                'round' => null,
            ];
        }

        return response()->json(['matches'=>$matches, 'type'=>$type]);
    }

    // Guardar/actualizar resultado (y sincronizar partidos.resultado_id y estado)
    public function setResult(Request $request, Partido $partido)
    {
        $data = $request->validate([
            'local_score' => ['required','integer','min:0'],
            'visitante_score' => ['required','integer','min:0'],
        ]);

        $a = (int)$data['local_score'];
        $b = (int)$data['visitante_score'];

        // Resultado actual (si existe)
        $res = Resultado::firstOrNew(['partido_id' => $partido->id]);

        // Si está finalizado, no permitir cambios diferentes
        $hasEstado = Schema::hasColumn('partidos', 'estado');
        if ($hasEstado && $partido->estado === 'finalizado' && $res->exists) {
            $pa = $res->marcador_equipo1;
            $pb = $res->marcador_equipo2;
            if ($pa !== null && $pb !== null && ($a !== (int)$pa || $b !== (int)$pb)) {
                return response()->json(['error' => 'Este partido ya está finalizado. No se puede modificar el marcador.'], 409);
            }
        }

        // Guardar/actualizar resultado
        $res->marcador_equipo1 = $a;
        $res->marcador_equipo2 = $b;
        $res->save();

        // Marcar finalizado
        if ($hasEstado) {
            $partido->estado = 'finalizado';
            $partido->save();
        }

        // Normalizar respuesta para la vista
        $local = null; $visit = null;
        if (method_exists($partido, 'equipo1')) $local = $partido->equipo1()->select('id','nombre')->first();
        if (method_exists($partido, 'equipo2')) $visit = $partido->equipo2()->select('id','nombre')->first();

        return response()->json([
            'ok' => true,
            'match' => [
                'id' => $partido->id,
                'fecha' => $partido->fecha,
                'hora' => $partido->hora,
                'estado' => $hasEstado ? $partido->estado : 'finalizado',
                'local_score' => $a,
                'visitante_score' => $b,
                'local' => $local,
                'visitante' => $visit,
            ],
        ]);
    }
}
