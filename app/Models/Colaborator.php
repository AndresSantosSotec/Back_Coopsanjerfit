<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Colaborator extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nombre',
        'sexo',
        'telefono',
        'direccion',
        'ocupacion',
        'area',
        'peso',
        'altura',
        'tipo_sangre',
        'alergias',
        'padecimientos',
        'indice_masa_corporal',
        'nivel_asignado',
        'photo_path',
    ];

    protected $appends = ['photo_url'];

    // Un colaborador pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor for photo_url
    public function getPhotoUrlAttribute()
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }
}