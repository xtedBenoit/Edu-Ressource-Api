<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;

class Serie extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'series';

    protected $fillable = [
        'titre',
        'description',
        'classe_id',
        'subject_id',
        'mots_cles',
    ];

    protected $casts = [
        'mots_cles' => 'array'
    ];

    protected static function booted(): void
    {
        static::ensureIndexes();
    }

    protected static function ensureIndexes(): void
    {
        static::raw(function ($collection) {
            $collection->createIndex(['mots_cles' => 'text']);
        });
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }

    public function getMotsClesArrayAttribute(): array
    {
        $mots = $this->attributes['mots_cles'] ?? [];

        if (is_array($mots)) {
            return $mots;
        }

        if (is_string($mots)) {
            return json_decode($mots, true) ?? [];
        }

        return [];
    }
}
