<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('payment_method', ['cheque', 'cash', 'online']);
            $table->enum('payment_type', ['dr', 'cr']);
            $table->text('description')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->enum('status', ['pending', 'cancelled', 'completed', 'failed', 'invalid'])->default('pending');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
