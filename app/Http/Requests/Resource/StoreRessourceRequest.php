<?php

namespace App\Http\Requests\Resource;

use Illuminate\Foundation\Http\FormRequest;

class StoreRessourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fichier' => 'required|file|mimes:pdf,jpg,jpeg,png,docx|max:20480',
            'classe_id' => 'required|string|exists:classes,_id',
            'subject_id' => 'required|string|exists:subjects,_id',
            'serie_id' => 'required|string|exists:series,_id',
        ];
    }

    public function messages(): array
    {
        return [
            'titre.required' => 'Le titre est obligatoire.',
            'titre.string' => 'Le titre doit être une chaîne de caractères.',
            'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.',

            'description.string' => 'La description doit être une chaîne de caractères.',
            'fichier.required' => 'Le fichier est requis.',
            'fichier.file' => 'Le fichier doit être un fichier valide.',
            'fichier.mimes' => 'Le fichier doit être de type : pdf, jpg, jpeg, png ou docx.',
            'fichier.max' => 'Le fichier ne doit pas dépasser 20 Mo.',

            'classe_id.required' => 'La classe est obligatoire.',
            'classe_id.string' => 'L\'identifiant de la classe doit être une chaîne.',
            'classe_id.exists' => 'La classe sélectionnée est invalide.',

            'subject_id.required' => 'La matière est obligatoire.',
            'subject_id.string' => 'L\'identifiant de la matière doit être une chaîne.',
            'subject_id.exists' => 'La matière sélectionnée est invalide.',

            'serie_id.required' => 'La série est obligatoire.',
            'serie_id.string' => 'L\'identifiant de la série doit être une chaîne.',
            'serie_id.exists' => 'La série sélectionnée est invalide.',
        ];
    }
}
