<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // EQUIPOS: asegurar nombre
        if (Schema::hasTable('equipos')) {
            Schema::table('equipos', function (Blueprint $table) {
                if (!Schema::hasColumn('equipos', 'nombre')) {
                    $table->string('nombre')->nullable();
                }
            });
        }

        // TORNEOS: deporte + fase única + formato_unica
        if (Schema::hasTable('torneos')) {
            Schema::table('torneos', function (Blueprint $table) {
                if (!Schema::hasColumn('torneos', 'deporte')) {
                    $table->string('deporte')->default('futboll')->index();
                }
                if (!Schema::hasColumn('torneos', 'fase_tipo')) {
                    $table->string('fase_tipo')->default('unica');
                }
                if (!Schema::hasColumn('torneos', 'formato_unica')) {
                    $table->string('formato_unica')->default('liga');
                }
            });

            // Normalizar valores existentes (sin romper datos)
            try {
                // set fase única cuando esté null
                DB::table('torneos')->whereNull('fase_tipo')->update(['fase_tipo' => 'unica']);
                // migrar posibles valores “todos contra todos” a “liga”
                DB::table('torneos')->where('formato_unica', 'todos_contra_todos')->update(['formato_unica' => 'liga']);
                // limitar deportes fuera de catálogo a futboll
                DB::table('torneos')->whereNotIn('deporte', ['futboll','voley','baloncesto'])->update(['deporte' => 'futboll']);
            } catch (\Throwable $e) {}
            // CHECK opcional (si tu motor lo soporta)
            try {
                DB::statement("ALTER TABLE torneos ADD CONSTRAINT chk_torneos_deporte CHECK (deporte IN ('futboll','voley','baloncesto'))");
            } catch (\Throwable $e) {}
        }

        // PARTIDOS: ids de equipos, fecha, hora, cancha, estado, FKs
        if (Schema::hasTable('partidos')) {
            Schema::table('partidos', function (Blueprint $table) {
                if (!Schema::hasColumn('partidos', 'torneo_id')) {
                    $table->unsignedBigInteger('torneo_id')->nullable()->index();
                }
                if (!Schema::hasColumn('partidos', 'equipo1_id')) {
                    $table->unsignedBigInteger('equipo1_id')->nullable()->index();
                }
                if (!Schema::hasColumn('partidos', 'equipo2_id')) {
                    $table->unsignedBigInteger('equipo2_id')->nullable()->index();
                }
                if (!Schema::hasColumn('partidos', 'fecha')) {
                    $table->date('fecha')->nullable()->index();
                }
                if (!Schema::hasColumn('partidos', 'hora')) {
                    $table->time('hora')->nullable();
                }
                if (!Schema::hasColumn('partidos', 'cancha')) {
                    $table->string('cancha')->nullable();
                }
                if (!Schema::hasColumn('partidos', 'estado')) {
                    $table->string('estado')->default('pendiente')->index();
                }
            });

            // FKs (envueltas en try si ya existen)
            try {
                Schema::table('partidos', function (Blueprint $table) {
                    if (Schema::hasColumn('partidos', 'torneo_id')) {
                        $table->foreign('torneo_id', 'partidos_torneo_fk')->references('id')->on('torneos')->onDelete('cascade');
                    }
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('partidos', function (Blueprint $table) {
                    if (Schema::hasColumn('partidos', 'equipo1_id')) {
                        $table->foreign('equipo1_id', 'partidos_equipo1_fk')->references('id')->on('equipos')->onDelete('cascade');
                    }
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('partidos', function (Blueprint $table) {
                    if (Schema::hasColumn('partidos', 'equipo2_id')) {
                        $table->foreign('equipo2_id', 'partidos_equipo2_fk')->references('id')->on('equipos')->onDelete('cascade');
                    }
                });
            } catch (\Throwable $e) {}
        }

        // RESULTADOS: único por partido y columnas de marcador
        if (Schema::hasTable('resultados')) {
            Schema::table('resultados', function (Blueprint $table) {
                if (!Schema::hasColumn('resultados', 'partido_id')) {
                    $table->unsignedBigInteger('partido_id')->unique()->index();
                } else {
                    // intentar declarar índice único si no existe
                    try { $table->unique('partido_id', 'resultados_partido_unique'); } catch (\Throwable $e) {}
                }
                if (!Schema::hasColumn('resultados', 'marcador_equipo1')) {
                    $table->unsignedSmallInteger('marcador_equipo1')->nullable();
                }
                if (!Schema::hasColumn('resultados', 'marcador_equipo2')) {
                    $table->unsignedSmallInteger('marcador_equipo2')->nullable();
                }
            });
            try {
                Schema::table('resultados', function (Blueprint $table) {
                    if (Schema::hasColumn('resultados', 'partido_id')) {
                        $table->foreign('partido_id', 'resultados_partido_fk')->references('id')->on('partidos')->onDelete('cascade');
                    }
                });
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        // Solo limpiar constraints/índices agregados
        try { Schema::table('resultados', fn (Blueprint $t) => $t->dropForeign('resultados_partido_fk')); } catch (\Throwable $e) {}
        try { Schema::table('partidos', fn (Blueprint $t) => $t->dropForeign('partidos_torneo_fk')); } catch (\Throwable $e) {}
        try { Schema::table('partidos', fn (Blueprint $t) => $t->dropForeign('partidos_equipo1_fk')); } catch (\Throwable $e) {}
        try { Schema::table('partidos', fn (Blueprint $t) => $t->dropForeign('partidos_equipo2_fk')); } catch (\Throwable $e) {}
        try { Schema::table('resultados', fn (Blueprint $t) => $t->dropUnique('resultados_partido_unique')); } catch (\Throwable $e) {}
        try { DB::statement("ALTER TABLE torneos DROP CONSTRAINT chk_torneos_deporte"); } catch (\Throwable $e) {}
    }
};