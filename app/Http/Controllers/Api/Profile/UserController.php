<?php

namespace App\Http\Controllers\Api\Profile;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\Providers\Auth;

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

    /**
     * Changer de mot de passe.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return ApiResponse::error('Mot de passe actuel incorrect.', null, 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return ApiResponse::success(null, 'Mot de passe mis à jour avec succès.');
    }


    /**
     * Mettre à jour ou ajouter une photo de profil pour l'utilisateur connecté.
     */
    public function uploadAvatar(Request $request)
    {
        $user = auth()->user();

        // Validation conditionnelle
        $request->validate([
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'avatar_url' => 'nullable|url',
        ]);

        if ($request->hasFile('avatar')) {
            // Traitement upload fichier
            $filename = uniqid() . '.' . $request->avatar->extension();
            $path = $request->avatar->storeAs('images/avatars', $filename, 'public');
            $user->profile_image = '/storage/' . $path;
        } elseif ($request->filled('avatar_url')) {
            // Utiliser l'URL
            $user->profile_image = $request->avatar_url;
        } else {
            return ApiResponse::error('Veuillez fournir un fichier avatar ou une URL valide.', null, 422);
        }

        $user->save();

        return ApiResponse::success([
            'profile_image' => $user->profile_image
        ], 'Image de profil mise à jour avec succès.');
    }

    /**
     * Supprime l'avatar de l'utilisateur.
     */ 
    public function deleteAvatar()
    {
        $user = auth()->user();

        // Option 1 : juste supprimer la référence (avatar remis à null)
        $user->profile_image = null;

        // Option 2 : si tu as une image par défaut, tu peux la mettre ici
        // $user->profile_image = '/images/default-avatar.png';

        $user->save();

        return ApiResponse::success(null, 'Image de profil supprimée avec succès.');
    }

    /**
     * Retourne l'historique des actions de l'utilisateur connecté.
     */
    public function getActionsHistory()
    {
        $user = auth()->user();

        return ApiResponse::success([
            'actions_history' => array_reverse($user->actions_history ?? []), // dernier en haut
        ]);
    }
}
