<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class ValidationLog extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'validation_logs';

    protected $fillable = [
        'resource_id',
        'validateur_id',
        'type_validation',
        'status',
        'score_confiance',
        'mots_cles',
        'commentaire'
    ];

    protected $casts = [
        'status' => 'boolean',
        'score_confiance' => 'float',
        'mots_cles' => 'array'
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }

    public static function createAutoValidation($resourceId, $status, $score, $keywords = [])
    {
        return self::create([
            'resource_id' => $resourceId,
            'type_validation' => 'auto',
            'status' => $status,
            'score_confiance' => $score,
            'mots_cles' => $keywords
        ]);
    }

    public static function createHumanValidation($resourceId, $validateurId, $status, $commentaire = null)
    {
        return self::create([
            'resource_id' => $resourceId,
            'validateur_id' => $validateurId,
            'type_validation' => 'humaine',
            'status' => $status,
            'commentaire' => $commentaire
        ]);
    }

    public function getValidateurName()
    {
        if ($this->type_validation === 'auto') {
            return 'Système';
        }

        return $this->validateur ? $this->validateur->name : 'Inconnu';
    }
}