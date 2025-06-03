<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use MongoDB\BSON\ObjectId;

class ValidMongoIdExists implements InvokableRule
{
    protected string $collection;
    protected string $field;

    public function __construct(string $collection, string $field = '_id')
    {
        $this->collection = $collection;
        $this->field = $field;
    }

    public function __invoke($attribute, $value, $fail)
    {
        if (!ObjectId::isValid($value)) {
            $fail("L'identifiant fourni pour {$attribute} est invalide.");
            return;
        }

        $modelClass = "\\App\\Models\\" . ucfirst($this->collection);

        if (!class_exists($modelClass)) {
            $fail("La ressource associée à {$attribute} est introuvable.");
            return;
        }

        if (!$modelClass::where($this->field, new ObjectId($value))->exists()) {
            $fail("L'identifiant fourni pour {$attribute} est introuvable.");
        }
    }
}
