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
        Schema::table('production_logs', function (Blueprint $table) {
            // Only add columns that don't exist yet
            // Skip: batch_number, production_date, expiry_date, user_id (already exist)

            $table->enum('shift', ['morning', 'afternoon', 'night'])->nullable()->after('user_id');

            // Quality & Efficiency
            $table->integer('production_time_minutes')->nullable()->after('shift');
            $table->decimal('yield_percentage', 5, 2)->nullable()->after('production_time_minutes');
            $table->enum('quality_grade', ['A', 'B', 'C', 'D'])->default('A')->after('yield_percentage');
            $table->decimal('waste_quantity', 10, 2)->default(0)->after('quality_grade');

            // Cost Tracking
            $table->decimal('labor_cost', 10, 2)->nullable()->after('waste_quantity');
            $table->decimal('overhead_cost', 10, 2)->nullable()->after('labor_cost');
            $table->decimal('total_production_cost', 10, 2)->nullable()->after('overhead_cost');

            // Notes & Issues
            $table->text('notes')->nullable()->after('total_production_cost');
            $table->text('issues_encountered')->nullable()->after('notes');
            $table->json('temperature_log')->nullable()->after('issues_encountered'); // {oven_temp, storage_temp, etc}
            $table->enum('status', ['planned', 'in_progress', 'completed', 'failed'])->default('planned')->after('temperature_log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'production_date',
                'expiry_date',
                'user_id',
                'shift',
                'production_time_minutes',
                'yield_percentage',
                'quality_grade',
                'waste_quantity',
                'labor_cost',
                'overhead_cost',
                'total_production_cost',
                'notes',
                'issues_encountered',
                'temperature_log',
                'status',
            ]);
        });
    }
};
