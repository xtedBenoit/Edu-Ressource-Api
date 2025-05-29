<?php

namespace App\Http\Controllers\Api\Profile;

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
        return response()->json(auth()->user());
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

        return response()->json([
            'message' => 'Profil mis à jour',
            'user' => $user
        ]);
    }

    /**
     * Supprime le compte de l'utilisateur connecté.
     */
    public function destroy()
    {
        $user = auth()->user();
        $user->delete();

        return response()->json(['message' => 'Compte supprimé']);
    }

    /**
     * Liste des utilisateurs (admin uniquement).
     */
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }
}