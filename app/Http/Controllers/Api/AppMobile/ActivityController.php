<?php
// app/Http/Controllers/Api/AppMobile/ActivityController.php

namespace App\Http\Controllers\Api\AppMobile;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ActivityController extends Controller
{
    // Listar actividades del usuario autenticado
    public function index(Request $request)
    {
        $user = $request->user();
        $activities = Activity::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn($act) => [
                'id'                 => $act->id,
                'exercise_type'      => $act->exercise_type,
                'duration'           => $act->duration,
                'duration_unit'      => $act->duration_unit,
                'intensity'          => $act->intensity,
                'calories'           => $act->calories,
                'steps'              => $act->steps,
                'selfie_url'         => $act->selfie_url,
                'device_image_url'   => $act->device_image_url,
                'attachments_url'    => $act->attachments_url,
                'notes'              => $act->notes,
                'location_lat'       => $act->location_lat,
                'location_lng'       => $act->location_lng,
                'created_at'         => $act->created_at->toDateTimeString(),
            ]);

        return response()->json($activities);
    }

    // Guardar nueva actividad
    public function store(Request $request)
    {
        $user = $request->user();

        // 1. Validación
        $data = $request->validate([
            'exercise_type'    => 'required|string',
            'duration'         => 'required|numeric|min:1',
            'duration_unit'    => 'required|in:minutos,horas',
            'intensity'        => 'required|string',
            'calories'         => 'nullable|integer',
            'steps'            => 'nullable|integer',
            'selfie'           => 'nullable|image|max:5120',
            'device_image'     => 'nullable|image|max:5120',
            'attachments.*'    => 'nullable|file|max:10240',
            'notes'            => 'nullable|string',
            'location_lat'     => 'nullable|numeric',
            'location_lng'     => 'nullable|numeric',
        ]);

        // 2. Manejo de archivos
        if ($request->hasFile('selfie')) {
            $data['selfie_path'] = $request->file('selfie')
                ->store('activities/selfies', 'public');
        }
        if ($request->hasFile('device_image')) {
            $data['device_image_path'] = $request->file('device_image')
                ->store('activities/device', 'public');
        }
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->store('activities/attachments', 'public');
            }
            $data['attachments'] = $attachments;
        }

        // 3. Creación de la actividad
        $activity = $user->activities()->create($data);

        // 4. Log para auditoría
        Log::info("Activity CREATED for user_id={$user->id}, activity_id={$activity->id}");

        // 5. Respuesta
        return response()->json([
            'message'  => 'Actividad registrada correctamente',
            'activity' => $activity
        ], 201);
    }

    //actividades por usuario especifico logeado
        public function getUserActivities(Request $request)
    {
        $user = $request->user();
        
        $query = Activity::where('user_id', $user->id);

        // Optional date range filter
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Optional exercise type filter
        if ($request->has('exercise_type')) {
            $query->where('exercise_type', $request->exercise_type);
        }

        $activities = $query->latest()
            ->get()
            ->map(fn($activity) => [
                'id' => $activity->id,
                'exercise_type' => $activity->exercise_type,
                'duration' => $activity->duration,
                'duration_unit' => $activity->duration_unit,
                'intensity' => $activity->intensity,
                'calories' => $activity->calories,
                'steps' => $activity->steps,
                'selfie_url' => $activity->selfie_url,
                'device_image_url' => $activity->device_image_url,
                'attachments_url' => $activity->attachments_url,
                'notes' => $activity->notes,
                'location_lat' => $activity->location_lat,
                'location_lng' => $activity->location_lng,
                'created_at' => $activity->created_at->toDateTimeString(),
            ]);

        return response()->json([
            'status' => 'success',
            'data' => $activities
        ]);
    }
}
