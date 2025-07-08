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

        $awarded = 0;
        // Se otorgan 10 CoinFits cuando el colaborador alcanza la meta de
        // minutos o la de pasos. Antes se requería cumplir ambas
        // condiciones lo que provocaba recompensas muy bajas cuando
        // una de las métricas no estaba disponible (por ejemplo, sin
        // registro de pasos). Con este ajuste se incentiva la
        // constancia independientemente del tipo de medición
        // proporcionada.
        if ($durationMinutes >= $metaMins || $activity->steps >= $metaSteps) {
            $awarded += 10;
        }
        if ($activity->selfie_path || $activity->location_lat) {
            $awarded += 2;
        }
        if ($activity->steps > $metaSteps) {
            $awarded += 3;
        }

        return $awarded;
    }
}
