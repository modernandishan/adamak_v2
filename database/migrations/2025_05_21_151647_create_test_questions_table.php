<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['text', 'choice', 'multiple_choice', 'upload', 'drawing', 'rating']);
            $table->longText('question_text');
            $table->text('instruction')->nullable();
            $table->json('options')->nullable(); // برای گزینه‌های سوالات چند گزینه‌ای
            $table->unsignedInteger('stage')->default(1); // برای آزمون‌های چند مرحله‌ای
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('sort')->nullable();
            $table->boolean('is_required')->default(true);
            $table->json('validation_rules')->nullable(); // قوانین اعتبارسنجی
            $table->json('settings')->nullable(); // تنظیمات اضافی
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_questions');
    }
};
