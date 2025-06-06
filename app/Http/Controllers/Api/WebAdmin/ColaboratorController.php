<?php

namespace App\Http\Controllers\Api\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\Colaborator;
use App\Models\User;
use App\Models\FitcoinAccount;          // ✅ nuevo
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ColaboratorController extends Controller
{
    /* ---------------------------------------------------------------------
     | 1. Listar colaboradores (incluye saldo de Fitcoins)
     * --------------------------------------------------------------------*/
    public function index()
    {
        // Traemos la relación fitcoinAccount para que se calcule coin_fits
        $colaborators = Colaborator::with(['user', 'fitcoinAccount'])->get();

        return response()->json($colaborators);
    }

    /* ---------------------------------------------------------------------
     | 2. Crear colaborador + usuario + cuenta de Fitcoins
     * --------------------------------------------------------------------*/
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
            'IMC_objetivo'         => 'nullable|numeric|min:18.5|max:40',
        ]);

        /* 1) Crear usuario */
        $user = User::create([
            'name'     => $data['nombre'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id'  => 4,   // Rol “Colaborador”
            'status'   => 'Activo',
        ]);

        /* 2) Crear colaborador */
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
            'IMC_objetivo'         => $data['IMC_objetivo'] ?? 24.0,  // IMC objetivo por defecto
        ];

        if ($request->hasFile('photo')) {
            $colData['photo_path'] = $request->file('photo')
                ->store('colaborator_photos', 'public');
        }

        // Calcular peso objetivo si tenemos altura
        if (isset($data['altura'])) {
            $alturaEnMetros = $data['altura'] / 100; // convertir cm a metros
            $colData['peso_objetivo'] = round(
                ($colData['IMC_objetivo'] * $alturaEnMetros * $alturaEnMetros),
                2
            );
        }

        $colaborator = Colaborator::create($colData);

        /* 3) Crear cuenta de Fitcoins con balance 0 (si no existe) */
        $colaborator->fitcoinAccount()->firstOrCreate(['balance' => 0]);

        // Cargamos relaciones para la respuesta
        $colaborator->load(['user', 'fitcoinAccount']);

        return response()->json($colaborator, 201);
    }

    /* ---------------------------------------------------------------------
     | 3. Mostrar colaborador individual (con saldo de Fitcoins)
     * --------------------------------------------------------------------*/
    public function show(Request $request)
    {
        $collab = $request->user()
                         ->colaborator()                    // relación
                         ->with(['user', 'fitcoinAccount']) // cargas
                         ->firstOrFail();

        return response()->json([
            'collaborator' => $collab
        ]);
    }

    /* ---------------------------------------------------------------------
     | 4. Actualizar colaborador
     * --------------------------------------------------------------------*/
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
            'IMC_objetivo'         => 'sometimes|nullable|numeric|min:18.5|max:40',
        ]);

        if ($request->hasFile('photo')) {
            if ($colaborator->photo_path) {
                Storage::disk('public')->delete($colaborator->photo_path);
            }
            $data['photo_path'] = $request->file('photo')
                ->store('colaborator_photos', 'public');
        }

        // Calcular nuevo peso objetivo si cambia IMC o altura
        if (isset($data['IMC_objetivo']) || isset($data['altura'])) {
            $altura = isset($data['altura']) ? $data['altura'] : $colaborator->altura;
            $imc = isset($data['IMC_objetivo']) ? $data['IMC_objetivo'] : $colaborator->IMC_objetivo;

            if ($altura) {
                $alturaEnMetros = $altura / 100;
                $data['peso_objetivo'] = round(($imc * $alturaEnMetros * $alturaEnMetros), 2);
            }
        }

        $colaborator->update($data);

        // Aseguramos que siempre exista cuenta de fitcoins
        $colaborator->fitcoinAccount()->firstOrCreate(['balance' => 0]);

        $colaborator->load(['user', 'fitcoinAccount']);

        return response()->json($colaborator);
    }

    /* ---------------------------------------------------------------------
     | 5. Eliminar colaborador
     * --------------------------------------------------------------------*/
    public function destroy(Colaborator $colaborator)
    {
        if ($colaborator->photo_path) {
            Storage::disk('public')->delete($colaborator->photo_path);
        }

        // Al eliminar colaborador también se elimina su cuenta de Fitcoins
        $colaborator->fitcoinAccount()->delete();
        $colaborator->delete();

        return response()->json(null, 204);
    }
}
