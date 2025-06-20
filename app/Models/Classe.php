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
        return is_array($this->mots_cles) ? $this->mots_cles : (
        is_string($this->mots_cles) ? json_decode($this->mots_cles, true) ?? [] : []
        );
    }
}
