<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resultado extends Model
{
    protected $table = 'resultados';

    protected $fillable = [
        'partido_id',
        'marcador_equipo1',
        'marcador_equipo2',
    ];

    public function partido()
    {
        return $this->belongsTo(Partido::class);
    }
}
