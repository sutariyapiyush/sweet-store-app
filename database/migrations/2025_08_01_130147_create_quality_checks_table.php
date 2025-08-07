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
        Schema::create('quality_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_log_id')->constrained()->onDelete('cascade');
            $table->enum('check_type', ['visual', 'taste', 'texture', 'weight', 'temperature', 'packaging', 'other']);
            $table->enum('result', ['pass', 'fail', 'conditional_pass'])->default('pass');
            $table->decimal('measured_value', 10, 3)->nullable(); // For weight, temperature, etc.
            $table->decimal('expected_value', 10, 3)->nullable(); // Expected value for comparison
            $table->decimal('tolerance', 10, 3)->nullable(); // Acceptable variance
            $table->text('notes')->nullable();
            $table->json('checklist_items')->nullable(); // For detailed checklists
            $table->foreignId('checked_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('checked_at');
            $table->string('corrective_action')->nullable(); // What was done if failed
            $table->timestamps();

            $table->index(['production_log_id', 'check_type']);
            $table->index(['result', 'checked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_checks');
    }
};
