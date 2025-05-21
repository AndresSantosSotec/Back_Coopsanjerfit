<?php
// app/Models/FitcoinTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FitcoinTransaction extends Model
{
    protected $fillable = [
        'fitcoin_account_id',
        'type',
        'amount',
        'description',
    ];

    /**
     * Relación con FitcoinAccount
     */
    public function account()
    {
        return $this->belongsTo(FitcoinAccount::class);
    }
}
