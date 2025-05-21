<?php
namespace App\Services;

use App\Models\Colaborator;
use App\Models\FitcoinAccount;
use App\Models\FitcoinTransaction;

class FitcoinService
{
    /**
     * Otorga CoinFits a un colaborador según la cantidad y descripción.
     * Crea la cuenta si no existe.
     */
    public function award(Colaborator $col, int $amount, string $description = null): FitcoinTransaction
    {
        // 1. Obtener o crear la cuenta
        $account = FitcoinAccount::firstOrCreate(
            ['colaborator_id' => $col->id],
            ['balance' => 0]
        );

        // 2. Registrar transacción
        $tx = $account->transactions()->create([
            'type'        => $amount >= 0 ? 'credit' : 'debit',
            'amount'      => $amount,
            'description' => $description,
        ]);

        // 3. Actualizar balance
        $account->balance += $amount;
        $account->save();

        return $tx;
    }
}
