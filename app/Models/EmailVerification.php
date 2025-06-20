<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use MongoDB\Laravel\Eloquent\Model;

class EmailVerification extends Model
{
    public $timestamps = true;
    protected $connection = 'mongodb';
    protected $table = 'email_verifications';
    protected $fillable = [
        'email', 'code', 'is_verified', 'expires_at',
    ];
    protected $casts = [
        'is_verified' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public static function generateCode(string $email): int
    {
        $code = rand(100000, 999999);
        static::updateOrCreate(
            ['email' => $email],
            [
                'code' => $code,
                'is_verified' => false,
                'expires_at' => Carbon::now()->addMinutes(15),
            ]
        );
        return $code;
    }
}
