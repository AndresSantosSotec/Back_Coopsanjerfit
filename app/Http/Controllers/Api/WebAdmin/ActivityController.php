<?php

namespace App\Http\Controllers\Api\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
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
}
