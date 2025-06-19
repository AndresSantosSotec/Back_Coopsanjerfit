<?php

namespace App\Observers;

use App\Models\Activity;
use App\Services\FitcoinService;
use Carbon\Carbon;

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

        $level     = $col->nivel_asignado;
        $metaSteps = config("coinfits.levels.{$level}.steps", 0);
        $metaMins  = config("coinfits.levels.{$level}.minutes", 0);

        $activityMinutes = $activity->duration_unit === 'horas'
            ? $activity->duration * 60
            : $activity->duration;

        $startDay = Carbon::parse($activity->created_at)->startOfDay();
        $endDay   = Carbon::parse($activity->created_at)->endOfDay();

        $account = $col->fitcoinAccount;
        $earnedToday = $account
            ? $account->transactions()
                ->where('type', 'credit')
                ->where('description', 'not like', 'Bono semanal%')
                ->whereBetween('created_at', [$startDay, $endDay])
                ->sum('amount')
            : 0;

        $previousActs = Activity::where('user_id', $activity->user_id)
            ->where('id', '<', $activity->id)
            ->whereBetween('created_at', [$startDay, $endDay])
            ->get();

        $prevSteps = $previousActs->sum('steps');
        $prevMinutes = $previousActs->sum(function (Activity $act) {
            return $act->duration_unit === 'horas'
                ? $act->duration * 60
                : $act->duration;
        });

        $goalMetBefore = $prevSteps >= $metaSteps && $prevMinutes >= $metaMins;

        $totalSteps   = $prevSteps + $activity->steps;
        $totalMinutes = $prevMinutes + $activityMinutes;

        $goalMetNow = $totalSteps >= $metaSteps && $totalMinutes >= $metaMins;

        $awarded = 0;
        if (! $goalMetBefore && $goalMetNow) {
            $awarded += 10;

            if ($activity->selfie_path || $activity->location_lat) {
                $awarded += 2;
            }

            if ($totalSteps > $metaSteps) {
                $awarded += 3;
            }
        }

        if ($awarded > 0) {
            $remaining = 10 - $earnedToday;
            if ($remaining > 0) {
                $toAward = min($awarded, $remaining);
                $this->fitcoin->award($col, $toAward, "Actividad ID {$activity->id}");
            }
        }

        if (! $goalMetBefore && $goalMetNow) {
            $weekStart = $startDay->copy()->startOfWeek();
            $weekEnd   = $startDay->copy()->endOfWeek();

            $daysMet = Activity::where('user_id', $activity->user_id)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->where('steps', '>=', $metaSteps)
                ->whereRaw(
                    "CASE WHEN duration_unit = 'horas' THEN duration * 60 ELSE duration END >= ?",
                    [$metaMins]
                )
                ->selectRaw('DATE(created_at) as day')
                ->groupBy('day')
                ->get()
                ->count();

            if ($daysMet >= 5) {
                $bonusExists = $account
                    ? $account->transactions()
                        ->where('description', 'like', 'Bono semanal%')
                        ->whereBetween('created_at', [$weekStart, $weekEnd])
                        ->exists()
                    : false;

                if (! $bonusExists) {
                    $this->fitcoin->award($col, 10, 'Bono semanal ' . $weekStart->format('Y-m-d'));
                }
            }
        }
    }
}
