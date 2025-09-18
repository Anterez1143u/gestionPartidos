<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Normalizar datos antiguos a 'futboll'
        try {
            DB::table('torneos')
                ->whereIn('deporte', ['utboll','futbol','fútbol'])
                ->update(['deporte' => 'futboll']);
        } catch (\Throwable $e) {}

        // Buscar y eliminar cualquier CHECK en 'torneos'
        $checks = DB::select("
            SELECT tc.CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
            WHERE tc.CONSTRAINT_SCHEMA = DATABASE()
              AND tc.TABLE_NAME = 'torneos'
              AND tc.CONSTRAINT_TYPE = 'CHECK'
        ");

        foreach ($checks as $c) {
            $constraint = $c->CONSTRAINT_NAME;

            // MySQL 8+ usa DROP CHECK, algunas variantes usan DROP CONSTRAINT
            try {
                DB::statement("ALTER TABLE `torneos` DROP CHECK `{$constraint}`");
            } catch (\Throwable $e) {
                try {
                    DB::statement("ALTER TABLE `torneos` DROP CONSTRAINT `{$constraint}`");
                } catch (\Throwable $e2) {}
            }
        }

        // Crear CHECK correcto (solo 'futboll','voley','baloncesto')
        try {
            DB::statement("ALTER TABLE `torneos` ADD CONSTRAINT `chk_torneos_deporte` CHECK (`deporte` IN ('futboll','voley','baloncesto'))");
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        // Eliminar el CHECK actual
        try {
            DB::statement("ALTER TABLE `torneos` DROP CHECK `chk_torneos_deporte`");
        } catch (\Throwable $e) {
            try {
                DB::statement("ALTER TABLE `torneos` DROP CONSTRAINT `chk_torneos_deporte`");
            } catch (\Throwable $e2) {}
        }

        // Restaurar un CHECK anterior de ejemplo (ajústalo si lo necesitas)
        try {
            DB::statement("ALTER TABLE `torneos` ADD CONSTRAINT `chk_torneos_deporte` CHECK (`deporte` IN ('utboll','voley','baloncesto'))");
        } catch (\Throwable $e) {}
    }
};