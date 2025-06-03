<?php
// app/Http/Controllers/QuotaController.php

namespace App\Http\Controllers\Other;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParrainageController extends Controller
{
    public function utiliserCodeParrain(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = Auth::user();

        // Vérifie que le code n'est pas le sien
        if ($user->parrain_code === $request->code) {
            return ApiResponse::error('Vous ne pouvez pas utiliser votre propre code.', 400);
        }

        // Vérifie s’il a déjà utilisé un code
        if (!empty($user->parrain_utilise)) {
            return ApiResponse::error('Vous avez déjà utilisé un code de parrainage.', 400);
        }

        // Cherche le parrain
        $parrain = User::where('parrain_code', $request->code)->first();

        if (!$parrain) {
            return ApiResponse::error('Code de parrainage invalide.', 404);
        }

        // Attribution des bonus
        $parrain->increment('downloads_remaining', 5);
        $user->increment('downloads_remaining', 5);

        $parrain->logAction('parrainage', "A gagné 5 téléchargements grâce à {$user->name}", 5);
        $user->logAction('parrainage', "A utilisé le code de {$parrain->name}", 5);

        // Empêche réutilisation
        $user->parrain_utilise = $request->code;
        $user->save();

        return ApiResponse::success([
            'message' => 'Code de parrainage appliqué avec succès. +5 téléchargements !',
            'downloads_remaining' => $user->downloads_remaining,
        ]);
    }
}
