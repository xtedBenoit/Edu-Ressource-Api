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
    private mixed $mots_cles;

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
        return is_array($this->mots_cles) ? $this->mots_cles : (
        is_string($this->mots_cles) ? json_decode($this->mots_cles, true) ?? [] : []
        );
    }
}
