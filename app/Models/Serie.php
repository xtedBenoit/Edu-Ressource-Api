<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

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

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function auteur()
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
