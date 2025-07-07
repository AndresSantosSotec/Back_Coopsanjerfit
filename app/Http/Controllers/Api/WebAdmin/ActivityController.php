<?php

namespace App\Http\Controllers\Api\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\User;
use App\Services\FitcoinService;
use App\Models\FitcoinTransaction;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    protected $fitcoin;

    public function __construct(FitcoinService $fitcoin)
    {
        $this->fitcoin = $fitcoin;
    }
    /**
     * GET /api/webadmin/activities
     *     ?user_id=   filtra por usuario
     *     ?per_page=  tamaño de página (default 15)
     *     ?search=    busca por tipo de ejercicio o notas
     */
    public function index(Request $request)
    {
        $query = Activity::with('user')               // eager-load usuario
            ->latest();                               // orden descendente

        // — Filtros opcionales —
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('exercise_type', 'like', "%$search%")
                  ->orWhere('notes', 'like', "%$search%");
            });
        }

        // — Paginación —
        $perPage = $request->integer('per_page', 15);
        return response()->json(
            $query->paginate($perPage)
        );
    }

    /**
     * GET /api/webadmin/users/{user}/activities
     */
    public function byUser(User $user, Request $request)
    {
        $perPage = $request->integer('per_page', 15);

        return response()->json(
            $user->activities()            // relación inversa
                 ->with('user')
                 ->latest()
                ->paginate($perPage)
        );
    }

    /**
     * PATCH /api/webadmin/activities/{activity}/validate
     * Cambia el estado de validación de una actividad y ajusta las CoinFits.
     */
    public function updateValidity(Request $request, Activity $activity)
    {
        $data = $request->validate(['is_valid' => 'required|boolean']);

        $col = $activity->user->colaborator;
        $tx = null;
        if ($col) {
            $tx = FitcoinTransaction::where('fitcoin_account_id', $col->fitcoinAccount->id ?? 0)
                ->where('description', "Actividad ID {$activity->id}")
                ->first();
        }

        // Invalidar
        if ($activity->is_valid && $data['is_valid'] === false) {
            $activity->is_valid = false;
            $activity->save();

            if ($col && $tx) {
                $this->fitcoin->award($col, -$tx->amount, "Invalidación actividad ID {$activity->id}");
            }
        }

        // Revalidar
        if (! $activity->is_valid && $data['is_valid'] === true) {
            $activity->is_valid = true;
            $activity->save();

            if ($col && ! $tx) {
                $amount = $this->fitcoin->calculateActivityReward($activity, $col);
                if ($amount > 0) {
                    $this->fitcoin->award($col, $amount, "Actividad ID {$activity->id}");
                }
            }
        }

        return response()->json(['message' => 'Actividad actualizada']);
    }
}
