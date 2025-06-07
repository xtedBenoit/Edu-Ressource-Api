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
            'firstname' => ['required', 'string', 'max:255'],
            'lastname'  => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'exists:email_verifications,email'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'role'      => ['required', 'string', 'in:user,admin'],
        ];
    }

    public function messages(): array
    {
        return [
            'firstname.required' => 'Le prénom est requis',
            'lastname.required'  => 'Le nom est requis',
            'email.required'     => 'L\'adresse email est requise',
            'email.email'        => 'L\'adresse email doit être valide',
            'email.exists'       => 'Veuillez d\'abord valider votre adresse email',
            'password.required'  => 'Le mot de passe est requis',
            'password.min'       => 'Le mot de passe doit contenir au moins :min caractères',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas',
            'role.required'      => 'Le rôle est requis',
            'role.in'            => 'Le rôle doit être soit user soit admin',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
