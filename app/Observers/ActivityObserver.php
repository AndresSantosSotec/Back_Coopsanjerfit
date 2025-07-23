<?php

namespace App\Observers;

use App\Models\Activity;
use App\Services\FitcoinService;
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

        $meta = $this->fitcoin->getLevelMeta($col->nivel_asignado);
        $metaSteps = $meta['steps'];
        $metaMins  = $meta['minutes'];

        $awarded = $this->fitcoin->calculateActivityReward($activity, $col);

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

        // 5) Bono por racha de 5 días consecutivos
        $account = $col->fitcoinAccount()->firstOrCreate(['balance' => 0]);

        $lastDate = $account->last_activity_date
            ? Carbon::parse($account->last_activity_date)
            : null;

        if (! $lastDate || !$lastDate->isToday()) {
            if ($lastDate && $lastDate->isYesterday()) {
                $account->streak_count += 1;
            } else {
                $account->streak_count = 1;
            }

            $account->last_activity_date = Carbon::today();

            if ($account->streak_count >= 5) {
                $bonusDesc = 'Bono racha ' . Carbon::today()->toDateString();
                $this->fitcoin->award($col, 10, $bonusDesc);
                $account->streak_count = 0;
            }

            $account->save();
        }
    }
}
