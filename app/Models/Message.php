<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

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
    private string $user_id;

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function canModify(User $user): bool
    {
        return $user->_id === $this->user_id || $user->role === 'admin';
    }

}
