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
        Schema::create('production_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->decimal('planned_quantity', 10, 2);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'delayed'])->default('scheduled');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->json('required_materials')->nullable(); // Pre-calculated materials needed
            $table->integer('estimated_duration_minutes')->nullable();
            $table->date('due_date')->nullable(); // When the production must be completed
            $table->timestamps();

            $table->index(['scheduled_date', 'status']);
            $table->index(['product_id', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_schedules');
    }
};
