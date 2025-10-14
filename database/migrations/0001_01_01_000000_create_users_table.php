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
        // Users table - Core user accounts
        Schema::create('users', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID format
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('phone', 50)->unique();
            $table->string('password_hash');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('user_type', 50)->default('USER'); // USER, ADMIN, CONTENT_CREATOR, ADVERTISER
            $table->string('status', 50)->default('ACTIVE'); // ACTIVE, INACTIVE, SUSPENDED
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index('username');
            $table->index('email');
            $table->index('phone');
            $table->index('user_type');
            $table->index('status');
            $table->index('created_at');
        });

        // Sessions table - JWT session management
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID format
            $table->string('user_id', 26);
            $table->text('token'); // Original JWT token (for reference)
            $table->string('hashed_token')->unique(); // SHA-256 hash (for lookup)
            $table->string('device_name')->nullable();
            $table->string('ip_address', 45)->nullable(); // Supports IPv4 and IPv6
            $table->text('user_agent')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at');
            $table->timestamp('last_used_at');
            $table->timestamp('created_at');

            // Foreign key
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Indexes
            $table->index('user_id');
            $table->index('hashed_token');
            $table->index('is_active');
            $table->index('expires_at');
            $table->index('last_used_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
