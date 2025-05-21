<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'type',
        'question_text',
        'instruction',
        'options',
        'stage',
        'order',
        'sort',
        'is_required',
        'validation_rules',
        'settings',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'validation_rules' => 'array',
        'settings' => 'array',
    ];

    // مقدار پیش‌فرض برای options تا از null بودن آن جلوگیری شود
    protected $attributes = [
        'options' => '[]',  // ذخیره به صورت JSON string
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    // تبدیل options از JSON به آرایه (به جای استفاده از accessor)
    public function getOptionsAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        // اگر $value رشته است، آن را decode کنید
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        // اگر $value آرایه است، آن را برگردانید
        if (is_array($value)) {
            return $value;
        }

        // در غیر این صورت آرایه خالی برگردانید
        return [];
    }

    public function getFormattedOptionsAttribute()
    {
        \Log::debug('Options value: ' . var_export($this->options, true));
        \Log::debug('Question type: ' . $this->type);

        if (empty($this->options)) {
            \Log::debug('Options is empty');
            return [];
        }

        if (!is_array($this->options)) {
            \Log::debug('Options is not an array, type: ' . gettype($this->options));
            return [];
        }

        if (!in_array($this->type, ['choice', 'multiple_choice'])) {
            \Log::debug('Question type is not choice or multiple_choice');
            return [];
        }

        \Log::debug('Processing options as array');

        return collect($this->options)->map(function ($option, $key) {
            return [
                'id' => $key,
                'text' => $option,
            ];
        })->values()->all();
    }
}
