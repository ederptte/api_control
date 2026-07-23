<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Maneja el inicio de sesión y retorna un token de Sanctum.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Buscamos el usuariwhere('email', $request->email)->first();o
        $user = User::query()->where('email', $request->email)->first();

        /** @var \App\Models\User|null $user */
        // Validamos si existe y la contraseña coincide
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Las credenciales proporcionadas son incorrectas.'
            ], 401);
        }

        /** @var \App\Models\User $user */
        // Creamos el token seguro
        $token = $user->createToken('auth_token')->plainTextToken;

        // Retornamos la respuesta mapeando los campos directamente para evitar advertencias
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->getAttribute('id'),
                'name' => $user->getAttribute('name'),
                'email' => $user->getAttribute('email')
            ]
        ]);
    }

    /**
     * Cierra la sesión revocando el token actual.
     */
    public function logout(Request $request)
    {
        // Obtenemos el token actual de la petición
        $token = $request->user()?->currentAccessToken();

        // Le aseguramos a VS Code que es un token válido de Sanctum antes de borrarlo
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Sesión cerrada con éxito.'
        ]);
    }
}