<?php
// app/Models/FitcoinAccount.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FitcoinAccount extends Model
{
    protected $fillable = [
        'colaborator_id',
        'balance',
        'streak_count',
        'last_activity_date',
    ];

    /**
     * Relación con Colaborator
     */
    public function colaborator()
    {
        return $this->belongsTo(Colaborator::class);
    }

    /**
     * Relación con FitcoinTransaction
     */
    public function transactions()
    {
        return $this->hasMany(FitcoinTransaction::class);
    }
}
