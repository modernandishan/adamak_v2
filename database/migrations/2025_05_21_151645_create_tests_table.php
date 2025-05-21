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
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable(); // برای TipTap Editor
            $table->string('featured_image')->nullable();
            $table->decimal('price', 12, 0);
            $table->decimal('discounted_price', 12, 0)->nullable();
            $table->boolean('requires_family')->default(false);
            $table->boolean('is_multi_stage')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('estimated_completion_time')->nullable(); // به دقیقه
            $table->unsignedInteger('order')->default(0);
            $table->json('settings')->nullable(); // تنظیمات اضافی
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
