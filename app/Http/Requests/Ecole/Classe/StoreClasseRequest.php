<?php

namespace App\Http\Requests\Ecole\Classe;

use App\Rules\ValidSubjectIds;
use Illuminate\Foundation\Http\FormRequest;

class StoreClasseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'niveau' => 'required|string',
            'subject_ids' => ['nullable', 'array', new ValidSubjectIds()],            
            'mots_cles' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required'        => 'Le nom de la classe est requis.',
            'nom.string'          => 'Le nom de la classe doit être une chaîne de caractères.',
            'nom.max'             => 'Le nom de la classe ne doit pas dépasser 255 caractères.',

            'niveau.required'     => 'Le niveau est requis.',
            'niveau.string'       => 'Le niveau doit être une chaîne de caractères.',

            'subject_ids.array'   => 'Les matières doivent être envoyées sous forme de tableau.',
            'mots_cles.array'     => 'Les mots-clés doivent être envoyés sous forme de tableau.',
        ];
    }
}