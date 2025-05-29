<?php

namespace App\Services\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'visiteur',
        ]);

        return $this->generateTokens($user);
    }

    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        return $this->generateTokens($user);
    }

    public function refreshToken(string $refreshToken): array
    {
        $user = User::where('refresh_token', $refreshToken)
            ->where('refresh_token_expiry', '>', now())
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'refresh_token' => ['Le refresh token est invalide ou expiré.'],
            ]);
        }

        return $this->generateTokens($user);
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
        $user->update([
            'refresh_token' => null,
            'refresh_token_expiry' => null,
        ]);
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Le mot de passe actuel est incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Révoquer tous les tokens après changement de mot de passe
        $this->logout($user);
    }

    public function forgotPassword(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Aucun utilisateur trouvé avec cette adresse email.'],
            ]);
        }

        $token = Str::random(60);
        $user->update([
            'reset_token' => $token,
            'reset_token_expiry' => Carbon::now()->addHours(24),
        ]);

        // Ici, vous pouvez envoyer un email avec le token
    }

    public function resetPassword(string $token, string $newPassword): void
    {
        $user = User::where('reset_token', $token)
            ->where('reset_token_expiry', '>', now())
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'token' => ['Le token de réinitialisation est invalide ou expiré.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword),
            'reset_token' => null,
            'reset_token_expiry' => null,
        ]);

        // Révoquer tous les tokens après réinitialisation du mot de passe
        $this->logout($user);
    }

    private function generateTokens(User $user): array
    {
        // Générer le token d'accès
        $token = $user->createToken('auth_token')->plainTextToken;

        // Générer le refresh token
        $refreshToken = Str::random(60);
        $user->update([
            'refresh_token' => $refreshToken,
            'refresh_token_expiry' => Carbon::now()->addDays(30),
        ]);

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'refresh_token' => $refreshToken,
            'expires_in' => config('sanctum.expiration') * 60, // en secondes
        ];
    }
}