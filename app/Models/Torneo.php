<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Torneo extends Model
{
    use HasFactory;

    protected $fillable = [
        'deporte',
        'numero_participantes',
        'fase',
        'formato_fase_unica',
        'formato_fase_1',
        'formato_fase_2',
        'fecha_inicio',
        'fecha_fin',
        'descripcion',
    ];

    // Guardar siempre 'futboll'; acepta sinónimos de entrada
    public function setDeporteAttribute($value)
    {
        $v = strtolower(trim((string) $value));
        $map = [
            'futbol' => 'futboll',
            'fútbol' => 'futboll',
            'utboll' => 'futboll',
            'voley' => 'voley',
            'baloncesto' => 'baloncesto',
        ];
        $this->attributes['deporte'] = $map[$v] ?? $v;
    }
}
