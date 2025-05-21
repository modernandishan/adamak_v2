<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Test extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'test_category_id',
        'title',
        'slug',
        'short_description',
        'description',
        'featured_image',
        'price',
        'discounted_price',
        'requires_family',
        'is_multi_stage',
        'is_active',
        'estimated_completion_time',
        'order',
        'settings',
    ];

    protected $casts = [
        'price' => 'decimal:0',
        'discounted_price' => 'decimal:0',
        'requires_family' => 'boolean',
        'is_multi_stage' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($test) {
            if (empty($test->slug)) {
                $test->slug = Str::slug($test->title);
            }
        });

        static::updating(function ($test) {
            if ($test->isDirty('title') && !$test->isDirty('slug')) {
                $test->slug = Str::slug($test->title);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TestCategory::class, 'test_category_id');
    }

    public function getEffectivePriceAttribute()
    {
        return $this->discounted_price ?? $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        if (!$this->discounted_price || $this->price == 0) {
            return 0;
        }

        return round((1 - ($this->discounted_price / $this->price)) * 100);
    }

    public function getStagesCountAttribute()
    {
        if (!$this->is_multi_stage) {
            return 1;
        }

        return $this->questions()->max('stage') ?? 1;
    }

    public function questions(): HasMany
    {
        return $this->hasMany(TestQuestion::class)->orderBy('stage', 'asc')->orderBy('order', 'asc');
        // حذف sort از ترتیب‌سازی
    }

    public function questionsByStage($stage): HasMany
    {
        return $this->hasMany(TestQuestion::class)
            ->where('stage', $stage)
            ->orderBy('order', 'asc');
        // حذف sort از ترتیب‌سازی
    }
}
