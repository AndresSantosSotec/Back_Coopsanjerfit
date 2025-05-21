<?php

namespace App\Observers;

use App\Models\Activity;
use App\Services\FitcoinService;
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
        if (! $col) return;

        $awarded   = 0;
        $level     = $col->nivel_asignado;
        $metaSteps = config("coinfits.levels.{$level}.steps", 0);
        $metaMins  = config("coinfits.levels.{$level}.minutes", 0);

        // 1) Cumplió pasos **y** minutos activos?
        if (
            $activity->steps >= $metaSteps
            && $activity->duration >= $metaMins
            && $activity->duration_unit === 'minutos'
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
    }
}
