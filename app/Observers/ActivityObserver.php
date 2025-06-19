<?php

namespace App\Observers;

use App\Models\Activity;
use App\Services\FitcoinService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;
use App\Models\FitcoinTransaction;

class ActivityObserver
{
    protected $fitcoin;

    public function __construct(FitcoinService $fitcoin)
    {
        $this->fitcoin = $fitcoin;
    }

    public function created(Activity $activity)
    {
        $col = $activity->user->colaborator;
        if (! $col) return;

        $awarded   = 0;
        $level     = $col->nivel_asignado;
        $metaSteps = config("coinfits.levels.{$level}.steps", 0);
        $metaMins  = config("coinfits.levels.{$level}.minutes", 0);

        // Convertir la duración a minutos para la evaluación
        $durationMinutes = $activity->duration_unit === 'horas'
            ? $activity->duration * 60
            : $activity->duration;

        // 1) Cumplió pasos **y** minutos activos?
        if (
            $activity->steps >= $metaSteps
            && $durationMinutes >= $metaMins
        ) {
            $awarded += 10;
        }

        // 2) Evidencia (foto o ubicación)
        if ($activity->selfie_path || $activity->location_lat) {
            $awarded += 2;
        }

        // 3) Superó la meta de pasos
        if ($activity->steps > $metaSteps) {
            $awarded += 3;
        }

        if ($awarded > 0) {
            $this->fitcoin->award(
                $col,
                $awarded,
                "Actividad ID {$activity->id}"
            );
        }

        // 4) Bono semanal por cumplir 5 días o más
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek   = Carbon::now()->endOfWeek();

        $daysMet = Activity::where('user_id', $activity->user_id)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->get()
            ->groupBy(fn($a) => $a->created_at->toDateString())
            ->filter(function ($acts) use ($metaSteps, $metaMins) {
                foreach ($acts as $act) {
                    $minutes = $act->duration_unit === 'horas'
                        ? $act->duration * 60
                        : $act->duration;
                    if ($act->steps >= $metaSteps && $minutes >= $metaMins) {
                        return true;
                    }
                }
                return false;
            })
            ->count();

        if ($daysMet >= 5) {
            $bonusDesc = 'Bono semanal ' . $startOfWeek->toDateString();
            $exists = FitcoinTransaction::where('fitcoin_account_id', $col->fitcoinAccount->id ?? 0)
                ->where('description', $bonusDesc)
                ->exists();

            if (! $exists) {
                $this->fitcoin->award($col, 10, $bonusDesc);
            }
        }
    }
}
