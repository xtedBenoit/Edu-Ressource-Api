<?php
namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvatarRequest extends FormRequest
{
    /**
     * Autorise cette requête.
     */
    public function authorize(): bool
    {
        return true; // tu peux ajouter une logique d'autorisation ici
    }

    /**
     * Règles de validation.
     */
    public function rules(): array
    {
        return [
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'avatar_url' => 'nullable|url',
        ];
    }

    /**
     * Messages personnalisés.
     */
    public function messages(): array
    {
        return [
            'avatar.image' => 'Le fichier doit être une image.',
            'avatar.mimes' => 'L’image doit être au format JPEG, PNG, JPG ou WEBP.',
            'avatar.max' => 'La taille de l’image ne doit pas dépasser 2 Mo.',
            'avatar_url.url' => 'L’URL de l’avatar doit être un lien valide.',
        ];
    }
}
