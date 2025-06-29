<?php

namespace App\Models;

use App\Enums\ResourceType;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;

class Resource extends Model
{

    protected $connection = 'mongodb';
    protected $table = 'resources';

    protected $fillable = [
        'titre',
        'description',
        'chemin_fichier',
        'type_ressource',
        'auteur_id',
        'classe_id',
        'subject_id',
        'serie_id',
        'mots_cles',
        'score_confiance',
        'valide',
        'commentaire_validation',
        'valide_par',
        'valide_le',
        'downloads',
        'fichier_hash',
        'commentaire_validation',
        'discussions'
    ];

    protected $casts = [
        'type_ressource' => ResourceType::class,
        'validee_auto' => 'boolean',
        'validee_humaine' => 'boolean',
        'mots_cles_detectes' => 'array',
        'score_confiance' => 'float'
    ];

    protected static function booted(): void
    {
        static::ensureIndexes();
    }

    protected static function ensureIndexes(): void
    {
        static::raw(function ($collection) {
            $collection->createIndex(
                ['fichier_hash' => 1],
                ['unique' => true, 'sparse' => true] // sparse = ignore les documents sans ce champ
            );
        });

        static::raw(function ($collection) {
            $collection->createIndex(['mots_cles' => 'text']);
        });
    }


    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function serie(): BelongsTo
    {
        return $this->belongsTo(Serie::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function validations(): HasMany
    {
        return $this->hasMany(ValidationLog::class);
    }

    public function downloadsLogs(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    public function getLikesCountAttribute(): int
    {
        return $this->likes()->count();
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }
}
