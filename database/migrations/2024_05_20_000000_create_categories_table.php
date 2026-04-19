<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This table is also created by an earlier migration in this repo.
        // Avoid failing migrations in environments where it already exists.
        if (Schema::hasTable('vendor_categories')) {
            return;
        }

        Schema::create('vendor_categories', function (Blueprint $table) {
            $table->id();
            // organization_id + FK: see 2026_03_08_020000 (organizations table is created later).
            $table->string('name');
            $table->text('desc')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('vendor_categories')->nullOnDelete();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_categories');
    }
};
