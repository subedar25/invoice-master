<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            // FK: 2026_03_08_031000 — `organizations` is created in 2026_03_08_000000.
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->string('email')->unique();
            $table->string('companyname')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('vendor_categories')->nullOnDelete();
            $table->text('address')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('pin')->nullable();
            $table->string('PAN')->nullable();
            $table->string('gst')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};