<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ApiResponse;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'unique:users,username'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'string', 'in:user,admin'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Le nom est requis',
            'name.max'          => 'Le nom ne peut pas dépasser :max caractères',
            'username.required' => 'Le nom d\'utilisateur est requis',
            'username.unique'   => 'Ce nom d\'utilisateur est déjà utilisé',
            'email.required'    => 'L\'adresse email est requise',
            'email.email'       => 'L\'adresse email doit être valide',
            'email.unique'      => 'Cette adresse email est déjà utilisée',
            'password.required' => 'Le mot de passe est requis',
            'password.min'      => 'Le mot de passe doit contenir au moins :min caractères',
            'password.confirmed'=> 'La confirmation du mot de passe ne correspond pas',
            'role.required'     => 'Le rôle est requis',
            'role.in'           => 'Le rôle doit être soit user soit admin',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
