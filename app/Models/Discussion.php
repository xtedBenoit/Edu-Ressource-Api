<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Discussion extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $table = 'discussions';

    protected $fillable = [
        'titre',
        'contenu',
        'publique',
        'resource_id',
        'user_id'
    ];

    protected $casts = [
        'publique' => 'boolean'
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->with('auteur');
    }

    public function scopePublic($query)
    {
        return $query->where('publique', true);
    }

    public function scopePrivate($query)
    {
        return $query->where('publique', false);
    }

    public function canAccess(User $user)
    {
        if ($this->publique) {
            return true;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->_id === $this->user_id) {
            return true;
        }

        if ($user->role === 'professeur' && $user->subject_ids->contains($this->resource->subject_id)) {
            return true;
        }

        return false;
    }
}