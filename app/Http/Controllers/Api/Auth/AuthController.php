<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Enregistrement d'un nouvel utilisateur
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'username' => 'required|string|unique:users',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => 'user',
            'refresh_token' => Str::random(64),
            'refresh_token_expiry' => now()->addDays(7),
        ]);

        $token = JWTAuth::fromUser($user);

        return ApiResponse::success([
            'access_token' => $token,
            'refresh_token' => $user->refresh_token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user
        ], 'Utilisateur enregistré avec succès');
    }

    /**
     * Connexion de l'utilisateur
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return ApiResponse::unauthorized('Identifiants invalides');
            }
        } catch (JWTException $e) {
            return ApiResponse::error('Erreur de connexion', null, 500);
        }

        $user = auth()->user();
        $user->refresh_token = Str::random(64);
        $user->refresh_token_expiry = now()->addDays(7);
        $user->save();

        return ApiResponse::success([
            'access_token' => $token,
            'refresh_token' => $user->refresh_token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user
        ], 'Connexion réussie');
    }

    /**
     * Rafraîchir le token
     */
    public function refresh(Request $request)
    {
        $refreshToken = $request->input('refresh_token');
        $user = User::where('refresh_token', $refreshToken)->first();

        if (!$user || !$user->isRefreshTokenValid()) {
            return ApiResponse::unauthorized('Refresh token invalide ou expiré');
        }

        $newToken = JWTAuth::fromUser($user);

        // Nouveau refresh token
        $user->refresh_token = Str::random(64);
        $user->refresh_token_expiry = now()->addDays(7);
        $user->save();

        return ApiResponse::success([
            'access_token' => $newToken,
            'refresh_token' => $user->refresh_token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ], 'Token rafraîchi avec succès');
    }

    /**
     * Déconnexion (invalider le token actuel)
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return ApiResponse::success(null, 'Déconnexion réussie');
        } catch (JWTException $e) {
            return ApiResponse::error('Erreur lors de la déconnexion', null, 500);
        }
    }

    /**
     * Récupérer les infos de l'utilisateur connecté
     */
    public function me()
    {
        return ApiResponse::success(auth()->user());
    }
}
