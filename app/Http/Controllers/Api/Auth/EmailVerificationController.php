<?php

namespace App\Http\Controllers\Api\Auth;


use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{
    /**
     * Envoie un code de vérification à l'adresse email fournie.
     * @param Request $request
     * @return JsonResponse
     */
    public function sendCode(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        if (User::where('email', $request->email)->exists()) {
            return ApiResponse::error("Cette adresse email est déjà utilisée.", 409);
        }

        $code = EmailVerification::generateCode($request->email);

        Mail::send('emails.verify-email', [
            'code' => $code,
            'firstname' => 'utilisateur',
        ], function ($message) use ($request) {
            $message->to($request->email)->subject('Vérification de votre adresse email');
        });

        return ApiResponse::success(null, 'Code de vérification envoyé à l\'adresse email.');
    }

    /**
     * Vérifie le code de vérification fourni par l'utilisateur.
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $verification = EmailVerification::where('email', $request->email)
            ->where('code', $request->code)
            ->first();

        if (!$verification) {
            return ApiResponse::error('Code invalide', 422);
        } elseif ($verification->is_verified) {
            return ApiResponse::error('Email déjà vérifié.', 400);
        } elseif (now()->greaterThan($verification->expires_at)) {
            return ApiResponse::error('Code expiré.', 400);
        }

        $verification->is_verified = true;
        $verification->save();

        return ApiResponse::success(null, 'Email vérifié avec succès.');
    }
}
