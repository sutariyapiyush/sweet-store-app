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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopify_order_id')->constrained('shopify_orders')->onDelete('cascade');
            $table->string('invoice_number')->unique();

            // Invoice Details
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();

            // Customer Information
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->json('billing_address');
            $table->json('shipping_address')->nullable();

            // Financial Details
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('shipping_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('INR');

            // Payment Information
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance_due', 10, 2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();

            // Invoice Files and QR Code
            $table->string('pdf_path')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->json('qr_code_data')->nullable(); // Data encoded in QR code

            // Notes and Terms
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();

            // Tracking
            $table->boolean('is_printed')->default(false);
            $table->timestamp('printed_at')->nullable();
            $table->boolean('is_emailed')->default(false);
            $table->timestamp('emailed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['shopify_order_id']);
            $table->index(['invoice_number']);
            $table->index(['status']);
            $table->index(['customer_email']);
            $table->index(['invoice_date']);
            $table->index(['due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
