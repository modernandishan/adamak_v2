<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'payment_gateway',
        'payment_ref_id',
        'payment_data',
        'status',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:0',
        'balance_before' => 'decimal:0',
        'balance_after' => 'decimal:0',
        'payment_data' => 'array',
    ];

    const TYPE_DEPOSIT = 'deposit';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_REFERRAL_BONUS = 'referral_bonus';
    const TYPE_ADMIN_ADJUSTMENT = 'admin_adjustment';
    const TYPE_REFUND = 'refund';

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    const GATEWAY_ZARINPAL = 'zarinpal';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            self::TYPE_DEPOSIT => 'success',
            self::TYPE_PURCHASE => 'danger',
            self::TYPE_REFERRAL_BONUS => 'info',
            self::TYPE_ADMIN_ADJUSTMENT => 'warning',
            self::TYPE_REFUND => 'secondary',
            default => 'primary',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_COMPLETED => 'success',
            self::STATUS_PENDING => 'warning',
            self::STATUS_FAILED => 'danger',
            default => 'secondary',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_DEPOSIT => 'شارژ کیف پول',
            self::TYPE_PURCHASE => 'خرید',
            self::TYPE_REFERRAL_BONUS => 'پاداش معرفی',
            self::TYPE_ADMIN_ADJUSTMENT => 'تنظیم توسط مدیر',
            self::TYPE_REFUND => 'بازگشت وجه',
            default => 'نامشخص',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_COMPLETED => 'موفق',
            self::STATUS_PENDING => 'در انتظار',
            self::STATUS_FAILED => 'ناموفق',
            default => 'نامشخص',
        };
    }
}
