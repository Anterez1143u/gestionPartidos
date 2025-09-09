<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'jugadores',
        'categoria',
        'torneo_id',
    ];

    protected $casts = [
        'jugadores' => 'array',
    ];

    public function torneo()
    {
        return $this->belongsTo(Torneo::class);
    }
}
