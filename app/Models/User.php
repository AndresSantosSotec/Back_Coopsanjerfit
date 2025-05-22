<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Importa el trait de Sanctum
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // Añade HasApiTokens antes de HasFactory
    use HasApiTokens, HasFactory, Notifiable;

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
        'last_login',
    ];

    // Campos que no queremos exponer en JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Casts para fechas y otros tipos
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login'        => 'datetime',
    ];

    /**
     * Relación con rol
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function colaborator()
    {
        return $this->hasOne(Colaborator::class);
    }

        public function activities()
    {
        // Ajusta App\Models\Activity al namespace correcto de tu modelo de actividad
        return $this->hasMany(Activity::class);
    }
    
    
}
