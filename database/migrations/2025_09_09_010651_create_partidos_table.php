<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('partidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipo1_id');
            $table->unsignedBigInteger('equipo2_id');
            $table->unsignedBigInteger('torneo_id');
            $table->unsignedBigInteger('grupo_id')->nullable();
            $table->date('fecha')->nullable();
            $table->time('hora')->nullable();
            $table->string('cancha')->nullable();
            $table->unsignedBigInteger('resultado_id')->nullable();
            $table->timestamps();

            $table->foreign('equipo1_id')->references('id')->on('equipos')->onDelete('cascade');
            $table->foreign('equipo2_id')->references('id')->on('equipos')->onDelete('cascade');
            $table->foreign('torneo_id')->references('id')->on('torneos')->onDelete('cascade');
            $table->foreign('grupo_id')->references('id')->on('grupos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partidos');
    }
};
