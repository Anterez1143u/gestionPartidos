<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partido extends Model
{
    use HasFactory;

    protected $fillable = [
        'torneo_id',
        'fecha',
        'hora',
        'cancha',
        'local_id',
        'visitante_id',
        'equipo1_id',
        'equipo2_id',
        'grupo_id',
        'resultado_id',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function torneo()
    {
        return $this->belongsTo(Torneo::class);
    }

    public function local()
    {
        return $this->belongsTo(Equipo::class, 'local_id');
    }

    public function visitante()
    {
        return $this->belongsTo(Equipo::class, 'visitante_id');
    }

    // Alias para compatibilidad con migración/controladores que usan equipo1/equipo2
    public function equipo1()
    {
        return $this->belongsTo(Equipo::class, 'equipo1_id');
    }

    public function equipo2()
    {
        return $this->belongsTo(Equipo::class, 'equipo2_id');
    }

    // <-- ADICIÓN: relación grupo para evitar el error indefinido
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function resultado()
    {
        return $this->hasOne(Resultado::class);
    }
}
