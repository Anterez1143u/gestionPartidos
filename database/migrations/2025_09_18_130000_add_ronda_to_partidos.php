<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('partidos') && !Schema::hasColumn('partidos', 'ronda')) {
            Schema::table('partidos', function (Blueprint $table) {
                $table->unsignedSmallInteger('ronda')->nullable()->after('grupo_id')->index();
            });
            // Inicializa a 1 si es null (primer cruce)
            try { DB::table('partidos')->whereNull('ronda')->update(['ronda' => 1]); } catch (\Throwable $e) {}
        }
    }
    public function down(): void
    {
        if (Schema::hasTable('partidos') && Schema::hasColumn('partidos', 'ronda')) {
            Schema::table('partidos', function (Blueprint $table) {
                $table->dropColumn('ronda');
            });
        }
    }
};