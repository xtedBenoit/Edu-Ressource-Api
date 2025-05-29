<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Classe extends Model
{

    protected $connection = 'mongodb';
    protected $collection = 'classes';

    protected $fillable = [
        'nom',
        'niveau',
        'subject_ids'
    ];

    protected $casts = [
        'subject_ids' => 'array',
    ];

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function series()
    {
        return $this->hasMany(Serie::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, '_id', 'subject_ids');
    }
}
