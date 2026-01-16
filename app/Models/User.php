<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements JWTSubject, FilamentUser
{
    use Notifiable, HasRoles;

    protected $fillable = ['name','email','phone','country_code','city','area','address','lat','lng','country','country_iso','password',
    'avatar','role','phone_otp','phone_otp_expires_at','phone_verified_at','email_verified_at',
    'email_verification_token','email_verification_token_expires_at','coins','referral_code','referred_by',
    'is_disabled','disabled_reason','disabled_by','disabled_at'];

    protected $hidden = ['password'];

    protected $appends = ['avatar_url'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_token_expires_at' => 'datetime',
        'coins' => 'integer',
        'is_disabled' => 'boolean',
        'disabled_at' => 'datetime',
    ];

    public function tutor(): HasOne
    {
        return $this->hasOne(Tutor::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(StudentRequirement::class, 'student_id');
    }

    public function disabledBy()
    {
        return $this->belongsTo(User::class, 'disabled_by');
    }

    public function coinTransactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(UserActivity::class);
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
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
            return url('storage/' . $this->avatar);
        }
        
        // Default avatar - reliable placeholder
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name ?? 'User') . '&size=200&background=ec4899&color=ffffff';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() !== 'admin') {
            return false;
        }

        return $this->hasAnyRole([
            'super_admin',
            'coin_wallet_admin',
            'student_admin',
            'tutor_admin',
            'enquiries_admin',
            'reviews_admin',
            'service_admin',
            'admin', // Keep backward compatibility
        ]);
    }

}