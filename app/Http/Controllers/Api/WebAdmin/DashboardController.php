<?php

namespace App\Http\Controllers\Api\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\Colaborator;
use App\Models\FitcoinAccount;
use App\Models\FitcoinTransaction;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/webadmin/stats
     * Devuelve:
     * {
     *   total_collaborators: 123,
     *   total_coinfits: 4580,
     *   redeemed_items: 98,
     *   level_distribution: { HalconFit: 12, JaguarFit: 45, KoalaFit: 31 },
     *   recent_activities: [ {id, user:{id,name}, exercise_type, created_at}, … ]
     * }
     */
    public function index(Request $request)
    {
        // 1) total colaboradores
        $totalCollabs = Colaborator::count();

        // 2) coinFits otorgados (balance total)
        $totalCoinFits = FitcoinAccount::sum('balance');

        // 3) canjes (ejemplo: transacciones debit)
        $redeemed = FitcoinTransaction::where('type', 'debit')->count();

        // 4) distribución de niveles (asumiendo nivel_asignado)
        $levels = Colaborator::select('nivel_asignado', DB::raw('count(*) as total'))
            ->groupBy('nivel_asignado')
            ->pluck('total', 'nivel_asignado');

        // 5) últimas 5 actividades
        $recent = Activity::with('user')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'total_collaborators' => $totalCollabs,
            'total_coinfits'      => $totalCoinFits,
            'redeemed_items'      => $redeemed,
            'level_distribution'  => $levels,
            'recent_activities'   => $recent,
        ]);
    }
}
