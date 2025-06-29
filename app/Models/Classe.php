<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\HasMany;

class Classe extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'classes';

    protected $fillable = [
        'nom',
        'niveau',
        'subject_ids',
        'mots_cles'
    ];

    protected $casts = [
        'mots_cles' => 'array',
        'subject_ids' => 'array',
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

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, '_id', 'subject_ids');
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
