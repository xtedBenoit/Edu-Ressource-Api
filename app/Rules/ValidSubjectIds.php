<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use MongoDB\BSON\ObjectId;
use App\Models\Subject;

class ValidSubjectIds implements Rule
{
    public function passes($attribute, $value)
    {
        if (!is_array($value)) return false;

        foreach ($value as $id) {
            if (!ObjectId::isValid($id)) return false;

            if (!Subject::where('_id', new ObjectId($id))->exists()) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return "Certains identifiants des matières sont invalides ou inexistants.";
    }
}
