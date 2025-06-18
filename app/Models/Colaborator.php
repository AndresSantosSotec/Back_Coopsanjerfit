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
        'IMC_objetivo',
        'peso_objetivo',
        'nickname',
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
        if (! $this->photo_path) {
            return null;
        }

        // Usa url() en lugar de Storage::url() para forzar la URL absoluta
        return url("storage/{$this->photo_path}");
    }

    // Agregar el método para calcular el peso objetivo
    protected static function booted()
    {
        static::saving(function ($colaborator) {
            // Si tenemos altura y IMC_objetivo, calculamos el peso_objetivo
            if ($colaborator->altura && $colaborator->IMC_objetivo) {
                $alturaEnMetros = $colaborator->altura / 100; // convertir cm a metros
                $colaborator->peso_objetivo = round(
                    ($colaborator->IMC_objetivo ?? 24.0) * ($alturaEnMetros * $alturaEnMetros),
                    2
                );
            }
        });
    }

    // Agregar un accessor para la diferencia de peso
    public function getDiferenciaPesoAttribute(): ?float
    {
        if (!$this->peso || !$this->peso_objetivo) {
            return null;
        }
        return round($this->peso - $this->peso_objetivo, 2);
    }
}
