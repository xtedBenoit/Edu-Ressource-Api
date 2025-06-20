<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Mail\ResetCodeMail;
use App\Models\EmailVerification;
use App\Models\User;
use App\Traits\GeneratesUsername;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use GeneratesUsername;

    /**
     * Enregistrement d'un nouvel utilisateur
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $verification = EmailVerification::where('email', $request->email)->first();
        if (!$verification || !$verification->is_verified) {
            return ApiResponse::error("L'email n'a pas été vérifié.", 403);
        }

        $username = $this->generateUniqueUsername($request->email);

        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'username' => $username,
            'password' => Hash::make($request->password),
            'role' => auth()->user() && auth()->user()->role == 'admin' ? $request->role : 'user',
            'refresh_token' => Str::random(64),
            'refresh_token_expiry' => now()->addDays(7),
        ]);

        $verification->delete(); // Supprimer la vérification après l'enregistrement

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
     * Connexion d'un utilisateur
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
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
     * Rafraîchissement du token JWT
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = $request->input('refresh_token');
        $user = User::where('refresh_token', $refreshToken)->first();

        if (!$user || !$user->isRefreshTokenValid()) {
            return ApiResponse::unauthorized('Refresh token invalide ou expiré');
        }

        $newToken = JWTAuth::fromUser($user);

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
     * Déconnexion de l'utilisateur
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return ApiResponse::success(null, 'Déconnexion réussie');
        } catch (JWTException $e) {
            return ApiResponse::error('Erreur lors de la déconnexion', null, 500);
        }
    }

    /**
     * Envoi du code de réinitialisation de mot de passe
     * @param Request $request
     * @return JsonResponse
     */
    public function sendResetCode(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $code = rand(100000, 999999);

        $user = User::where('email', $request->email)->first();
        $user->reset_token = $code;
        $user->reset_token_expiry = now()->addMinutes(15);
        $user->save();

        Mail::to($user->email)->send(new ResetCodeMail($code, $user));

        return ApiResponse::success(null, 'Code de réinitialisation envoyé par email');
    }

    /**
     * Réinitialisation du mot de passe
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'code' => ['required', 'integer', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $code = (int)$request->code;

        $user = User::where('email', $request->email)
            ->where('reset_token', $code)
            ->first();

        if (!$user) {
            return ApiResponse::error('Code de réinitialisation invalide', null, 400);
        } elseif (!$user->reset_token_expiry || now()->greaterThan($user->reset_token_expiry)) {
            return ApiResponse::error('Code expiré. Veuillez demander un nouveau code.', null, 400);
        }

        $user->password = Hash::make($request->password);
        $user->reset_token = null;
        $user->reset_token_expiry = null;
        $user->save();

        return ApiResponse::success(null, 'Mot de passe réinitialisé avec succès.');
    }
}
