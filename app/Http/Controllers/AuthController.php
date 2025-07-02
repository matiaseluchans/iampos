<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $tokenResult = $user->createToken('auth_token', ['*'], now()->addHours(12));

        return response()->json([
            'access_token' => $tokenResult->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $tokenResult->accessToken->expires_at->toDateTimeString(),
            'user' => $user,
        ]);
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        // Revoca el token usado en la petición actual
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
    public function getUser(Request $request)
    {
        return new UserResource($request->user()->load([
            'roles',
            'tenant'
        ]));
    }

    public function validateToken(Request $request)
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        // Si no hay token o ya expiró
        if (!$currentToken || ($currentToken->expires_at && $currentToken->expires_at->isPast())) {
            return response()->json([
                'valid' => false,
                'message' => 'Token has expired'
            ]);
        }

        // Si el token es válido, renovamos por 30 minutos más
        $newExpiration = now()->addHours(12);
        $currentToken->forceFill([
            'expires_at' => $newExpiration
        ])->save();

        return response()->json([
            'valid' => true,
            'expires_at' => $newExpiration->toDateTimeString(),
            'user' => $user,
            'message' => 'Token is valid and has been renewed'
        ]);
    }
}
