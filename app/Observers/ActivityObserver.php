<?php

namespace App\Observers;

use App\Models\Activity;
use App\Services\FitcoinService;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

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

        $level     = $col->nivel_asignado;
        $metaSteps = config("coinfits.levels.{$level}.steps", 0);
        $metaMins  = config("coinfits.levels.{$level}.minutes", 0);

        $meetsGoal = $activity->steps >= $metaSteps
            && $activity->duration >= $metaMins
            && $activity->duration_unit === 'minutos';

        $startDay = Carbon::parse($activity->created_at)->startOfDay();
        $endDay   = Carbon::parse($activity->created_at)->endOfDay();

        // Verificar si ya se registró una actividad válida hoy
        $alreadyMetToday = Activity::where('user_id', $activity->user_id)
            ->where('id', '<', $activity->id)
            ->whereBetween('created_at', [$startDay, $endDay])
            ->where('steps', '>=', $metaSteps)
            ->where('duration', '>=', $metaMins)
            ->where('duration_unit', 'minutos')
            ->exists();

        $awarded = 0;

        if ($meetsGoal && ! $alreadyMetToday) {
            $awarded += 10;

            if ($activity->selfie_path || $activity->location_lat) {
                $awarded += 2;
            }

            if ($activity->steps > $metaSteps) {
                $awarded += 3;
            }
        }

        if ($awarded > 0) {
            $this->fitcoin->award(
                $col,
                $awarded,
                "Actividad ID {$activity->id}"
            );
        }

        // Evaluar bono semanal si esta actividad aportó para la meta diaria
        if ($meetsGoal && ! $alreadyMetToday) {
            $weekStart = $startDay->copy()->startOfWeek();
            $weekEnd   = $startDay->copy()->endOfWeek();

            $daysMet = Activity::where('user_id', $activity->user_id)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->where('steps', '>=', $metaSteps)
                ->where('duration', '>=', $metaMins)
                ->where('duration_unit', 'minutos')
                ->selectRaw('DATE(created_at) as day')
                ->groupBy('day')
                ->get()
                ->count();

            if ($daysMet >= 5) {
                $bonusExists = $col->fitcoinAccount
                    ? $col->fitcoinAccount->transactions()
                        ->where('description', 'like', 'Bono semanal%')
                        ->whereBetween('created_at', [$weekStart, $weekEnd])
                        ->exists()
                    : false;

                if (! $bonusExists) {
                    $this->fitcoin->award(
                        $col,
                        10,
                        'Bono semanal ' . $weekStart->format('Y-m-d')
                    );
                }
            }
        }
    }
}
