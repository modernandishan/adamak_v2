<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'meta_title',
        'meta_value',
        'description',
        'type',
        'options',
        'is_private',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'options' => 'array',
    ];

    // متد کمکی برای بازیابی آسان مقادیر تنظیمات
    public static function get(string $key, $default = null)
    {
        $setting = static::where('meta_title', $key)->first();

        if (!$setting) {
            return $default;
        }

        // تبدیل مقدار بر اساس نوع
        return match($setting->type) {
            'boolean' => (bool) $setting->meta_value,
            'number' => is_numeric($setting->meta_value) ? (float) $setting->meta_value : $default,
            default => $setting->meta_value,
        };
    }

    // متد کمکی برای ذخیره آسان مقادیر تنظیمات
    public static function set(string $key, $value)
    {
        $setting = static::firstOrNew(['meta_title' => $key]);
        $setting->meta_value = $value;
        $setting->save();

        return $setting;
    }
}
