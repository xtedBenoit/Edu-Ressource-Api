<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use MongoDB\Laravel\Eloquent\Model;

class Message extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'messages';

    protected $fillable = [
        'contenu',
        'resource_id',
        'user_id'
    ];


    protected $with = ['auteur'];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function canModify(User $user)
    {
        return $user->_id === $this->user_id || $user->role === 'admin';
    }

}