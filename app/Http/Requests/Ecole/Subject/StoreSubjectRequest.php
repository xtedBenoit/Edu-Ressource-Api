<?php

namespace App\Http\Requests\Ecole\Subject;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'code' => 'required|string|unique:subjects,code',
            'mots_cles' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required'     => 'Le nom est requis.',
            'nom.string'       => 'Le nom doit être une chaîne de caractères.',
            'nom.max'          => 'Le nom ne doit pas dépasser 255 caractères.',

            'description.string' => 'La description doit être une chaîne de caractères.',

            'code.required'    => 'Le code est requis.',
            'code.string'      => 'Le code doit être une chaîne de caractères.',
            'code.unique'      => 'Ce code est déjà utilisé pour une autre matière.',

            'mots_cles.array'  => 'Les mots-clés doivent être envoyés sous forme de tableau.',
        ];
    }
}
