<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'resources';

    protected $fillable = [
        'titre',
        'description',
        'type_fichier',
        'url',
        'auteur_id',
        'classe_id',
        'serie_id',
        'subject_id',
        'validee_auto',
        'validee_humaine',
        'mots_cles_detectes',
        'score_confiance'
    ];

    protected $casts = [
        'validee_auto' => 'boolean',
        'validee_humaine' => 'boolean',
        'mots_cles_detectes' => 'array',
        'score_confiance' => 'float'
    ];

    public function auteur()
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function serie()
    {
        return $this->belongsTo(Serie::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function discussion()
    {
        return $this->hasOne(Discussion::class);
    }

    public function validations()
    {
        return $this->hasMany(ValidationLog::class);
    }
}