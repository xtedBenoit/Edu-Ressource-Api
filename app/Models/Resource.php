<?php

namespace App\Models;

use App\Enums\ResourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Resource extends Model
{

    protected $connection = 'mongodb';

    protected $table = 'resources';

    protected $fillable = [
        'titre',
        'description',
        'type', // pdf, image, lien
        'chemin_fichier', // ou lien vers S3
        'type_ressource', // enum: cours, exercice, etc.
        'auteur_id', // ref user
        'classe_id',
        'subject_id',
        'serie_id',
        'mots_cles', // tableau
        'score_confiance',
        'valide',
        'commentaire_validation',
        'valide_par',
        'valide_le',
        'downloads',
        'fichier_hash',
        'commentaire_validation',
        'discussions' // facultatif
    ];

    protected $casts = [
        'type_ressource' => ResourceType::class,
        'validee_auto' => 'boolean',
        'validee_humaine' => 'boolean',
        'mots_cles_detectes' => 'array',
        'score_confiance' => 'float'
    ];

    protected static function booted()
    {
        static::ensureIndexes();
    }

    protected static function ensureIndexes()
    {
        static::raw(function ($collection) {
            $collection->createIndex(
                ['fichier_hash' => 1],
                ['unique' => true, 'sparse' => true] // sparse = ignore les documents sans ce champ
            );
        });
    }


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

    public function comments()
    {
        return $this->hasMany(Message::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function validations()
    {
        return $this->hasMany(ValidationLog::class);
    }

    public function downloadsLogs()
    {
        return $this->hasMany(Download::class);
    }

    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }
}
