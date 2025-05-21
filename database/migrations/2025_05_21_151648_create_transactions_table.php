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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['deposit', 'purchase', 'referral_bonus', 'admin_adjustment', 'refund']);
            $table->decimal('amount', 12, 0);
            $table->decimal('balance_before', 12, 0);
            $table->decimal('balance_after', 12, 0);
            $table->string('payment_gateway')->nullable();
            $table->string('payment_ref_id')->nullable();
            $table->json('payment_data')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed']);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
