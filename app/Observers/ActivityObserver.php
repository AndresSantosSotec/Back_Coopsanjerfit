<?php

namespace App\Observers;

use App\Models\Activity;
use App\Services\FitcoinService;
use Illuminate\Support\Carbon;
use App\Models\FitcoinTransaction;
use Illuminate\Support\Facades\Config;

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
        if (! $col) {
            return;
        }

        // 1) Metas del colaborador
        $level     = $col->nivel_asignado;
        $metaSteps = config("coinfits.levels.{$level}.steps", 0);
        $metaMins  = config("coinfits.levels.{$level}.minutes", 0);

        // 2) Convertir duración a minutos
        $durationMinutes = $activity->duration_unit === 'horas'
            ? $activity->duration * 60
            : $activity->duration;

        // 3) ¿Cumple los requisitos básicos?
        $meetsGoal = $activity->steps >= $metaSteps
            && $durationMinutes >= $metaMins;

        // 4) Rango del día de la actividad
        $activityDate = Carbon::parse($activity->created_at);
        $startDay     = $activityDate->copy()->startOfDay();
        $endDay       = $activityDate->copy()->endOfDay();

        // 5) ¿Ya tuvo una actividad válida hoy?
        $alreadyMetToday = Activity::where('user_id', $activity->user_id)
            ->where('id', '<', $activity->id)
            ->whereBetween('created_at', [$startDay, $endDay])
            ->where('steps', '>=', $metaSteps)
            ->whereRaw(
                "CASE WHEN duration_unit = 'horas' THEN duration * 60 ELSE duration END >= ?",
                [$metaMins]
            )
            ->exists();

        // 6) Calcular monedas a otorgar por esta actividad
        $awarded = 0;
        if ($meetsGoal && ! $alreadyMetToday) {
            $awarded += 10; // base diaria
            if ($activity->selfie_path || $activity->location_lat) {
                $awarded += 2; // bonus selfie o ubicación
            }
            if ($activity->steps > $metaSteps) {
                $awarded += 3; // bonus sobrepasó pasos
            }
        }

        if ($awarded > 0) {
            $this->fitcoin->award(
                $col,
                $awarded,
                "Actividad ID {$activity->id}"
            );
        }

        // 7) Bono semanal: contar días cumplidos en la semana de la actividad
        $weekStart = $activityDate->copy()->startOfWeek();
        $weekEnd   = $activityDate->copy()->endOfWeek();

        $daysMet = Activity::where('user_id', $activity->user_id)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->get()
            ->groupBy(fn($act) => Carbon::parse($act->created_at)->toDateString())
            ->filter(function ($acts) use ($metaSteps, $metaMins) {
                foreach ($acts as $act) {
                    $mins = $act->duration_unit === 'horas'
                        ? $act->duration * 60
                        : $act->duration;
                    if ($act->steps >= $metaSteps && $mins >= $metaMins) {
                        return true;
                    }
                }
                return false;
            })
            ->count();

        if ($daysMet >= 5) {
            $bonusDesc = 'Bono semanal ' . $weekStart->toDateString();
            $bonusExists = FitcoinTransaction::where('fitcoin_account_id', $col->fitcoinAccount->id ?? 0)
                ->where('description', $bonusDesc)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->exists();

            if (! $bonusExists) {
                $this->fitcoin->award($col, 10, $bonusDesc);
            }
        }
    }
}
