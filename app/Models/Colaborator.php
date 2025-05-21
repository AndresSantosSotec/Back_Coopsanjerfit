<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Colaborator extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | ATRIBUTOS
    |--------------------------------------------------------------------------
    */
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

    /**  Estos campos virtuales aparecerán en la respuesta JSON */
    protected $appends = ['photo_url', 'coin_fits'];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    /** Relación con el usuario del sistema */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Relación 1–1 con la cuenta de Fitcoins */
    public function fitcoinAccount()
    {
        // el FK ya es colaborator_id; se incluye explícitamente por claridad
        return $this->hasOne(FitcoinAccount::class, 'colaborator_id');
    }

    /** Relación 1–N con las transacciones de Fitcoins (opcional) */
    public function fitcoinTransactions()
    {
        return $this->hasMany(FitcoinTransaction::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESORES
    |--------------------------------------------------------------------------
    */

    /** Saldo de Fitcoins expuesto como coin_fits */
    public function getCoinFitsAttribute(): int
    {
        // null-safe: si aún no hay cuenta, retorna 0
        return $this->fitcoinAccount->balance ?? 0;
    }

    /** URL pública de la foto */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path
            ? Storage::url($this->photo_path)   // genera /storage/…
            : null;
    }
}
