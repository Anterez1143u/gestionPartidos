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
        Schema::create('torneos', function (Blueprint $table) {
            $table->id();
            $table->string('deporte');
            $table->integer('numero_participantes');
            $table->enum('fase', ['unica', 'multifase']);
            $table->string('formato_fase_unica')->nullable();
            $table->string('formato_fase_1')->nullable();
            $table->string('formato_fase_2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('torneos');
    }
};
