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

    public function enseignants()
    {
        return $this->hasMany(User::class, 'subject_ids', '_id')
            ->where('role', 'enseignant');
    }
}