<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $connection = 'mongodb';

    protected $table = 'users';

    /**
     * Retourne l'identifiant JWT
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Retourne les claims personnalisés du JWT
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'bio',
        'password',
        'role',
        'profile_image',
        'refresh_token',
        'refresh_token_expiry',
        'reset_token',
        'reset_token_expiry',
        'downloads_remaining',
        'downloads_reset_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'refresh_token',
        'refresh_token_expiry',
        'reset_token',
        'reset_token_expiry',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'downloads_reset_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'password' => 'string',
        'refresh_token_expiry' => 'datetime',
        'reset_token_expiry' => 'datetime',
    ];

    /**
     * Vérifie si le refresh token est valide
     */
    public function isRefreshTokenValid(): bool
    {
        return $this->refresh_token && $this->refresh_token_expiry > now();
    }

    /**
     * Vérifie si le reset token est valide
     */
    public function isResetTokenValid(): bool
    {
        return $this->reset_token && $this->reset_token_expiry > now();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function resetDownloadQuotaIfNeeded()
    {
        $now = now();

        if (is_null($this->downloads_reset_at) || $this->downloads_reset_at->lt($now->startOfMonth())) {
            $this->downloads_remaining = 5;
            $this->downloads_reset_at = $now;
            $this->save();
        }
    }

    public function decrementDownloadQuota()
    {
        $this->downloads_remaining = max(0, $this->downloads_remaining - 1);
        $this->save();
    }

    public function addDownloadBonus(int $amount)
    {
        $this->downloads_remaining += $amount;
        $this->save();
    }


}
