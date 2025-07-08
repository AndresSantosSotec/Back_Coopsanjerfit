<?php
namespace App\Services;

use App\Models\Colaborator;
use App\Models\Activity;
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

    /**
     * Calcula la recompensa que corresponde a una actividad para el colaborador.
     */
    public function calculateActivityReward(Activity $activity, Colaborator $col): int
    {
        $level     = $col->nivel_asignado;
        $metaSteps = config("coinfits.levels.{$level}.steps", 0);
        $metaMins  = config("coinfits.levels.{$level}.minutes", 0);

        $durationMinutes = $activity->duration_unit === 'horas'
            ? $activity->duration * 60
            : $activity->duration;


        $awarded = 2; // Recompensa base por registrar actividad

        // 1) Evidencia opcional
        if ($activity->selfie_path) {
            $awarded += 2;
        }

        if ($activity->location_lat) {
            $awarded += 2;
        }


        // 2) Bono por cumplir meta (minutos o pasos)
        if ($durationMinutes >= $metaMins || $activity->steps >= $metaSteps) {
            $awarded += 3;
        }

        // Obtener la cuenta para calcular lo ya ganado hoy
        $account = FitcoinAccount::firstOrCreate(
            ['colaborator_id' => $col->id],
            ['balance' => 0]
        );

        $earnedToday = $account->transactions()
            ->whereDate('created_at', now()->toDateString())
            ->sum('amount');

        // Limitar a 13 CoinFits por día (10 base + 3 de bono diario)
        $remaining = 13 - $earnedToday;
        if ($remaining <= 0) {
            return 0;
        }


        return min($awarded, $remaining);
    }
}
