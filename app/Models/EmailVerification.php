<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Carbon;

class EmailVerification extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'email_verifications';

    protected $fillable = [
        'email', 'code', 'is_verified', 'expires_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'expires_at'  => 'datetime',
    ];

    public $timestamps = true;

    public static function generateCode(string $email): string
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
