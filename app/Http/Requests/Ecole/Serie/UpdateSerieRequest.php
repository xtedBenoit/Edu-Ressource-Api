<?php

namespace App\Http\Requests\Ecole\Serie;

use App\Rules\ValidMongoIdExists;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSerieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'classe_id'   => ['sometimes', 'required', 'string', new ValidMongoIdExists('classe')],
            'subject_id'  => ['sometimes', 'required', 'string', new ValidMongoIdExists('subject')],
            'mots_cles' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'titre.required'       => 'Le titre est requis lorsqu’il est présent.',
            'titre.string'         => 'Le titre doit être une chaîne de caractères.',
            'titre.max'            => 'Le titre ne doit pas dépasser 255 caractères.',

            'description.string'   => 'La description doit être une chaîne de caractères.',

            'classe_id.required'   => 'La classe associée est requise lorsqu’elle est présente.',
            'classe_id.string'     => 'L\'identifiant de la classe doit être une chaîne de caractères.',

            'subject_id.required'  => 'La matière associée est requise lorsqu’elle est présente.',
            'subject_id.string'    => 'L\'identifiant de la matière doit être une chaîne de caractères.',

            'mots_cles.array'      => 'Les mots-clés doivent être envoyés sous forme de tableau.',
        ];
    }
}