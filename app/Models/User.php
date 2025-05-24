<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'mobile',
        'password',
        'mobile_verified_at',
        'wallet_balance',
        'referrer_id',
        'referral_code',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'mobile_verified_at' => 'datetime',
            'password' => 'hashed',
            'wallet_balance' => 'decimal:0',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function families()
    {
        return $this->hasMany(Family::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function questionsByStage($stage): HasMany
    {
        return $this->hasMany(TestQuestion::class)
            ->where('stage', $stage)
            ->orderBy('order', 'asc');
        // حذف sort از ترتیب‌سازی
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(TestPurchase::class);
    }

    public function purchasers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'test_purchases')
            ->withPivot(['amount_paid', 'status', 'started_at', 'completed_at'])
            ->withTimestamps();
    }
}
