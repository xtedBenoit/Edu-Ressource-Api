<?php

namespace App\Http\Controllers\Api\Profile;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Retourne le profil de l'utilisateur connecté.
     */
    public function me(Request $request)
    {
        return ApiResponse::success(auth()->user());
    }

    /**
     * Met à jour les informations de l'utilisateur.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'nullable|string',
            'username' => 'nullable|string|unique:users,username,' . $user->_id,
            'bio' => 'nullable|string',
            'profile_image' => 'nullable|string',
        ]);

        $user->update($validated);

        return ApiResponse::success([
            'user' => $user
        ], 'Profil mis à jour avec succès');
    }

    /**
     * Supprime le compte de l'utilisateur connecté.
     */
    public function destroy()
    {
        $user = auth()->user();
        $user->delete();

        return ApiResponse::success(null, 'Compte supprimé avec succès');
    }

    /**
     * Liste des utilisateurs (admin uniquement).
     */
    public function index()
    {
        $users = User::all();
        return ApiResponse::success($users);
    }
}
