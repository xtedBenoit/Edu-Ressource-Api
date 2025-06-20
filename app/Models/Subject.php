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
    private mixed $mots_cles;

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

    public function getMotsClesArrayAttribute(): array
    {
        return is_array($this->mots_cles) ? $this->mots_cles : (
        is_string($this->mots_cles) ? json_decode($this->mots_cles, true) ?? [] : []
        );
    }
}
