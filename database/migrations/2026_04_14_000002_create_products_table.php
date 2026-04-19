<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prefer upgrading `products` from 2024_05_25 when it already exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            $this->upgradeExistingProductsTable();

            return;
        }

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('hsn')->nullable();
            $table->decimal('cgst', 10, 2)->default(0);
            $table->decimal('sgst', 10, 2)->default(0);
            $table->decimal('total_gst', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2)->default(0);
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        $this->addProductsOrganizationForeignKeyIfPossible();
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }

    private function upgradeExistingProductsTable(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'hsn')) {
                $table->string('hsn')->nullable();
            }
            if (! Schema::hasColumn('products', 'organization_id')) {
                $table->unsignedBigInteger('organization_id')->nullable();
            }
            if (! Schema::hasColumn('products', 'status')) {
                $table->boolean('status')->default(1);
            }
        });

        $this->addProductsOrganizationForeignKeyIfPossible();
    }

    private function addProductsOrganizationForeignKeyIfPossible(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasTable('organizations')) {
            return;
        }

        if (! Schema::hasColumn('products', 'organization_id')) {
            return;
        }

        try {
            Schema::table('products', function (Blueprint $table) {
                $table->foreign('organization_id')
                    ->references('id')
                    ->on('organizations')
                    ->onDelete('cascade');
            });
        } catch (\Throwable) {
            // Foreign key may already exist (e.g. partial run).
        }
    }
};
