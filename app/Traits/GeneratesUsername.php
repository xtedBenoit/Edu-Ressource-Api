<?php

namespace App\Traits;

use App\Models\User;

trait GeneratesUsername
{
    /**
     * Génère un nom d'utilisateur unique basé sur l'email.
     */
    public function generateUniqueUsername(string $email): string
    {
        $base = explode('@', $email)[0];
        $username = $base;
        $i = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . $i;
            $i++;
        }

        return $username;
    }
}
