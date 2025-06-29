<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\HasMany;

class Subject extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'subjects';

    protected $fillable = [
        'nom',
        'description',
        'code',
        'mots_cles'
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

    public function series(): HasMany
    {
        return $this->hasMany(Serie::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(Classe::class, 'subject_ids', '_id');
    }

    // Accesseur personnalisé si jamais tu veux avoir une clé virtuelle "mots_cles_array"
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
