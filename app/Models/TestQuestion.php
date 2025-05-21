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
        'sort', // اضافه کردن این فیلد
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

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function getFormattedOptionsAttribute()
    {
        if (empty($this->options) || !is_array($this->options) || !in_array($this->type, ['choice', 'multiple_choice'])) {
            return [];
        }

        return collect($this->options)->map(function ($option, $key) {
            return [
                'id' => $key,
                'text' => $option,
            ];
        })->values()->all();
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($question) {
            if (empty($question->options)) {
                $question->options = [];
            }
        });
    }
}
