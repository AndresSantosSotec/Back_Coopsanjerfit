<?php

namespace App\Http\Controllers\Api\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // 1. Listar todos los roles
    public function index()
    {
        return response()->json(Role::all());
    }

    // 2. Crear un nuevo rol
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|unique:roles,name',
            'description' => 'nullable|string',
        ]);

        $role = Role::create($data);
        return response()->json($role, 201);
    }

    // 3. Mostrar un rol específico
    public function show(Role $role)
    {
        return response()->json($role);
    }

    // 4. Actualizar un rol
    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'        => "required|string|unique:roles,name,{$role->id}",
            'description' => 'nullable|string',
        ]);

        $role->update($data);
        return response()->json($role);
    }

    // 5. Eliminar un rol
    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(null, 204);
    }
}
