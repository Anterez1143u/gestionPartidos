<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol', // coincide con la migración (campo 'rol')
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // arreglar $casts (propiedad, no método)
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Comprueba si el usuario tiene uno o varios roles.
     * Uso: $user->hasRole('admin') o $user->hasRole('admin','editor')
     */
    public function hasRole(...$roles): bool
    {
        // usar el campo 'rol' (coincide con la migración)
        if (empty($this->rol)) {
            return false;
        }

        foreach ($roles as $r) {
            if (is_array($r)) {
                foreach ($r as $sub) {
                    if (strcasecmp($this->rol, $sub) === 0) {
                        return true;
                    }
                }
            } else {
                if (strcasecmp($this->rol, $r) === 0) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
}
