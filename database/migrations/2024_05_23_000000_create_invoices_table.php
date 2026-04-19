<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->nullable();
            // FKs to organizations / departments: see 2026_03_08_030000 and 2025_12_29_133000 (created before those tables exist).
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('createdby_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('pay_term')->nullable();
            $table->date('comp_date')->nullable();
            $table->year('year')->nullable();
            $table->text('description')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('status')->default('pending');
            $table->string('order_status')->default('pending'); // Values: pending, approved, cancel, completed
            $table->string('task_status')->default('pending'); // Values: pending, approved, cancel, completed
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_date')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};