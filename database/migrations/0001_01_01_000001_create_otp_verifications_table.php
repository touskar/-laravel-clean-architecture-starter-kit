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
        // OTP Verifications table - Store OTP codes for phone/email verification
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID format
            $table->string('phone', 50);
            $table->string('email')->nullable();
            $table->string('otp_code', 10);
            $table->string('user_type', 50)->default('USER'); // USER, ADMIN, CONTENT_CREATOR, ADVERTISER
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at');
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(3);
            $table->timestamps();

            // Indexes for better query performance
            $table->index('phone');
            $table->index('email');
            $table->index('is_verified');
            $table->index('expires_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
