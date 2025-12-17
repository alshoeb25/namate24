<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasRoles;

    protected $fillable = ['name','email','phone','password','avatar','role','phone_otp','phone_otp_expires_at','phone_verified_at','email_verified_at','email_verification_token','email_verification_token_expires_at'];

    protected $hidden = ['password'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_token_expires_at' => 'datetime',
    ];

    public function tutor(): HasOne
    {
        return $this->hasOne(Tutor::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(StudentRequirement::class, 'student_id');
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * Get primary role of user
     */
    public function getUserRole(): ?string
    {
        return $this->roles->pluck('name')->first();
    }

    /**
     * Get avatar URL attribute
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            // If avatar is a full URL (e.g., from Google), return as is
            if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
                return $this->avatar;
            }
            // If it's a local file path
            return asset('storage/' . $this->avatar);
        }
        
        // Default avatar
        return asset('images/default-avatar.png');
    }

}