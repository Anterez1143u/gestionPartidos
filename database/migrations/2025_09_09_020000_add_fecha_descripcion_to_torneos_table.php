<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('torneos', function (Blueprint $table) {
            $table->date('fecha_inicio')->nullable()->after('formato_fase_2');
            $table->date('fecha_fin')->nullable()->after('fecha_inicio');
            $table->text('descripcion')->nullable()->after('fecha_fin');
        });
    }

    public function down(): void
    {
        Schema::table('torneos', function (Blueprint $table) {
            $table->dropColumn(['fecha_inicio', 'fecha_fin', 'descripcion']);
        });
    }
};