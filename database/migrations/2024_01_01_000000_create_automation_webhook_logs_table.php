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
        Schema::create('automation_webhook_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('service')->index();
            $table->string('event')->nullable()->index();
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->enum('status', ['processing', 'success', 'failed'])->default('processing')->index();
            $table->text('error_message')->nullable();
            $table->json('response')->nullable();
            $table->decimal('processing_time_ms', 8, 2)->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['service', 'status']);
            $table->index(['service', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_webhook_logs');
    }
};
