<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $fillable = [
        'nombre',
        'torneo_id',
    ];

    public function torneo()
    {
        return $this->belongsTo(Torneo::class);
    }
}
