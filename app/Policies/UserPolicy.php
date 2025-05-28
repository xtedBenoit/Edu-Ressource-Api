<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; // À personnaliser selon vos besoins
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id; // L'utilisateur ne peut voir que son propre profil
    }

    public function create(User $user): bool
    {
        return false; // Seuls les administrateurs peuvent créer des utilisateurs
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id; // L'utilisateur ne peut modifier que son propre profil
    }

    public function delete(User $user, User $model): bool
    {
        return false; // Seuls les administrateurs peuvent supprimer des utilisateurs
    }
}