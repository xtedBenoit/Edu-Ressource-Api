<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users')->ignore($this->user()->id),
            ],
            'current_password' => ['required_with:password', 'current_password'],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
            'avatar' => ['sometimes', 'image', 'max:2048'], // 2MB max
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.max' => 'Le nom ne peut pas dépasser :max caractères',
            'email.email' => 'L\'adresse email doit être valide',
            'email.unique' => 'Cette adresse email est déjà utilisée',
            'current_password.required_with' => 'Le mot de passe actuel est requis pour changer de mot de passe',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins :min caractères',
            'password.confirmed' => 'La confirmation du nouveau mot de passe ne correspond pas',
            'avatar.image' => 'Le fichier doit être une image',
            'avatar.max' => 'L\'image ne doit pas dépasser 2Mo',
        ];
    }
}