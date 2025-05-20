<?php

namespace App\Http\Controllers\Api\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\Colaborator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ColaboratorController extends Controller
{
    // 1. Listar todos los colaboradores con su usuario relacionado
    public function index()
    {
        // photo_url vendrá junto al modelo gracias al $appends en el modelo
        $colaborators = Colaborator::with('user')->get();
        return response()->json($colaborators);
    }

    // 2. Crear un nuevo colaborador + generar su usuario “Colaborador”
    public function store(Request $request)
    {
        $data = $request->validate([
            // datos de usuario
            'nombre'               => 'required|string|max:255',
            'email'                => 'required|email|unique:users,email',
            'password'             => 'required|string|min:8|confirmed',
            // datos de colaborador
            'sexo'                 => 'nullable|in:masculino,femenino',
            'telefono'             => 'nullable|string|max:50',
            'direccion'            => 'nullable|string|max:255',
            'ocupacion'            => 'nullable|string|max:255',
            'area'                 => 'nullable|string|max:255',
            'peso'                 => 'nullable|numeric|min:0',
            'altura'               => 'nullable|numeric|min:0',
            'tipo_sangre'          => 'nullable|string|max:10',
            'alergias'             => 'nullable|string',
            'padecimientos'        => 'nullable|string',
            'indice_masa_corporal' => 'nullable|numeric|min:0',
            'nivel_asignado'       => 'nullable|string|max:50',
            'photo'                => 'nullable|image|mimes:jpeg,png|max:2048',
        ]);

        // 1) Crear Usuario
        $user = User::create([
            'name'     => $data['nombre'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id'  => 4, // rol Colaborador
            'status'   => 'Activo',
        ]);

        // 2) Preparar y crear Colaborador
        $colData = [
            'user_id'              => $user->id,
            'nombre'               => $data['nombre'],
            'sexo'                 => $data['sexo'] ?? null,
            'telefono'             => $data['telefono'] ?? null,
            'direccion'            => $data['direccion'] ?? null,
            'ocupacion'            => $data['ocupacion'] ?? null,
            'area'                 => $data['area'] ?? null,
            'peso'                 => $data['peso'] ?? null,
            'altura'               => $data['altura'] ?? null,
            'tipo_sangre'          => $data['tipo_sangre'] ?? null,
            'alergias'             => $data['alergias'] ?? null,
            'padecimientos'        => $data['padecimientos'] ?? null,
            'indice_masa_corporal' => $data['indice_masa_corporal'] ?? null,
            'nivel_asignado'       => $data['nivel_asignado'] ?? null,
        ];

        if ($request->hasFile('photo')) {
            $colData['photo_path'] = $request->file('photo')
                ->store('colaborator_photos', 'public');
        }

        $colaborator = Colaborator::create($colData);
        $colaborator->load('user');

        return response()->json($colaborator, 201);
    }

    // 3. Mostrar un colaborador en particular
    public function show(Colaborator $colaborator)
    {
        $colaborator->load('user');
        return response()->json($colaborator);
    }

    // 4. Actualizar un colaborador existente
    public function update(Request $request, Colaborator $colaborator)
    {
        $data = $request->validate([
            'nombre'               => 'sometimes|required|string|max:255',
            'sexo'                 => 'sometimes|in:masculino,femenino',
            'telefono'             => 'sometimes|nullable|string|max:50',
            'direccion'            => 'sometimes|nullable|string|max:255',
            'ocupacion'            => 'sometimes|nullable|string|max:255',
            'area'                 => 'sometimes|nullable|string|max:255',
            'peso'                 => 'sometimes|nullable|numeric|min:0',
            'altura'               => 'sometimes|nullable|numeric|min:0',
            'tipo_sangre'          => 'sometimes|nullable|string|max:10',
            'alergias'             => 'sometimes|nullable|string',
            'padecimientos'        => 'sometimes|nullable|string',
            'indice_masa_corporal' => 'sometimes|nullable|numeric|min:0',
            'nivel_asignado'       => 'sometimes|nullable|string|max:50',
            'photo'                => 'sometimes|nullable|image|mimes:jpeg,png|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($colaborator->photo_path) {
                Storage::disk('public')->delete($colaborator->photo_path);
            }
            $data['photo_path'] = $request->file('photo')
                ->store('colaborator_photos', 'public');
        }

        $colaborator->update($data);
        $colaborator->load('user');

        return response()->json($colaborator);
    }

    // 5. Eliminar un colaborador
    public function destroy(Colaborator $colaborator)
    {
        if ($colaborator->photo_path) {
            Storage::disk('public')->delete($colaborator->photo_path);
        }
        $colaborator->delete();

        return response()->json(null, 204);
    }
}
