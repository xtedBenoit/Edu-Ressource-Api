<?php

// app/Models/Download.php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class Download extends Model
{

    protected $connection = 'mongodb';
    protected $table = 'downloads';

    protected $fillable = [
        'user_id',
        'resource_id',
        'downloaded_at'
    ];

    protected $casts = [
        'downloaded_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
