<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Like extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'likes';

    protected $fillable = [
        'resource_id',
        'auteur_id',
    ];


    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }
}