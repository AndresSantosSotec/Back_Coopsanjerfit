<?php

namespace App\Observers;

use App\Models\Activity;
use App\Services\FitcoinService;
use Illuminate\Support\Carbon;
use App\Models\FitcoinTransaction;

class ActivityObserver
{
    protected FitcoinService $fitcoin;

    public function __construct(FitcoinService $fitcoin)
    {
        $this->fitcoin = $fitcoin;
    }

    public function created(Activity $activity): void
    {
        $col = $activity->user->colaborator;
        if (! $col) {
            return;
        }

        // Metas del colaborador
        $level     = $col->nivel_asignado;
        $metaSteps = config("coinfits.levels.{$level}.steps", 0);
        $metaMins  = config("coinfits.levels.{$level}.minutes", 0);

        // 1) Convertir duración a minutos
        $activityMinutes = $activity->duration_unit === 'horas'
            ? $activity->duration * 60
            : $activity->duration;

        // 2) Rango del día de la actividad
        $activityDate = Carbon::parse($activity->created_at);
        $startDay     = $activityDate->copy()->startOfDay();
        $endDay       = $activityDate->copy()->endOfDay();

        // 3) Cuenta actual del colaborador
        //    (solo se utiliza para bonos semanales)
        $account = $col->fitcoinAccount;

        // 4) Actividades previas hoy
        $previousActs = Activity::where('user_id', $activity->user_id)
            // Considerar solo actividades previas dentro del mismo día
            // Usamos created_at en lugar del ID para evitar problemas de
            // ordenamiento o importaciones manuales.
            ->where('created_at', '<', $activity->created_at)
            ->whereBetween('created_at', [$startDay, $endDay])
            ->get();

        $prevSteps   = $previousActs->sum('steps');
        $prevMinutes = $previousActs->sum(function (Activity $act) {
            return $act->duration_unit === 'horas'
                ? $act->duration * 60
                : $act->duration;
        });

        // 5) Meta cumplida antes y ahora
        $goalMetBefore = $prevSteps >= $metaSteps && $prevMinutes >= $metaMins;
        $totalSteps    = $prevSteps + $activity->steps;
        $totalMinutes  = $prevMinutes + $activityMinutes;
        $goalMetNow    = $totalSteps >= $metaSteps && $totalMinutes >= $metaMins;

        // 6) Calcular premio diario
        $awarded = 0;
        if (! $goalMetBefore && $goalMetNow) {
            $awarded += 10; // premio base
            if ($activity->selfie_path || $activity->location_lat) {
                $awarded += 2; // selfie/ubicación
            }
            if ($activity->steps > $metaSteps) {
                $awarded += 3; // sobrepasó pasos
            }
        }

        // 7) Otorgar las CoinFits sin límite diario
        //    La variable $earnedToday fue eliminada para permitir
        //    múltiples premios en un mismo día.
        if ($awarded > 0) {
            $this->fitcoin->award(
                $col,
                $awarded,
                "Actividad ID {$activity->id}"
            );
        }

        // 8) Bono semanal (semana de la actividad)
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
            $bonusDesc   = 'Bono semanal ' . $weekStart->toDateString();
            $bonusExists = $account
                ? $account->transactions()
                    ->where('description', $bonusDesc)
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->exists()
                : false;

            if (! $bonusExists) {
                $this->fitcoin->award($col, 10, $bonusDesc);
            }
        }
    }
}
