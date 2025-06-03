<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

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

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function series()
    {
        return $this->hasMany(Serie::class);
    }

    public function classes()
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
