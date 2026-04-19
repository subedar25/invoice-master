<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_bank', function (Blueprint $table) {
            $table->id();
            // FK added in 2024_05_27_010000 — `vendors` is created later the same week.
            $table->unsignedBigInteger('vendor_id');
            $table->string('bank_name');
            $table->string('ac_number');
            $table->string('ifsc_number');
            $table->string('ac_type');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_bank');
    }
};