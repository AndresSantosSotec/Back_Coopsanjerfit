<?php

namespace App\Http\Controllers\Api\AppMobile;

use App\Http\Controllers\Controller;
use App\Models\Colaborator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Autenticación: login y token
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            Log::warning("Login FAILED for email: {$credentials['email']}");
            return response()->json([
                'message' => 'Credenciales no válidas'
            ], 401);
        }

        $user->update(['last_login' => now()]);
        $token = $user->createToken('app-token')->plainTextToken;

        Log::info("Login SUCCESS for user_id: {$user->id}");

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    /**
     * Cierra la sesión (revoca el token actual)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    /**
     * Devuelve el usuario autenticado (y su colaborador)
     */
    public function user(Request $request)
    {
        $user = $request->user();
        // Opcional: cargar relación colaborator
        $user->load('colaborator');
        return response()->json(['user' => $user]);
    }

    /**
     * Actualiza la foto de perfil del colaborador autenticado.
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png|max:2048',
        ]);

        /** @var User $user */
        $user = $request->user();
        $colab = $user->colaborator;

        // Borra foto anterior si existe
        if ($colab->photo_path) {
            Storage::disk('public')->delete($colab->photo_path);
        }

        // Guarda nueva foto
        $path = $request->file('photo')
                        ->store('avatars', 'public');

        $colab->photo_path = $path;
        $colab->save();

        return response()->json([
            'photo_url' => Storage::url($path),
        ]);
    }

    /**
     * Cambia la contraseña del usuario autenticado.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password'      => 'required|string',
            'new_password'          => 'required|string|min:8|confirmed',
        ]);

        /** @var User $user */
        $user = $request->user();

        // Verifica que la actual coincida
        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual no coincide.'],
            ]);
        }

        // Actualiza a la nueva
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.',
        ]);
    }
}
