<?php

namespace App\Http\Controllers\Api\AppMobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;        // ← Importa el facade de Log
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            // Logea el fallo
            Log::warning("Login FAILED for email: {$credentials['email']}");
            // Devuelve 401 para que el cliente lo capte como error
            return response()->json([
                'message' => 'Credenciales no válidas'
            ], 401);
        }

        // Actualizar last_login y generar token
        $user->update(['last_login' => now()]);
        $token = $user->createToken('app-token')->plainTextToken;

        // Logea el éxito
        Log::info("Login SUCCESS for user_id: {$user->id}");

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }
    public function user(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }
}
